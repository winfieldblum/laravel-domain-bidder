import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

// DDEV_HOSTNAME is a comma-separated list when additional_hostnames are set.
const ddevHostname = (process.env.DDEV_HOSTNAME || 'domain-bidder.ddev.site')
    .split(',')[0]
    .trim();
const ddevPrimary =
    process.env.DDEV_PRIMARY_URL || `https://${ddevHostname}`;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        inertia({
            ssr: false,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: {
            origin: [
                ddevPrimary,
                'https://domain-bidder.ddev.site',
                'https://agentic.io.ddev.site',
                'https://onlinescrums.com.ddev.site',
                'http://domain-bidder.ddev.site',
                'http://agentic.io.ddev.site',
                'http://onlinescrums.com.ddev.site',
            ],
        },
        origin: `${ddevPrimary}:5173`,
        hmr: {
            host: ddevHostname,
            protocol: 'wss',
            clientPort: 5173,
        },
    },
});
