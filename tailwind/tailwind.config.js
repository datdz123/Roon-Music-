// Set the Preflight flag based on the build target.
const includePreflight = 'editor' === process.env._TW_TARGET ? false : true;

module.exports = {
	presets: [
		// Manage Tailwind Typography's configuration in a separate file.
		require('./tailwind-typography.config.js'),
	],
	content: [
		// Ensure changes to PHP files and `theme.json` trigger a rebuild.
		'./theme/**/*.php',
		'./theme/**/*.js',
	],
	theme: {
		// Extend the default Tailwind theme.
		extend: {
			colors: {
				primary:       '#3b3663',
				secondary:     '#f47514',
				'roon-blue':   '#3b3ef6',
				'roon-indigo': '#2a2cc4',
			},
			fontFamily: {
				body:  ['SVN-Poppins', 'sans-serif'],
				inter: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
			},
			width: {
				'roon-sidebar': '200px',
			},
			height: {
				'roon-player': '72px',
				'roon-header': '52px',
			},
			spacing: {
				'roon-player': '72px',
				'roon-header': '52px',
				'roon-sidebar': '200px',
			},
			minWidth: {
				'roon-sidebar': '200px',
			},
			zIndex: {
				'60': '60',
				'100': '100',
				'200': '200',
			},
		},
		container: {
			center: true,
			padding: '1rem',
			screens: {
				sm: '600px',
				md: '728px',
				lg: '984px',
				xl: '1240px',
				'2xl': '1460px',
			  },
		},
	},
	corePlugins: {
		// Disable Preflight base styles in builds targeting the editor.
		preflight: includePreflight,
	},
	plugins: [
		// Add Tailwind Typography (via _tw fork).
		require('@_tw/typography'),

		// Extract colors and widths from `theme.json`.
		require('@_tw/themejson'),

		// Uncomment below to add additional first-party Tailwind plugins.
		// require('@tailwindcss/forms'),
		// require('@tailwindcss/aspect-ratio'),
		// require('@tailwindcss/container-queries'),
	],
};
