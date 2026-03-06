const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const statusText = document.getElementById("statusText");
const statusDot = document.getElementById("statusDot");
const successOverlay = document.getElementById("successOverlay");
const permissionOverlay = document.getElementById("permissionOverlay");
const btnAbsen = document.getElementById("btnAbsen");

const { verifyUrl, csrfToken, dashboardUrl, type } = window.attendanceConfig;

let currentType = type;
let isModelsLoaded = false,
    isFaceDetected = false,
    isProcessing = false,
    userCoords = null,
    lastDetection = null;

function getLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            toastr.error("Browser tidak mendukung GPS.");
            return reject(new Error("No Geolocation Support"));
        }

        navigator.geolocation.getCurrentPosition(
            (p) => {
                userCoords = {
                    lat: p.coords.latitude,
                    lng: p.coords.longitude,
                };
                document.getElementById("gpsStatus").innerHTML =
                    '<span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i>STABIL</span>';
                document.getElementById("coordsDebug").innerText =
                    userCoords.lat.toFixed(6) +
                    ", " +
                    userCoords.lng.toFixed(6);
                checkPermissionsDone();
                resolve(p);
            },
            (e) => {
                document.getElementById("gpsStatus").innerHTML =
                    '<span class="badge badge-danger px-2 py-1">OFF</span>';
                if (e.code === 1) permissionOverlay.classList.add("show");
                toastr.error("Akses GPS ditolak / bermasalah.");
                reject(e);
            },
            { enableHighAccuracy: true, timeout: 5000 },
        );

        navigator.geolocation.watchPosition(
            (p) => {
                userCoords = {
                    lat: p.coords.latitude,
                    lng: p.coords.longitude,
                };
                document.getElementById("coordsDebug").innerText =
                    userCoords.lat.toFixed(6) +
                    ", " +
                    userCoords.lng.toFixed(6);
            },
            null,
            { enableHighAccuracy: true },
        );
    });
}

function checkPermissionsDone() {
    // Hide overlay only if BOTH camera and location are ready
    if (video.srcObject && userCoords) {
        permissionOverlay.classList.remove("show");
    }
}

async function init() {
    try {
        getLocation();
        updateStatus("Syncing AI...", "default");

        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("/models"),
            faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
            faceapi.nets.faceRecognitionNet.loadFromUri("/models"),
        ]);

        isModelsLoaded = true;

        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: 1280, height: 720 },
        });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            video.play();
            checkPermissionsDone();
            startDetection();
        };
    } catch (e) {
        // Tampilkan overlay jika akses ditolak (NotAllowedError/PermissionDeniedError)
        if (
            e.name === "NotAllowedError" ||
            e.name === "PermissionDeniedError"
        ) {
            permissionOverlay.classList.add("show");
        }
        console.error("Camera Error:", e);
        toastr.error("Kamera akses ditolak atau tidak terdeteksi");
        updateStatus("Cam Error", "error");
    }
}

// Global Re-init
window.requestPermissions = async function (event) {
    if (location.protocol !== "https:" && location.hostname !== "localhost") {
        toastr.error("Kamera & GPS butuh koneksi HTTPS!");
        return;
    }

    console.log("Requesting permissions via user interaction...");
    const btn = event?.currentTarget || event?.target;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>MEMINTA...';
    }

    try {
        // 1. Manually trigger Camera first (Must be in user callback)
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: 1280, height: 720 },
        });
        video.srcObject = stream;
        await video.play();

        // 2. Manually trigger GPS
        await getLocation();

        // 3. Continue with AI models
        if (!isModelsLoaded) await init();

        checkPermissionsDone();
    } catch (e) {
        console.error("Manual Request Error:", e);
        if (e.name === "NotAllowedError" || e.code === 1) {
            toastr.warning("Hapus blokir di setelan browser (Ikon Gembok)");
        }
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = "IZINKAN SEKARANG";
        }
    }
};

function updateStatus(text, type) {
    statusText.innerText = text;
    statusDot.className =
        "dot " + (type === "active" ? "green" : type === "error" ? "red" : "");
}

// Custom Drawing Function
function drawStylizedBox(ctx, box) {
    const { x, y, width, height } = box;
    const cornerLength = 30;
    const lineWidth = 4;

    ctx.strokeStyle = "#22c55e";
    ctx.lineWidth = lineWidth;
    ctx.lineJoin = "round";

    // Top Left
    ctx.beginPath();
    ctx.moveTo(x, y + cornerLength);
    ctx.lineTo(x, y);
    ctx.lineTo(x + cornerLength, y);
    ctx.stroke();

    // Top Right
    ctx.beginPath();
    ctx.moveTo(x + width - cornerLength, y);
    ctx.lineTo(x + width, y);
    ctx.lineTo(x + width, y + cornerLength);
    ctx.stroke();

    // Bottom Left
    ctx.beginPath();
    ctx.moveTo(x, y + height - cornerLength);
    ctx.lineTo(x, y + height);
    ctx.lineTo(x + cornerLength, y + height);
    ctx.stroke();

    // Bottom Right
    ctx.beginPath();
    ctx.moveTo(x + width - cornerLength, y + height);
    ctx.lineTo(x + width, y + height);
    ctx.lineTo(x + width, y + height - cornerLength);
    ctx.stroke();

    ctx.fillStyle = "rgba(34, 197, 94, 0.05)";
    ctx.fillRect(x, y, width, height);
}

function startDetection() {
    // Gunakan recursive loop untuk mencegah 'stacking' proses deteksi
    // yang membuat browser berat/laggy.
    const runDetection = async () => {
        if (!isModelsLoaded || isProcessing) {
            setTimeout(runDetection, 200);
            return;
        }

        // Cek apakah video sedang playing
        if (video.paused || video.ended || !video.srcObject) {
            setTimeout(runDetection, 200);
            return;
        }

        try {
            // Dynamic resize: Always match current visible video dimensions
            const displaySize = {
                width: video.clientWidth,
                height: video.clientHeight,
            };

            // Optimization: Hanya match dimension jika size valid
            if (displaySize.width > 0 && displaySize.height > 0) {
                // Cek canvas match dimensions manual untuk menghindari re-allocation berat
                if (
                    canvas.width !== displaySize.width ||
                    canvas.height !== displaySize.height
                ) {
                    faceapi.matchDimensions(canvas, displaySize);
                }
            }

            // Detection options: Input size kecil lebih cepat (160 atau 224)
            // withFaceLandmarks necessary for aligning face for descriptor
            const det = await faceapi
                .detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions({
                        inputSize: 160, // Lowered from 224 for better speed
                        scoreThreshold: 0.5,
                    }),
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (det && displaySize.width > 0) {
                const resized = faceapi.resizeResults(det, displaySize);
                drawStylizedBox(ctx, resized.detection.box);

                if (!isFaceDetected) {
                    updateStatus("Scan Ready", "active");
                    isFaceDetected = true;
                }
                lastDetection = det;
                if (userCoords) btnAbsen.disabled = false;
            } else {
                if (isFaceDetected) {
                    updateStatus("Searching...", "default");
                    isFaceDetected = false;
                    btnAbsen.disabled = true;
                }
            }
        } catch (err) {
            console.error("Detection Error:", err);
        }

        // Jalankan frame berikutnya dengan delay
        // Delay 100ms memberikan waktu "nafas" untuk UI thread agar animasi smooth
        setTimeout(runDetection, 100);
    };

    runDetection();
}

btnAbsen.onclick = async () => {
    if (isProcessing || !lastDetection || !userCoords) return;

    isProcessing = true;
    btnAbsen.disabled = true;
    btnAbsen.innerHTML =
        '<i class="fa fa-refresh fa-spin mr-2"></i>MENCOCOKKAN...';

    const shot = document.createElement("canvas");
    shot.width = video.videoWidth;
    shot.height = video.videoHeight;
    shot.getContext("2d").drawImage(video, 0, 0);

    try {
        const res = await fetch(verifyUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                image: shot.toDataURL("image/jpeg", 0.5),
                face_embedding: Array.from(lastDetection.descriptor),
                type: currentType,
                latitude: userCoords.lat,
                longitude: userCoords.lng,
            }),
        });

        const data = await res.json();
        if (data.status === "success") {
            document.getElementById("successName").innerText = data.nama;
            document.getElementById("successTime").innerText =
                "Diterima Pukul " + data.waktu;

            if (data.is_late) {
                document.getElementById("successName").innerHTML +=
                    " <span class='text-warning'>(TERLAMBAT)</span>";
                document.getElementById("successTime").innerHTML +=
                    "<br><small class='text-danger'>" +
                    data.late_info +
                    "</small>";
            }

            successOverlay.style.display = "flex";
            if (data.is_late) {
                toastr.warning("Presensi TERLAMBAT dicatat!");
            } else {
                toastr.success("Presensi berhasil dicatat!");
            }
        } else {
            toastr[data.status === "info" ? "info" : "error"](data.message);
            isProcessing = false;
            btnAbsen.innerHTML =
                '<i class="fa fa-camera mr-2"></i> KONFIRMASI HADIR';
        }
    } catch (e) {
        toastr.error("Gangguan koneksi server");
        isProcessing = false;
        btnAbsen.innerHTML =
            '<i class="fa fa-camera mr-2"></i> KONFIRMASI HADIR';
    }
};

window.setType = function (newType, el) {
    currentType = newType;
    document
        .querySelectorAll(".type-btn")
        .forEach((b) => b.classList.remove("active"));
    el.classList.add("active");
    toastr.info("Kategori diubah ke: " + newType.toUpperCase());
};

window.resetScanner = function () {
    successOverlay.style.display = "none";
    isProcessing = false;
    btnAbsen.innerHTML = '<i class="fa fa-camera mr-2"></i> KONFIRMASI HADIR';
};

init();
