import { defineConfig, type Plugin } from 'vite';
import react from '@vitejs/plugin-react';
import { existsSync, rmSync, writeFileSync } from 'node:fs';
import { resolve } from 'node:path';

const root = resolve(import.meta.dirname, 'theme');
const hotFile = resolve(root, '.vite-hot');
const port = Number(process.env.VITE_PORT ?? 5173);

/**
 * Public URL of the dev server as seen by the browser. In GitHub Codespaces the
 * forwarded port lives on a dedicated hostname; locally it is plain localhost.
 */
const devOrigin = (): string => {
	if (process.env.CODESPACE_NAME && process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN) {
		return `https://${process.env.CODESPACE_NAME}-${port}.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`;
	}
	return `http://localhost:${port}`;
};

/**
 * Writes theme/.vite-hot while the dev server runs so the theme knows to load
 * assets from Vite instead of the production manifest.
 */
const hotFilePlugin = (): Plugin => ({
	name: 'theme-hot-file',
	apply: 'serve',
	configureServer(server) {
		const cleanup = (): void => {
			if (existsSync(hotFile)) {
				rmSync(hotFile);
			}
		};
		server.httpServer?.once('listening', () => writeFileSync(hotFile, devOrigin()));
		server.httpServer?.once('close', cleanup);
		process.once('exit', cleanup);
		process.once('SIGINT', () => process.exit());
		process.once('SIGTERM', () => process.exit());
	},
});

export default defineConfig(({ command }) => ({
	root,
	base: command === 'build' ? './' : '/',
	plugins: [react(), hotFilePlugin()],
	publicDir: false,
	server: {
		host: '0.0.0.0',
		port,
		strictPort: true,
		origin: devOrigin(),
		cors: true,
		hmr: process.env.CODESPACE_NAME ? { clientPort: 443, protocol: 'wss' } : undefined,
	},
	build: {
		outDir: resolve(root, 'dist'),
		emptyOutDir: true,
		manifest: 'manifest.json',
		assetsInlineLimit: 0,
		rollupOptions: {
			input: {
				main: resolve(root, 'src/scripts/main.ts'),
				editor: resolve(root, 'src/styles/editor.css'),
				'react-example': resolve(root, 'src/react/index.tsx'),
			},
		},
	},
}));
