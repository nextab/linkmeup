// Change styles for buttons
wp.domReady( () => {
	wp.blocks.unregisterBlockStyle('core/button', 'outline');
	wp.blocks.unregisterBlockStyle('core/button', 'fill');
})
wp.blocks.registerBlockStyle('core/button', {
	name: 'default-button',
	label: 'Standard',
	isDefault: true
});
wp.blocks.registerBlockStyle('core/button', {
	name: 'outline-button',
	label: 'Umriss'
});
wp.blocks.registerBlockStyle('core/button', {
	name: 'square-button',
	label: 'Eckig'
});
wp.blocks.registerBlockStyle('core/button', {
	name: 'minimal-button',
	label: 'Minimal'
});
wp.blocks.registerBlockStyle('core/button', {
	name: 'ripped-paper-button',
	label: 'Papier-Look'
});

//#region Add reverse order functionality to Columns Block
(function(wp) {
	const { addFilter } = wp.hooks;
	const { createHigherOrderComponent } = wp.compose;
	const { InspectorControls } = wp.blockEditor;
	const { PanelBody, ToggleControl } = wp.components;
	const { __ } = wp.i18n;
	const { createElement, Fragment } = wp.element;

	addFilter(
		'blocks.registerBlockType',
		'core/columns-reverse-order-attribute',
		(settings, name) => {
			if (name !== 'core/columns') {
				return settings;
			}

			return {
				...settings,
				attributes: {
					...settings.attributes,
					reverseOrderOnMobile: {
						type: 'boolean',
						default: false,
					},
				},
			};
		}
	);

	const withCustomSettings = createHigherOrderComponent((BlockEdit) => {
		return (props) => {
			if (props.name !== 'core/columns') {
				return createElement(BlockEdit, props);
			}

			const { attributes, setAttributes } = props;
			const { reverseOrderOnMobile, isStackedOnMobile } = attributes;

			return createElement(
				Fragment,
				null,
				createElement(BlockEdit, props),
				isStackedOnMobile && createElement(
					InspectorControls,
					{ group: 'settings' },
					createElement(ToggleControl, {
						label: __('Reverse order on mobile'),
						checked: reverseOrderOnMobile,
						onChange: (value) => setAttributes({ reverseOrderOnMobile: value }),
					})
				)
			);
		};
	}, 'withCustomSettings');

	addFilter(
		'editor.BlockEdit',
		'core/columns-custom-settings',
		withCustomSettings
	);

	addFilter(
		'blocks.getSaveContent.extraProps',
		'core/columns-reverse-order-save',
		(extraProps, blockType, attributes) => {
			if (blockType.name !== 'core/columns') {
				return extraProps;
			}

			let className = extraProps.className || '';

			if (attributes.reverseOrderOnMobile) {
				className += ' reverse-responsive';
			}

			extraProps.className = className.trim();
			return extraProps;
		}
	);
})(window.wp);
//#endregion