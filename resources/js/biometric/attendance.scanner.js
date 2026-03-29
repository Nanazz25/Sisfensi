// Inisialisasi elemen-elemen UI utamanya
const video = document.getElementById("video");
const canvas = document.getElementById("canvas");
const statusText = document.getElementById("statusText");
const statusDot = document.getElementById("statusDot");
const successOverlay = document.getElementById("successOverlay");
const permissionOverlay = document.getElementById("permissionOverlay");
const btnAbsen = document.getElementById("btnAbsen");

// Ambil konfigurasi dari objek window yang disuntikkan oleh Laravel
const { verifyUrl, csrfToken, dashboardUrl, type } = window.attendanceConfig;

let currentType = type;
let isModelsLoaded = false, // Menandai apakah model AI sudah siap
    isFaceDetected = false, // Menandai apakah ada wajah di depan kamera
    isProcessing = false,   // Menandai proses verifikasi ke server
    userCoords = null,       // Menyimpan koordinat GPS pengguna
    lastDetection = null;    // Menyimpan hasil deteksi wajah terakhir

// Fungsi untuk mendapatkan lokasi GPS pengguna
function getLocation() {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            toastr.error("Browser tidak mendukung GPS.");
            return reject(new Error("No Geolocation Support"));
        }

        // Ambil posisi saat ini
        navigator.geolocation.getCurrentPosition(
            (p) => {
                userCoords = {
                    lat: p.coords.latitude,
                    lng: p.coords.longitude,
                };
                // Update tampilan UI untuk status GPS
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
                // Jika akses ditolak, tampilkan instruksi bantuan
                if (e.code === 1) permissionOverlay.classList.add("show");
                toastr.error("Akses GPS ditolak / bermasalah.");
                reject(e);
            },
            { enableHighAccuracy: true, timeout: 5000 },
        );

        // Terus pantau perubahan posisi pengguna (GPS)
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

// Cek apakah semua izin (Kamera & GPS) sudah diberikan
function checkPermissionsDone() {
    // Sembunyikan overlay bantuan jika Kamera dan GPS sudah siap
    if (video.srcObject && userCoords) {
        permissionOverlay.classList.remove("show");
    }
}

// Inisialisasi awal: Muat model AI dan jalankan Kamera
async function init() {
    try {
        getLocation();
        updateStatus("Sinkronisasi AI...", "default");

        // Muat model Face-API (TinyFace, Landmarks, Recognition)
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri("/models"),
            faceapi.nets.faceLandmark68Net.loadFromUri("/models"),
            faceapi.nets.faceRecognitionNet.loadFromUri("/models"),
        ]);

        isModelsLoaded = true;

        // Minta akses kamera pengguna
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: 1280, height: 720 },
        });
        video.srcObject = stream;
        video.onloadedmetadata = () => {
            video.play();
            checkPermissionsDone();
            startDetection(); // Mulai loop deteksi wajah
        };
    } catch (e) {
        // Tampilkan overlay jika akses ditolak (NotAllowedError/PermissionDeniedError)
        if (
            e.name === "NotAllowedError" ||
            e.name === "PermissionDeniedError"
        ) {
            permissionOverlay.classList.add("show");
        }
        console.error("Kesalahan Kamera:", e);
        toastr.error("Akses kamera ditolak atau tidak terdeteksi");
        updateStatus("Error Kamera", "error");
    }
}

// Fungsi global untuk meminta izin ulang melalui interaksi user (misal klik tombol)
window.requestPermissions = async function (event) {
    if (location.protocol !== "https:" && location.hostname !== "localhost") {
        toastr.error("Kamera & GPS butuh koneksi HTTPS!");
        return;
    }

    console.log("Meminta izin via interaksi pengguna...");
    const btn = event?.currentTarget || event?.target;
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>MEMINTA...';
    }

    try {
        // 1. Picu kamera secara manual (harus di dalam callback user)
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: "user", width: 1280, height: 720 },
        });
        video.srcObject = stream;
        await video.play();

        // 2. Picu izin GPS secara manual
        await getLocation();

        // 3. Lanjutkan sinkronisasi model AI jika belum
        if (!isModelsLoaded) await init();

        checkPermissionsDone();
    } catch (e) {
        console.error("Kesalahan Permintaan Manual:", e);
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

// Update teks status di pojok UI pembaca wajah
function updateStatus(text, type) {
    statusText.innerText = text;
    statusDot.className =
        "dot " + (type === "active" ? "green" : type === "error" ? "red" : "");
}

// Fungsi bantu untuk menggambar kotak deteksi bergaya futuristik di canvas
function drawStylizedBox(ctx, box) {
    const { x, y, width, height } = box;
    const cornerLength = 30; // Panjang siku kotak
    const lineWidth = 4;

    ctx.strokeStyle = "#22c55e"; // Hijau Emerald
    ctx.lineWidth = lineWidth;
    ctx.lineJoin = "round";

    // Sudut Kiri Atas
    ctx.beginPath();
    ctx.moveTo(x, y + cornerLength);
    ctx.lineTo(x, y);
    ctx.lineTo(x + cornerLength, y);
    ctx.stroke();

    // Sudut Kanan Atas
    ctx.beginPath();
    ctx.moveTo(x + width - cornerLength, y);
    ctx.lineTo(x + width, y);
    ctx.lineTo(x + width, y + cornerLength);
    ctx.stroke();

    // Sudut Kiri Bawah
    ctx.beginPath();
    ctx.moveTo(x, y + height - cornerLength);
    ctx.lineTo(x, y + height);
    ctx.lineTo(x + cornerLength, y + height);
    ctx.stroke();

    // Sudut Kanan Bawah
    ctx.beginPath();
    ctx.moveTo(x + width - cornerLength, y + height);
    ctx.lineTo(x + width, y + height);
    ctx.lineTo(x + width, y + height - cornerLength);
    ctx.stroke();

    // Isi kotak dengan warna transparan tipis
    ctx.fillStyle = "rgba(34, 197, 94, 0.05)";
    ctx.fillRect(x, y, width, height);
}

// Mulai loop rekursif deteksi wajah
function startDetection() {
    // Gunakan loop rekursif dengan setTimeout untuk mencegah 'stuck' process
    // yang bisa membebani browser (mencegah memory leak / lag).
    const runDetection = async () => {
        // Jangan jalankan jika model belum siap atau sedang mengirim data ke server
        if (!isModelsLoaded || isProcessing) {
            setTimeout(runDetection, 200);
            return;
        }

        // Cek apakah video sedang aktif
        if (video.paused || video.ended || !video.srcObject) {
            setTimeout(runDetection, 200);
            return;
        }

        try {
            // Resize dinamis: Sesuaikan ukuran canvas dengan tampilan video yang terlihat
            const videoRect = video.getBoundingClientRect();
            const displaySize = {
                width: videoRect.width,
                height: videoRect.height,
            };

            // Optimalisasi: Hanya sinkronkan dimensi jika ukurannya berubah signifikan
            if (displaySize.width > 0 && displaySize.height > 0) {
                if (
                    Math.abs(canvas.width - displaySize.width) > 2 ||
                    Math.abs(canvas.height - displaySize.height) > 2
                ) {
                    faceapi.matchDimensions(canvas, displaySize);
                }
            }

            // Jalankan deteksi wajah tunggal (paling ringan)
            const det = await faceapi
                .detectSingleFace(
                    video,
                    new faceapi.TinyFaceDetectorOptions({
                        inputSize: 160,
                        scoreThreshold: 0.5,
                    }),
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Bersihkan canvas tiap frame

            if (det && displaySize.width > 0) {
                // PERBAIKAN: Hitung skala box manual agar posisi tidak meleset akibat CSS object-fit: cover
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;
                const canvasWidth = displaySize.width;
                const canvasHeight = displaySize.height;

                const xScale = canvasWidth / videoWidth;
                const yScale = canvasHeight / videoHeight;
                const finalScale = Math.max(xScale, yScale); // Meniru perilaku object-fit: cover

                const xOffset = (canvasWidth - videoWidth * finalScale) / 2;
                const yOffset = (canvasHeight - videoHeight * finalScale) / 2;

                const resized = {
                    detection: {
                        box: {
                            x: det.detection.box.x * finalScale + xOffset,
                            y: det.detection.box.y * finalScale + yOffset,
                            width: det.detection.box.width * finalScale,
                            height: det.detection.box.height * finalScale,
                        },
                    },
                };

                // Gambar kotak hijau di wajah yang terdeteksi
                drawStylizedBox(ctx, resized.detection.box);

                if (!isFaceDetected) {
                    updateStatus("Siap Memindai", "active");
                    isFaceDetected = true;
                }
                lastDetection = det;
                // Aktifkan tombol absen hanya jika wajah dan GPS sudah siap
                if (userCoords) btnAbsen.disabled = false;
            } else {
                if (isFaceDetected) {
                    updateStatus("Mencari Wajah...", "default");
                    isFaceDetected = false;
                    btnAbsen.disabled = true;
                }
            }
        } catch (err) {
            console.error("Kesalahan Deteksi:", err);
        }

        // Jalankan frame berikutnya dengan jeda 100ms
        // Memberikan waktu 'napas' bagi browser agar animasi UI tetap smooth
        setTimeout(runDetection, 100);
    };

    runDetection();
}

// Logika saat tombol konfirmasi presensi diklik
btnAbsen.onclick = async () => {
    // Validasi data sebelum mengirim ke server
    if (isProcessing || !lastDetection || !userCoords) return;

    isProcessing = true;
    btnAbsen.disabled = true;
    btnAbsen.innerHTML =
        '<i class="fa fa-refresh fa-spin mr-2"></i>MENCOCOKKAN...';

    // Ambil cuplikan (screenshot) wajah dari video saat ini
    const shot = document.createElement("canvas");
    shot.width = video.videoWidth;
    shot.height = video.videoHeight;
    shot.getContext("2d").drawImage(video, 0, 0);

    try {
        // Kirim data wajah (Base64 & Embedding) serta lokasi ke Server
        const res = await fetch(verifyUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                image: shot.toDataURL("image/jpeg", 0.5), // Cuplikan wajah
                face_embedding: Array.from(lastDetection.descriptor), // Data vektor wajah
                type: currentType, // 'masuk', 'pulang', atau 'mapel'
                latitude: userCoords.lat,
                longitude: userCoords.lng,
            }),
        });

        const data = await res.json();
        if (data.status === "success") {
            // Tampilkan overlay sukses jika presensi berhasil dicatat
            document.getElementById("successName").innerText = data.nama;
            document.getElementById("successTime").innerText =
                "Diterima Pukul " + data.waktu;

            // Beri penanda jika siswa terlambat
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
            // Tampilkan pesan error jika server menolak (wajah tidak kenal, luar area, dsb)
            toastr[data.status === "info" ? "info" : "error"](data.message);
            isProcessing = false;
            btnAbsen.innerHTML =
                '<i class="fa fa-camera mr-2"></i> ' +
                getButtonLabel(currentType);
        }
    } catch (e) {
        toastr.error("Gangguan koneksi server");
        isProcessing = false;
        btnAbsen.innerHTML =
            '<i class="fa fa-camera mr-2"></i> ' + getButtonLabel(currentType);
    }
};

// Fungsi untuk mengganti tipe presensi (Masuk/Mapel/Pulang)
window.setType = function (newType, el) {
    currentType = newType;
    document
        .querySelectorAll(".type-btn")
        .forEach((b) => b.classList.remove("active"));
    el.classList.add("active");

    if (!isProcessing) {
        btnAbsen.innerHTML =
            '<i class="fa fa-camera mr-2"></i> ' + getButtonLabel(currentType);
    }

    toastr.info("Kategori diubah ke: " + newType.toUpperCase());
};

// Label bantuan untuk tombol konfirmasi
function getButtonLabel(type) {
    if (type === "masuk") return "KONFIRMASI HADIR";
    if (type === "mapel") return "KONFIRMASI MAPEL";
    return "KONFIRMASI PULANG";
}

// Reset scanner agar bisa digunakan oleh orang berikutnya
window.resetScanner = function () {
    successOverlay.style.display = "none";
    isProcessing = false;
    btnAbsen.innerHTML =
        '<i class="fa fa-camera mr-2"></i> ' + getButtonLabel(currentType);
};

// Jalankan inisialisasi awal
init();
