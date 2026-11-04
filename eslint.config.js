import js from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import reactHooks from 'eslint-plugin-react-hooks';

export default tseslint.config(
	{ ignores: ['node_modules/', 'vendor/', 'build/', 'theme/dist/', 'playwright-report/', 'test-results/'] },
	js.configs.recommended,
	...tseslint.configs.recommended,
	{
		files: ['theme/src/**/*.{ts,tsx}'],
		languageOptions: { globals: globals.browser },
		plugins: { 'react-hooks': reactHooks },
		rules: reactHooks.configs.recommended.rules,
	},
	{
		files: ['**/*.{js,mjs,ts}'],
		ignores: ['theme/src/**'],
		languageOptions: { globals: globals.node },
	}
);
