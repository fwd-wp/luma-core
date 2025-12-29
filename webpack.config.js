const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const { resolve } = require('path'); 

module.exports = {
	...defaultConfig,

	entry: {
		'css/editor': './resources/scss/editor.scss',
		'css/main': './resources/scss/main.scss',
		'css/print': './resources/scss/print.scss',
		'css/woocommerce': './resources/scss/woocommerce.scss',
		'js/main': './resources/js/main.js',
	},

	output: {
		path: resolve(__dirname, 'build'), // this must be absolute
		filename: '[name].js',
	},

	resolve: {
		...defaultConfig.resolve,
		modules: ['resources/blocks', 'node_modules'],
	},
};
