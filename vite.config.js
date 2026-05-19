import path from 'path'
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
import eslint from 'vite-plugin-eslint'
import VueI18nPlugin from '@intlify/unplugin-vue-i18n/vite'

export default defineConfig({
    plugins: [
        vue(),
        eslint(),
        laravel({
            input: [
                'resources/css/main.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        VueI18nPlugin({
            runtimeOnly: false
        })
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    build: {
        rollupOptions: {
          output: {
            chunkFileNames: 'js/pages/[name]-[hash].js',
          },
        },
        minify: 'esbuild'
    },
    
    experimental: {
        renderBuiltUrl(filename, hostType) {
            return { relative: true }
        }
    }
});
