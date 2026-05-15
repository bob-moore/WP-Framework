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
	return ! [ 'MiniCssExtractPlugin', 'CopyPlugin' ].includes(
		item.constructor.name
	);
} );

/**
 * Webpack configuration
 */
const assetConfig = {
	...scriptConfig,
	entry: {
		frontend: [
			path.resolve( __dirname, 'src', 'frontend.ts' ),
			path.resolve( __dirname, 'src', 'frontend.scss' ),
		],
		admin: [
			path.resolve( __dirname, 'src', 'admin.ts' ),
			path.resolve( __dirname, 'src', 'admin.scss' ),
		],
		login: [
			path.resolve( __dirname, 'src', 'login.ts' ),
			path.resolve( __dirname, 'src', 'login.scss' ),
		],
	},
	output: {
		path: path.resolve( __dirname, 'build' ),
		filename: '[name].js',
		clean: {
			keep: /^blocks\//,
		},
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

const blockConfigs = defaultConfigs.map( ( config ) => ( {
	...config,
	output: {
		...config.output,
		path: path.resolve( __dirname, 'build/blocks' ),
		filename: '[name].js',
		clean: false,
	},
	resolve: {
		...config.resolve,
		alias: {
			'@images': path.resolve( __dirname, 'src/images' ),
		},
	},
} ) );

module.exports = () => {
	return [ ...blockConfigs, assetConfig ];
};
