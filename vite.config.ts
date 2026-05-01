import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/twill-admin-addons.css',
                'resources/js/app.tsx',
                'resources/js/block-preview.tsx',
                'resources/js/module-preview.tsx',
            ],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
            command: process.env.WAYFINDER_SKIP
                ? 'true'
                : 'php artisan wayfinder:generate',
        }),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
