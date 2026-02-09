// ================== ELEMENT ==================
const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const statusText = document.getElementById("statusText");
const statusDot = document.getElementById("statusDot");
const enrollBtn = document.getElementById("enrollBtn");
const accuracyBadge = document.getElementById("accuracyBadge");
const accuracyBar = document.getElementById("accuracyBar");

// ================== STATE ==================
let isModelsLoaded = false;
let isProcessing = false;
let lastDetection = null;

// ================== INIT ==================
async function init() {
    try {
        updateStatus("Syncing AI...");

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
        console.error(e);
        toastr.error("Gagal mengakses kamera!");
        updateStatus("Cam Error", "error");
    }
}

// ================== UI HELPERS ==================
function updateStatus(text, type = "default") {
    statusText.innerText = text;
    statusDot.className =
        "dot " + (type === "active" ? "green" : type === "error" ? "red" : "");
}

function drawStylizedBox(ctx, box) {
    const { x, y, width, height } = box;
    const c = 30;

    ctx.strokeStyle = "#4f46e5";
    ctx.lineWidth = 4;
    ctx.lineJoin = "round";

    const lines = [
        [
            [x, y + c],
            [x, y],
            [x + c, y],
        ],
        [
            [x + width - c, y],
            [x + width, y],
            [x + width, y + c],
        ],
        [
            [x, y + height - c],
            [x, y + height],
            [x + c, y + height],
        ],
        [
            [x + width - c, y + height],
            [x + width, y + height],
            [x + width, y + height - c],
        ],
    ];

    lines.forEach((l) => {
        ctx.beginPath();
        ctx.moveTo(...l[0]);
        ctx.lineTo(...l[1]);
        ctx.lineTo(...l[2]);
        ctx.stroke();
    });
}

// ================== DETECTION ==================
function startDetection() {
    const displaySize = {
        width: video.clientWidth,
        height: video.clientHeight,
    };

    faceapi.matchDimensions(canvas, displaySize);

    setInterval(async () => {
        if (!isModelsLoaded || isProcessing) return;

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
            return;
        }

        const resized = faceapi.resizeResults(detection, displaySize);
        drawStylizedBox(ctx, resized.detection.box);

        const score = Math.round(detection.detection.score * 100);
        accuracyBadge.innerText = `${score}%`;
        accuracyBar.style.width = `${score}%`;

        lastDetection = detection;
        enrollBtn.disabled = false;
        updateStatus("SIAP DAFTAR", "active");
    }, 150);
}

// ================== ENROLL ==================
enrollBtn.addEventListener("click", async () => {
    if (!lastDetection || isProcessing) return;

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
        outputSize // output (1:1)
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
            }),
        });

        const data = await response.json();

        if (data.status === "ok") {
            toastr.success("Wajah berhasil didaftarkan!");
            setTimeout(() => {
                window.location.href = window.redirectUrl;
            }, 1500);
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

document.addEventListener("DOMContentLoaded", init);
