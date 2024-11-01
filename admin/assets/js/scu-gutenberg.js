( function( config, i18n, blocks, editor, blockEditor, element, components, shortcode ) {
	// Using the @wordpress/shortcode module. From .../wp-includes/js/dist/shortcode.js
	// Documented in: https://developer.wordpress.org/block-editor/packages/packages-shortcode/
	// Destructuring components because they will be refered often
	const { __ } = i18n;
	const { registerBlockType } = blocks;
	//const { RichText } = editor;	// Deprecated
	const { RichText, InspectorControls, BlockDescription,
			MediaUpload, MediaUploadCheck } = blockEditor;
	const { createElement, RawHTML} = element;	
	const { PanelBody, PanelRow, SelectControl, RadioControl,
		Radio, RadioGroup,ColorPicker, TimePicker, ToggleControl, RangeControl,
		UnitControl, TextControl, TextareaControl, Text } = components;

	var el = createElement;
	
	let atts_from_scu = [];
	config.forEach ( function(sc_from_config, index) {	
		let tag_start_default = '[scu name="'+sc_from_config.shortcode+'" ';
		sc_from_config.attributes.forEach(function(attribute, i) {
			params = attribute.params.split('|'); 
			tag_start_default += attribute.name + '="'+params[0]+'" ';
		});
		tag_start_default += ']';
		let msg_help;
		if (typeof shortcode.next('scu', tag_start_default) !== 'undefined') {
			shortcode_from_scu = shortcode.next('scu', tag_start_default).shortcode;
			msg_help = __('The shortcode maybe is correct', 'ultimate-shortcodes-creator'); 
		}
		else {
			shortcode_from_scu = false;
			msg_help = __('Invalid Shortcode', 'ultimate-shortcodes-creator');
		}

		atts_from_scu.push(shortcode_from_scu.attrs.named);
		blocks.registerBlockType( 'shortcodes-creator-ultimate/'+sc_from_config.shortcode, {
			title: sc_from_config.shortcode,
			description: sc_from_config.description,
			keywords: ['example', 'test'],
			icon: {
				//background: '#7e70af',
				foreground: '#9c1c92',
				src: sc_from_config.icon
			},
			category: 'scu-shortcodes',

			attributes: {				
				scu_content: {
					type: 'string',		// No source specified so included in the markup when setAttribute()	
					//default: sc_from_config.defaultcontent	
					default: 'null'	
				},
				scu_attributes: {
					type: 'object',
					default: 'null'
					//default: shortcode_from_scu.attrs.named
				}
			},
			edit: ( props ) => {
				const {attributes, setAttributes } = props;

				// Need setAttributes in case default value in scu_content or scu_attributes be saved in the html markup
				if(attributes.scu_content=='null') {
					setAttributes({scu_content: sc_from_config.defaultcontent});
				}
				if(attributes.scu_attributes=='null') {
					setAttributes({scu_attributes: atts_from_scu[index]});
				}

				// Retrieve the tag_string from registerblockType attributes each time editing (including after onChange)
				let tag_start_string = '[scu';
				Object.keys(attributes.scu_attributes).forEach((k, i) => {
					tag_start_string += ' '+k+ '="'+attributes.scu_attributes[k]+'"';
				});
				tag_start_string += ']';

				// Need to recreate a new object from markup in order to be able to update scu_attributes (a registerBlockType attribute)
				let atts_from_markup_tmp = {};
				Object.keys(attributes.scu_attributes).forEach((k, i) => {
					atts_from_markup_tmp[k] = Object.values(attributes.scu_attributes)[i];
				});
				// May be is needed if something should be cheched or consolidated
				let atts_from_scu_tmp = atts_from_scu[index];
				
				let inspector_elements = [];
				sc_from_config.attributes.forEach(function(att, ind) {
					inspector_elements.push(
						el('p', {}, 'Attribute: '+att.name)
					);
					params = att.params.split('|');
					switch(att.type) {
					case 'inputcontrol':						
						inspector_elements.push( 
							el(TextControl, {
								type: 'text',
								help: params[1],
								//label: att.name,
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},								
								value: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'colorpicker':
						inspector_elements.push(							
							el(ColorPicker, {
								onChangeComplete: function (newValue) {
									a = Math.round(newValue.rgb.a * 255).toString(16);
									atts_from_markup_tmp[att.name] = newValue.hex+a;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								color: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'timepicker':
						inspector_elements.push(
							el(TimePicker, {
								onChange: function (newValue) {									
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								currentDate: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'numbercontrol':
						inspector_elements.push(
							el(TextControl, {
								type: 'number',
								min: params[1],
								max: params[2],
								step: params[3],
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								value: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'togglecontrol':
						inspector_elements.push(
							el(ToggleControl, {
								help: attributes.scu_attributes[att.name] ? params[1] : params[2],
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								checked: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'rangecontrol':
						inspector_elements.push(
							el(RangeControl, {
								//allowReset: true,
								//initialPosition: 20,
								//resetFallbackValue: 20,
								min: params[1],
								max: params[2],
								step: params[3],
								help: params[4],
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								value: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'selectcontrol':
						option_values = [];
						for (i = 1; i < params.length; i=i+2) {
							option_values.push({label: params[i], value:params[i+1]});
						}
						inspector_elements.push(
							el(SelectControl, {								
								options: option_values,
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								value: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'radiocontrol':
						option_values = [];
						for (i = 1; i < params.length; i=i+2) {
							option_values.push({label: params[i], value:params[i+1]});
						}
						inspector_elements.push(
							el(RadioControl, {						
								options: option_values,
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},
								selected: attributes.scu_attributes[att.name]
							})
						);
						break;
				/*
					case 'radiogroup':            // RadioGroup doesn't work now 25/06/2020
						radio_els = [];
						for (i = 1; i < params.length; i=i+2) {
							radio_els.push(
								el(Radio, {value: params[i+1]}, params[i])
							);
						}
						inspector_elements.push(
							el(RadioGroup, {}, 
								el(Radio, {value: "25"}, "25%"),
								el(Radio, {value: "50"}, "50%"),
							)
						);
						break;
				*/
					case 'unitcontrol':				// UnitControl doesn't work now 25/06/2020			
						inspector_elements.push( 
							el(TextControl, {
								type: 'number',
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},								
								value: attributes.scu_attributes[att.name]
							})
						);
						break;
					case 'filepicker':			
						inspector_elements.push(
							el('div',{className: 'scu-mediaupload'},
							el(TextControl, {
								type: 'text',
								help: params[1],								
								onChange: function (newValue) {
									atts_from_markup_tmp[att.name] = newValue;
									setAttributes({scu_attributes: atts_from_markup_tmp});
								},								
								value: attributes.scu_attributes[att.name]
							}),
							
							el(MediaUploadCheck, {}, 
							el(MediaUpload, { // (1) Botón de subida
									onSelect: function(media) {
										console.log( media.url);
										atts_from_markup_tmp[att.name] = media.url;
										setAttributes({scu_attributes: atts_from_markup_tmp});
									},									
									//allowedTypes: [ 'images', 'audio' ],
									render: function( obj ) {
										return el( components.Button, {
												className: 'is-secondary is-small',
												onClick: obj.open
											},
											__('Pick File', 'ultimate-shortcodes-creator')
										);
									}
								}
							))),
						);
						break;					
					}	// End of case
				});

				let editor_elements = [];
				if(sc_from_config.description=='Orphaned Shortcode') {
					editor_elements.push(
						el('div', {}, 'Caution: This Shortcode is not available')
					);
				}
				editor_elements.push(
					el(TextareaControl, {						
						label: 'Shortcode Attributes',
						rows: 0,
						onChange: function (content) {								
						},
						//help: 'help',								
						value: tag_start_string
					}),					
					/*
					el(TextControl, {
						type: 'text',
						label: 'Shortcode Attributes',
						onChange: function (content) {								
						},
						//help: 'help',								
						value: tag_start_string
					}),
					*/
				);				
				if(sc_from_config.has_content=="1") {
					editor_elements.push(
						el('label', {}, 'Content'),
						el(RichText, {
							tagName: 'div',							
							//className: props.className,					
							value: attributes.scu_content,
							onChange: function (newtext) {
								setAttributes({ scu_content: newtext });						
							}
						})
						/*
						el(TextareaControl, {
							label: 'Content',
							onChange: function (content) {
								setAttributes( {scu_content: content });						
							},
							//help: 'help',
							value: attributes.scu_content 
						})
						*/
					);
				}
				return ([
					/************* Inspector Control ***************/
					el(InspectorControls,
						'',	 // Fix a problem. I don't know why it is needed
						el(PanelBody, {
							title: __('General', 'ultimate-shortcodes-creator'),
							initialOpen: true
							},						
							el(PanelRow,{className: 'scu-inspector-panelrow'},
								inspector_elements
							),
						),
					),   // End of InspectorControls	

					/************* Editor **************************/
					el('div', {className: 'wp-block-shortcodes-creator-ultimate'},
						editor_elements,						
					)
				]);
			},  // edit
			save: ( props ) => {
				tag_start_string = '[scu';
				Object.keys(props.attributes.scu_attributes).forEach((k, i) => {
					 tag_start_string += ' '+k+ '="'+props.attributes.scu_attributes[k]+'"';
				});
				tag_start_string += ']';				
				if(sc_from_config.has_content=="1") {
					tag_return = tag_start_string+props.attributes.scu_content+'[/scu]';
				}
				else {
					tag_return = tag_start_string;
				}				
				return (
					el(RawHTML, null, tag_return)
				);
			}
		});	   // registerBlockType
	});	 //forEach
	
}(
	scu_gutenberg_config.config,		// From wp_localize_script in class-gutenberg.php
	window.wp.i18n,
	window.wp.blocks,
	window.wp.editor,
	window.wp.blockEditor,
	window.wp.element,	
	window.wp.components,	
	window.wp.shortcode,
	
) );