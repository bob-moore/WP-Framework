/**
 * Wordpress dependencies
 */
const { getAsBooleanFromENV } = require( '@wordpress/scripts/utils' );
/**
 * External dependencies
 */
const path = require( 'path' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
/**
 * Check if the --experimental-modules flag is set.
 */
const hasExperimentalModulesFlag = getAsBooleanFromENV(
	'WP_EXPERIMENTAL_MODULES'
);
/**
 * Get default script config from @wordpress/scripts
 * based on the --experimental-modules flag.
 */
const defaultConfigs = hasExperimentalModulesFlag
	? require( '@wordpress/scripts/config/webpack.config' )
	: [ require( '@wordpress/scripts/config/webpack.config' ) ];
const [ scriptConfig ] = defaultConfigs;
/**
 * Filter plugins from the default config
 */
const plugins = scriptConfig.plugins.filter( ( item ) => {
	return ! [ 'MiniCssExtractPlugin' ].includes( item.constructor.name );
} );

/**
 * Webpack configuration
 */
const assetConfig = {
	...scriptConfig,
	entry: {
		frontend: [
			path.resolve( __dirname, 'src/scripts', 'frontend.ts' ),
			path.resolve( __dirname, 'src/styles', 'frontend.scss' ),
		],
		admin: [
			path.resolve( __dirname, 'src/scripts', 'admin.ts' ),
			path.resolve( __dirname, 'src/styles', 'admin.scss' ),
		],
		editor: [
			path.resolve( __dirname, 'src/scripts', 'editor.ts' ),
			path.resolve( __dirname, 'src/styles', 'editor.scss' ),
		],
		login: [
			path.resolve( __dirname, 'src/scripts', 'login.ts' ),
			path.resolve( __dirname, 'src/styles', 'login.scss' ),
		],
	},
	output: {
		path: path.resolve( __dirname, 'dist/build' ),
		filename: '[name].js',
		clean: true,
	},
	resolve: {
		...scriptConfig.resolve,
		alias: {
			'@images': path.resolve( __dirname, 'src/images' ),
		},
	},
	plugins: [
		...plugins,
		new RemoveEmptyScriptsPlugin(),
		new MiniCssExtractPlugin( { filename: '[name].css' } ),
	],
};

const blockConfigs = defaultConfigs.map( ( config, index ) => ( {
	...config,
	output: {
		...config.output,
		path: path.resolve( __dirname, 'dist/blocks' ),
		filename: '[name].js',
		clean: false,
	},
	resolve: {
		...config.resolve,
		alias: {
			...( config.resolve?.alias ?? {} ),
			'@images': path.resolve( __dirname, 'src/images' ),
		},
	},
} ) );

module.exports = () => {
	return [ ...blockConfigs, assetConfig ];
};
