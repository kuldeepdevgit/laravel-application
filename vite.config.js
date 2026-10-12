import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import collectModuleAssetsPaths from './vite-module-loader.js';

export default defineConfig(async () => {
    const paths = [
        'resources/css/app.css',
        'resources/js/app.js',
    ];

    return {
        plugins: [
            laravel({
                input: await collectModuleAssetsPaths(paths, 'Modules'),
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            tailwindcss(),
        ],
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
