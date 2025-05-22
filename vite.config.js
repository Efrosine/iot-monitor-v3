import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
     server: {
    host: '172.28.41.90',  // Listen on all network interfaces
    cors: true,        // Enable CORS for all origins
    port: 5173,
  },
});
