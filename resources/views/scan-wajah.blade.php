<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Scan Wajah Siswa</title>

    <style>
        body {
            margin: 0;
            background: #020617;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
            font-family: Arial, Helvetica, sans-serif;
        }

        .container {
            position: relative;
            width: 640px;
            height: 520px;
        }

        video {
            transform: scaleX(-1);
        }

        video,
        canvas {
            position: absolute;
            top: 0;
            left: 0;
        }

        .status {
            position: absolute;
            bottom: -40px;
            width: 100%;
            text-align: center;
        }

        button {
            position: absolute;
            bottom: -80px;
            width: 100%;
            padding: 10px;
            background: #2563eb;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div class="container">
        <video id="video" width="640" height="480" autoplay muted></video>
        <canvas id="canvas" width="640" height="480"></canvas>
        <div class="status" id="status">Menyiapkan kamera...</div>
        <button onclick="captureFace()">Ambil Wajah Siswa</button>
    </div>

    <script src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const statusText = document.getElementById('status');
        const ctx = canvas.getContext('2d');

        async function init() {
            statusText.innerText = "Load model AI...";

            await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
            await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
            await faceapi.nets.faceRecognitionNet.loadFromUri('/models');

            statusText.innerText = "Aktifkan kamera...";
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;

            video.onloadedmetadata = () => {
                video.play();
                startDetection();
            };
        }

        function startDetection() {
            const displaySize = { width: video.width, height: video.height };
            faceapi.matchDimensions(canvas, displaySize);

            setInterval(async () => {
                const detections = await faceapi.detectAllFaces(
                    video,
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 160 })
                );

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                if (detections.length > 0) {
                    const resized = faceapi.resizeResults(detections, displaySize);
                    faceapi.draw.drawDetections(canvas, resized);
                    statusText.innerText = "Wajah terdeteksi";
                } else {
                    statusText.innerText = "Tidak ada wajah";
                }
            }, 150);
        }

        async function captureFace() {
            const detection = await faceapi
                .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions({ inputSize: 160 }))
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                alert("Wajah tidak terdeteksi");
                return;
            }

            const descriptor = Array.from(detection.descriptor);

            fetch('/api/simpan-wajah', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    peserta_didik_id: 1042,
                    face_embedding: descriptor
                })
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'Gagal menyimpan wajah');
                    }
                    return data;
                })
                .then(() => {
                    alert("Wajah siswa berhasil disimpan");
                })
                .catch(err => {
                    console.error(err);
                    alert("Error: " + err.message);
                });
        }

        init();
    </script>

</body>

</html>