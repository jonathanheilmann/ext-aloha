/**
 * lean on https://github.com/CarouselSMS/Aloha-Plugin-FontSize
 *
 * see http://www.supnig.com/blog/aloha-editor-and-jqueryui for editables
 */
define([
	'aloha',
	'jquery',
	'aloha/plugin',
	'ui/ui',
	'ui/button',
	'i18n!fontSize/nls/i18n',
	'css!fontSize/css/fontSize.css'],
function(
	Aloha,
	jQuery,
	Plugin,
	Ui,
	Button,
	i18n
) {
	"use strict";
	var
		$ = jQuery,
		GENTICS = window.GENTICS;

	/**
	 * @type {Object}
	 */
	var settings = null;

	/**
	 * register the plugin with unique name
	 */
	return Plugin.create('fontSize', {
		languages: ['en', 'de'],

		/**
		 * Initialize the plugin
		 */
		init: function () {
			this.settings = Aloha.require('fontSize/fontSize-plugin').settings;

			var names = ['increaseFontSize', 'decreaseFontSize'];
			this.buttons = {};

			this.createButtons(names);
		},

		/**
		 * Create Buttons
		 */
		createButtons: function(names) {
			var that = this;

			jQuery.each(names, function(index, button) {
				var size = 'small';
				var menuTab = 'Format';

				that.buttons[button] = Ui.adopt(button, Button, {
					tooltip: i18n.t('button.' + button),
					icon: 'button_' + button,
					size : size,
					click: function () {
						if (Aloha.activeEditable) {
							Aloha.activeEditable.obj[0].focus()
						};

						var newSize,
							markup = jQuery('<span></span>'),
							range = Aloha.Selection.getRangeObject(),
							foundMarkup = range.findMarkup(function() {
									return this.nodeName.toLowerCase() == markup.get(0).nodeName.toLowerCase();
							}, Aloha.activeEditable.obj);

						// when the range is collapsed, extend it to a word
						if (range.isCollapsed()) {
							GENTICS.Utils.Dom.extendToWord(range);
							range.select();
						}

						if (foundMarkup) {
  			  				// calculate new size
							newSize = (parseInt(jQuery(range.markupEffectiveAtStart[0]).css('font-size')) + (index === 0?1:-1)) + 'px';
							console.log('newSize: ' + newSize);
							var parentSize = '';
							// get size of parent if available
							console.log(range);
							if (typeof range.markupEffectiveAtStart[1] != 'undefined') {
								parentSize = parseInt(jQuery(range.markupEffectiveAtStart[1]).css('font-size')) + 'px';
								console.log('parentSize: ' + parentSize);
							}
							// only add new markup if the new size does not equal the parent size
							if (newSize != parentSize) {
								jQuery(foundMarkup).css('font-size', newSize);
								console.log('updated font-size');
							} else {
								// remove the markup from the range
								jQuery(foundMarkup).css('font-size', '');
								that.cleanup(foundMarkup, range);
								console.log('default font-size; removed markup');
							}
						} else {
							// calculate new size
							newSize = (parseInt(jQuery(range.markupEffectiveAtStart[0]).css('font-size')) + (index === 0?1:-1)) + 'px';
							markup = jQuery('<span style="font-size:'+newSize+'"></span>');
							// add the markup
							GENTICS.Utils.Dom.addMarkup(range, markup);
						}

						range.select();
						return false;
					}
				});
			});
		},

		cleanup: function(node, range) {
			if (
				jQuery(node+':not([class])') &&
				jQuery(node+':not([style])')
			) {
				GENTICS.Utils.Dom.removeFromDOM(node, range, true);
			};
		},

	});
});