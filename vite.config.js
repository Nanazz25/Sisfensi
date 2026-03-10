import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/attendance.scanner.css",
                "resources/css/profile.css",
                "resources/css/biometric.css",
                "resources/css/dashboard.css",
                "resources/js/app.js",
                "resources/js/global-main.js",
                "resources/js/biometric/attendance.scanner.js",
                "resources/js/biometric/enroll.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
});
