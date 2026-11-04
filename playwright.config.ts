import { defineConfig, devices } from '@playwright/test';

const baseURL = process.env.BASE_URL ?? 'http://localhost:8080';
const stagingBasicAuth = process.env.STAGING_BASIC_AUTH_USERNAME && process.env.STAGING_BASIC_AUTH_PASSWORD
	? { username: process.env.STAGING_BASIC_AUTH_USERNAME, password: process.env.STAGING_BASIC_AUTH_PASSWORD }
	: undefined;

export default defineConfig({
	testDir: './tests/e2e',
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 1 : 0,
	reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
	use: { baseURL, trace: 'on-first-retry' },
	projects: [
		{ name: 'chromium', testIgnore: /(visual|staging)\.spec\.ts/, use: { ...devices['Desktop Chrome'] } },
		// Visual baselines are opt-in: generate with `pnpm test:visual --update-snapshots`.
		{ name: 'visual', testMatch: /visual\.spec\.ts/, use: { ...devices['Desktop Chrome'] } },
		// Runs only when explicitly targeting separately managed HTTPS staging WordPress.
		{ name: 'staging', testMatch: /staging\.spec\.ts/, use: { ...devices['Desktop Chrome'], ...(stagingBasicAuth ? { httpCredentials: stagingBasicAuth } : {}) } },
	],
});
