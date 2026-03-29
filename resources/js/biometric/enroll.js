// ================== ELEMEN UI ==================
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

// ================== STATUS APLIKASI ==================
let isModelsLoaded = false; // Status apakah model AI sudah siap
let isProcessing = false;   // Status apakah sedang mengirim data ke server
let lastDetection = null;    // Hasil deteksi wajah terakhir

// ================== KEGIATAN AWAL (INIT) ==================
// Mempersiapkan model AI dan kamera saat halaman dimuat
async function init() {
    try {
        updateStatus("Menghubungkan AI...");

        // Pastikan pustaka Face-API sudah dimuat di browser
        if (typeof faceapi === "undefined") {
            throw new Error("Pustaka Face-API belum dimuat!");
        }

        const MODEL_URL = "/models";

        console.log("Memuat model Face-API...");

        // Muat model satu per satu secara sekuensial untuk stabilitas lebih baik
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            console.log("TinyFaceDetector berhasil dimuat.");
        } catch (e) {
            console.error("Gagal memuat TinyFaceDetector:", e);
        }

        try {
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            console.log("FaceLandmark68Net berhasil dimuat.");
        } catch (e) {
            console.error("Gagal memuat FaceLandmark68Net:", e);
        }

        try {
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            console.log("FaceRecognitionNet berhasil dimuat.");
        } catch (e) {
            console.error("Gagal memuat FaceRecognitionNet:", e);
        }

        isModelsLoaded = true;
        console.log("Semua model telah dicoba. Memulai kamera...");

        // Minta izin akses kamera dengan resolusi ideal 720p
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
            permissionOverlay.classList.remove("show"); // Sembunyikan panduan izin jika berhasil
            startDetection(); // Mulai loop pemindaian wajah
        };
    } catch (e) {
        console.error("Kesalahan Inisialisasi Utama:", e);
        // Tampilkan overlay instruksi jika akses ditolak browser
        if (
            e.name === "NotAllowedError" ||
            e.name === "PermissionDeniedError"
        ) {
            permissionOverlay.classList.add("show");
        }
        toastr.error("Gagal inisialisasi: " + e.message);
        updateStatus("Gangguan Sistem", "error");
    }
}

// Fungsi global untuk memicu permintaan izin kamera (digunakan di tombol manual)
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

// ================== HELPER ANTARMUKA (UI) ==================
// Memperbarui indikator status deteksi wajah di layar
function updateStatus(text, type = "default") {
    statusText.innerText = text;
    statusDot.className =
        "dot " + (type === "active" ? "green" : type === "error" ? "red" : "");
}

// Menggambar kotak penanda wajah kustom yang futuristik
function drawStylizedBox(ctx, box) {
    const { x, y, width, height } = box;
    const cornerLength = 30;
    const lineWidth = 4;

    ctx.strokeStyle = "#4f46e5"; // Indigo Blue
    ctx.lineWidth = lineWidth;
    ctx.lineJoin = "round";

    // Kiri Atas
    ctx.beginPath();
    ctx.moveTo(x, y + cornerLength);
    ctx.lineTo(x, y);
    ctx.lineTo(x + cornerLength, y);
    ctx.stroke();

    // Kanan Atas
    ctx.beginPath();
    ctx.moveTo(x + width - cornerLength, y);
    ctx.lineTo(x + width, y);
    ctx.lineTo(x + width, y + cornerLength);
    ctx.stroke();

    // Kiri Bawah
    ctx.beginPath();
    ctx.moveTo(x, y + height - cornerLength);
    ctx.lineTo(x, y + height);
    ctx.lineTo(x + cornerLength, y + height);
    ctx.stroke();

    // Kanan Bawah
    ctx.beginPath();
    ctx.moveTo(x + width - cornerLength, y + height);
    ctx.lineTo(x + width, y + height);
    ctx.lineTo(x + width, y + height - cornerLength);
    ctx.stroke();

    ctx.fillStyle = "rgba(79, 70, 229, 0.05)";
    ctx.fillRect(x, y, width, height);
}

// ================== PROSES DETEKSI ==================
function startDetection() {
    const runDetection = async () => {
        // Jangan jalankan jika sedang memproses data atau model belum siap
        if (!isModelsLoaded || isProcessing) {
            setTimeout(runDetection, 200);
            return;
        }

        if (video.paused || video.ended || !video.srcObject) {
            setTimeout(runDetection, 200);
            return;
        }

        try {
            // Adaptasi ukuran canvas sesuai tampilan video di layar
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

            // Jalankan deteksi wajah tunggal dengan akurasi tinggi
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
                // PERBAIKAN: Hitung skala manual agar kotak deteksi tetap presisi di layar object-fit: cover
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;
                const canvasWidth = displaySize.width;
                const canvasHeight = displaySize.height;

                const xScale = canvasWidth / videoWidth;
                const yScale = canvasHeight / videoHeight;
                const finalScale = Math.max(xScale, yScale); // Meniru object-fit: cover

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

                // Gambar kotak penanda
                drawStylizedBox(ctx, resized.detection.box);

                // Update indikator akurasi di UI
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
            console.error("Kesalahan Deteksi:", err);
        }

        // Jalankan frame berikutnya dengan jeda singkat
        setTimeout(runDetection, 150);
    };

    runDetection();
}

// ================== PENDAFTARAN WAJAH (ENROLL) ==================
enrollBtn.addEventListener("click", async () => {
    // Pastikan wajah siap dan tidak sedang memproses
    if (!lastDetection || isProcessing) return;

    // Ambil ID siswa dari selector (baik desktop maupun mobile)
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

    // --- LOGIKA POTONG WAJAH (SQUARE 1:1) ---
    // Mengambil area wajah saja dengan rasio kotak sempurna agar rapi di database
    const size = Math.max(box.width, box.height);
    const centerX = box.x + box.width / 2;
    const centerY = box.y + box.height / 2;

    const sx = Math.max(centerX - size / 2, 0);
    const sy = Math.max(centerY - size / 2, 0);

    const shot = document.createElement("canvas");
    const outputSize = 300; // Ukuran foto profil akhir
    shot.width = outputSize;
    shot.height = outputSize;

    const ctx = shot.getContext("2d");

    // Crop video sumber ke area wajah yang terdeteksi
    ctx.drawImage(
        video,
        sx,
        sy,
        size,
        size, // Sumber (area wajah)
        0,
        0,
        outputSize,
        outputSize, // Hasil (kotak 300x300)
    );

    try {
        // Kirim foto profil wajah dan data vektor wajah (embedding) ke Server
        const response = await fetch(window.enrollEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": window.csrfToken,
            },
            body: JSON.stringify({
                image: shot.toDataURL("image/jpeg", 0.8), // Hasil foto profil JPG
                face_embedding: Array.from(lastDetection.descriptor), // Data unik wajah
                peserta_didik_id: studentId,
            }),
        });

        const data = await response.json();

        if (data.status === "ok") {
            // Tampilkan layar sukses jika pendaftaran diterima server
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

// Fungsi untuk mereset tampilan pendaftaran agar bisa digunakan kembali
window.resetEnroll = function () {
    successOverlay.classList.remove("show");
    isProcessing = false;
    enrollBtn.disabled = false;
    enrollBtn.innerHTML =
        '<i class="fa fa-user-plus mr-2"></i> DAFTARKAN WAJAH';
    // Reset selector siswa (menggunakan Select2 jika aktif)
    if (studentSelectDesktop)
        $(studentSelectDesktop).val(null).trigger("change");
    if (studentSelectMobile) $(studentSelectMobile).val(null).trigger("change");
};

// Mulai inisialisasi saat struktur dokumen selesai dimuat
document.addEventListener("DOMContentLoaded", init);
