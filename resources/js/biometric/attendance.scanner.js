const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const statusText = document.getElementById("statusText");
const statusDot = document.getElementById("statusDot");
const successOverlay = document.getElementById("successOverlay");
const btnAbsen = document.getElementById("btnAbsen");

const { verifyUrl, csrfToken, dashboardUrl, type } = window.attendanceConfig;

let currentType = type;
let isModelsLoaded = false,
    isFaceDetected = false,
    isProcessing = false,
    userCoords = null,
    lastDetection = null;

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
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
            },
            (e) => {
                document.getElementById("gpsStatus").innerHTML =
                    '<span class="badge badge-danger px-2 py-1">OFF</span>';
                toastr.error("Akses GPS diperlukan untuk presensi!");
            },
            { enableHighAccuracy: true },
        );
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
            startDetection();
        };
    } catch (e) {
        toastr.error("Kamera tidak terdeteksi atau akses ditolak");
        updateStatus("Cam Error", "error");
    }
}

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
    const displaySize = {
        width: video.clientWidth,
        height: video.clientHeight,
    };
    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {
        if (!isModelsLoaded || isProcessing) return;

        const det = await faceapi
            .detectSingleFace(
                video,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 224,
                    scoreThreshold: 0.5,
                }),
            )
            .withFaceLandmarks()
            .withFaceDescriptor();

        const ctx = canvas.getContext("2d");
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (det) {
            const resized = faceapi.resizeResults(det, displaySize);
            drawStylizedBox(ctx, resized.detection.box);

            updateStatus("Scan Ready", "active");
            isFaceDetected = true;
            lastDetection = det;
            if (userCoords) btnAbsen.disabled = false;
        } else {
            updateStatus("Searching...", "default");
            isFaceDetected = false;
            btnAbsen.disabled = true;
        }
    }, 150);
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
            document.getElementById("successTime").innerText = "Diterima Pukul " + data.waktu;
            
            if (data.is_late) {
                 document.getElementById("successName").innerHTML += " <span class='text-warning'>(TERLAMBAT)</span>";
                 document.getElementById("successTime").innerHTML += "<br><small class='text-danger'>" + data.late_info + "</small>";
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
