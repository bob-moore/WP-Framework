import { registerBlockVariation } from '@wordpress/blocks';

// require( './extensions/blockPresets' );
require( './extensions/highlightSpan' );
require( './extensions/formatNoWrap' );

require( './dynamicCoverImage' );

require( './templatePartWrapper' );

require( './maxWidth' );

// registerBlockVariation(
// 	'core/navigation',
// 	{
// 		name: 'vertical-navigation',
// 		title: 'Vertical Navigation',
//         viewScriptModule: 'file:./vertical-navigation.js',
// 	}
// );
