// ================== ELEMENT ==================
const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const statusText = document.getElementById("statusText");
const statusDot = document.getElementById("statusDot");
const enrollBtn = document.getElementById("enrollBtn");
const accuracyBadge = document.getElementById("accuracyBadge");
const accuracyBar = document.getElementById("accuracyBar");
const studentSelectDesktop = document.getElementById("studentSelect_desktop");
const studentSelectMobile = document.getElementById("studentSelect_mobile");
const permissionOverlay = document.getElementById("permissionOverlay");
const successOverlay = document.getElementById("successOverlay");

// ================== STATE ==================
let isModelsLoaded = false;
let isProcessing = false;
let lastDetection = null;

// ================== INIT ==================
async function init() {
    try {
        updateStatus("Syncing AI...");

        // Ensure libraries are totally ready
        if (typeof faceapi === "undefined") {
            throw new Error("Face-API library not loaded yet!");
        }

        const MODEL_URL = "/models";

        console.log("Loading Face-API models...");

        // Load sequentially for better stability and error pinpointing
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            console.log("TinyFaceDetector loaded.");
        } catch (e) {
            console.error("Failed to load TinyFaceDetector:", e);
        }

        try {
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            console.log("FaceLandmark68Net loaded.");
        } catch (e) {
            console.error("Failed to load FaceLandmark68Net:", e);
        }

        try {
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            console.log("FaceRecognitionNet loaded.");
        } catch (e) {
            console.error("Failed to load FaceRecognitionNet:", e);
        }

        isModelsLoaded = true;
        console.log("All models attempted. Starting camera...");

        // Even if some models failed, try to start camera
        const stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "user",
                width: { ideal: 1280 },
                height: { ideal: 720 },
            },
        });

        video.srcObject = stream;
        video.onloadedmetadata = () => {
            video.play();
            permissionOverlay.classList.remove("show");
            startDetection(); // This will handle if specific features aren't available
        };
    } catch (e) {
        console.error("Core Init Error:", e);
        if (
            e.name === "NotAllowedError" ||
            e.name === "PermissionDeniedError"
        ) {
            permissionOverlay.classList.add("show");
        }
        toastr.error("Gagal inisialisasi: " + e.message);
        updateStatus("System Error", "error");
    }
}

// Global Permission Request
window.requestPermissions = async function (event) {
    const btn = event?.currentTarget || event?.target;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>MEMINTA...';
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: 1280, height: 720 },
        });
        video.srcObject = stream;
        await video.play();

        if (!isModelsLoaded) await init();
        permissionOverlay.classList.remove("show");
    } catch (e) {
        console.error(e);
        toastr.error("Akses kamera tetap ditolak.");
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = "IZINKAN SEKARANG";
        }
    }
};

// ================== UI HELPERS ==================
function updateStatus(text, type = "default") {
    statusText.innerText = text;
    statusDot.className =
        "dot " + (type === "active" ? "green" : type === "error" ? "red" : "");
}

function drawStylizedBox(ctx, box) {
    const { x, y, width, height } = box;
    const cornerLength = 30;
    const lineWidth = 4;

    ctx.strokeStyle = "#4f46e5";
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

    ctx.fillStyle = "rgba(79, 70, 229, 0.05)";
    ctx.fillRect(x, y, width, height);
}

// ================== DETECTION ==================
function startDetection() {
    const runDetection = async () => {
        if (!isModelsLoaded || isProcessing) {
            setTimeout(runDetection, 200);
            return;
        }

        if (video.paused || video.ended || !video.srcObject) {
            setTimeout(runDetection, 200);
            return;
        }

        try {
            const videoRect = video.getBoundingClientRect();
            const displaySize = {
                width: videoRect.width,
                height: videoRect.height,
            };

            if (displaySize.width > 0 && displaySize.height > 0) {
                if (
                    Math.abs(canvas.width - displaySize.width) > 2 ||
                    Math.abs(canvas.height - displaySize.height) > 2
                ) {
                    faceapi.matchDimensions(canvas, displaySize);
                }
            }

            const detection = await faceapi
                .detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions({
                        inputSize: 224,
                        scoreThreshold: 0.6,
                    }),
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (!detection) {
                updateStatus("MENCARI WAJAH");
                accuracyBadge.innerText = "0%";
                accuracyBar.style.width = "0%";
                enrollBtn.disabled = true;
            } else {
                // PENTING: Fix Gepeng Box karena object-fit: cover
                // Kita hitung skala yang benar agar box tidak gepeng
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;
                const canvasWidth = displaySize.width;
                const canvasHeight = displaySize.height;

                const xScale = canvasWidth / videoWidth;
                const yScale = canvasHeight / videoHeight;
                const finalScale = Math.max(xScale, yScale); // tiru object-fit: cover

                const xOffset = (canvasWidth - videoWidth * finalScale) / 2;
                const yOffset = (canvasHeight - videoHeight * finalScale) / 2;

                const resized = {
                    detection: {
                        box: {
                            x: detection.detection.box.x * finalScale + xOffset,
                            y: detection.detection.box.y * finalScale + yOffset,
                            width: detection.detection.box.width * finalScale,
                            height: detection.detection.box.height * finalScale,
                        },
                    },
                };

                drawStylizedBox(ctx, resized.detection.box);

                const score = Math.round(detection.detection.score * 100);
                accuracyBadge.innerText = `${score}%`;
                accuracyBar.style.width = `${score}%`;

                const accuracyVal = document.getElementById("accuracyVal");
                if (accuracyVal) accuracyVal.innerText = `${score}%`;

                lastDetection = detection;
                enrollBtn.disabled = false;
                updateStatus("SIAP DAFTAR", "active");
            }
        } catch (err) {
            console.error("Detection Error:", err);
        }

        setTimeout(runDetection, 150);
    };

    runDetection();
}

// ================== ENROLL ==================
enrollBtn.addEventListener("click", async () => {
    if (!lastDetection || isProcessing) return;

    const studentId = studentSelectDesktop.value || studentSelectMobile.value;
    if (!studentId) {
        toastr.warning("Silakan pilih siswa terlebih dahulu!");
        return;
    }

    isProcessing = true;
    enrollBtn.disabled = true;
    enrollBtn.innerHTML =
        '<i class="fa fa-refresh fa-spin mr-2"></i>MENDAFTARKAN...';

    const box = lastDetection.detection.box;

    // === HITUNG AREA WAJAH (PERSEGI 1:1) ===
    const size = Math.max(box.width, box.height);
    const centerX = box.x + box.width / 2;
    const centerY = box.y + box.height / 2;

    const sx = Math.max(centerX - size / 2, 0);
    const sy = Math.max(centerY - size / 2, 0);

    const shot = document.createElement("canvas");
    const outputSize = 300; // hasil akhir 1:1
    shot.width = outputSize;
    shot.height = outputSize;

    const ctx = shot.getContext("2d");

    ctx.drawImage(
        video,
        sx,
        sy,
        size,
        size, // source (crop wajah)
        0,
        0,
        outputSize,
        outputSize, // output (1:1)
    );

    try {
        const response = await fetch(window.enrollEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": window.csrfToken,
            },
            body: JSON.stringify({
                image: shot.toDataURL("image/jpeg", 0.8),
                face_embedding: Array.from(lastDetection.descriptor),
                peserta_didik_id: studentId,
            }),
        });

        const data = await response.json();

        if (data.status === "ok") {
            successOverlay.classList.add("show");
            toastr.success(data.message);
        } else {
            throw new Error(data.message || "Gagal mendaftarkan wajah");
        }
    } catch (e) {
        console.error(e);
        toastr.error(e.message || "Gangguan koneksi server");
        isProcessing = false;
        enrollBtn.disabled = false;
        enrollBtn.innerHTML =
            '<i class="fa fa-user-plus mr-2"></i> DAFTARKAN WAJAH';
    }
});

window.resetEnroll = function () {
    successOverlay.classList.remove("show");
    isProcessing = false;
    enrollBtn.disabled = false;
    enrollBtn.innerHTML =
        '<i class="fa fa-user-plus mr-2"></i> DAFTARKAN WAJAH';
    if (studentSelectDesktop)
        $(studentSelectDesktop).val(null).trigger("change");
    if (studentSelectMobile) $(studentSelectMobile).val(null).trigger("change");
};

document.addEventListener("DOMContentLoaded", init);
