import { useBlockProps } from '@wordpress/block-editor';
import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';

const Edit = () => {
	return <p { ...useBlockProps() }>Hello World</p>;
};

const Save = () => {
	return <p { ...useBlockProps.save() }>Hello World</p>;
};

registerBlockType( metadata, {
	edit: Edit,
	save: Save,
} );
