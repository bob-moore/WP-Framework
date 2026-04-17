module.exports = {
	plugins: [
		require( 'postcss-assets' )( {
			loadPaths: [ './assets/images/' ],
			relative: true,
		} ),
		require( 'autoprefixer' ),
		require( 'postcss-pxtorem' ),
	],
};
