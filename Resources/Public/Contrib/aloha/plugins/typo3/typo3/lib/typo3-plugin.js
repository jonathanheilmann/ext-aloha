define([
	'aloha',
	'jquery',
	'aloha/plugin',
	'ui/ui',
	'ui/scopes',
	'ui/button',
	'i18n!typo3/nls/i18n',
	'i18n!aloha/nls/i18n',
	'css!typo3/css/typo3.css'],
function(
	Aloha,
	jQuery,
	Plugin,
	Ui,
	Scopes,
	Button,
	i18n,
	i18nCore
) {
	"use strict";
	var
		$ = jQuery,
		GENTICS = window.GENTICS;

	/**
	 * register the plugin with unique name
	 */
	return Plugin.create('typo3', {
		languages: ['en'],
		config: [],

		/**
		 * Initialize the plugin
		 */
		init: function () {
			this.createButtons();
			this.subscribeEvents();
		},

		/**
		 * Create the buttons
		 * @todo: Refactor
		 */
		createButtons: function () {
			var that = this;

			this.buttons = {};
			var buttons = [ 'up', 'down', 'edit', 'hide', 'unhide', 'newContentElementBelow', 'move', 'link', 'unlink', 'delete'];

			jQuery.each(buttons, function(j, button) {
				var askUserForAction = false;
				var size = 'small';
				var menuTab = 'Format';

				switch (button) {
					case 'hide':
					case 'unhide':
						askUserForAction = true;
						break;
					case 'delete':
						askUserForAction = true;
						break;
				}

				that.buttons[button] = Ui.adopt(button, Button, {
					tooltip: i18n.t('button.' + button),
					icon: 'button_' + button,
					size : size,
					click: function() {
						var split = Aloha.activeEditable.getId().split('--');
						var customCloseUrl =  typo3BackendUrl + '../typo3conf/ext/aloha/Resources/Public/Contrib/shadowbox/close.html';
						var generalUrl = typo3BackendUrl + 'alt_doc.php?noView=1&returnUrl=' + customCloseUrl + '&';

						if (button == 'edit') {
							var url = generalUrl + 'edit[' + split[0] + '][' + split[2] + ']=edit';
							that.openLightbox(url);
						} else if(button == 'newContentElementBelow') {
							var url = generalUrl + 'edit[' + split[0] + '][-' + split[2] + ']=new';
							that.openLightbox(url);
						} else if(button == 'move') {
							var url = typo3BackendUrl + 'move_el.php?returnUrl=' + customCloseUrl + '&' + 'table=' + split[0] + '&uid=' + split[2];
							that.openLightbox(url);
						} else if(button == 'link') {
							that.newTypolink();
						} else if(button == 'unlink') {
							that.removeTypolink();
							that.saveEditable(Aloha.getActiveEditable().obj[0]);
						} else {
							if (askUserForAction && (confirm(i18n.t('confirm.' + button))) || (!askUserForAction)) {
								that.ajaxRequest(button);
							}
						}
					}
				});

			});

		},

		newTypolink: function ( ) {
			// @todo: check this path
			var url = typo3BackendUrl + '../typo3conf/ext/aloha/Classes/BrowseLink/browse_links.php?mode=rte&act=page',
				range = Aloha.Selection.getRangeObject(),
				link = this.findLinkMarkup( range );

			// If link exists sent curUrl
			if ( link ) {
				var additionalParameter = '&curUrl[href]=' + encodeURIComponent(link.getAttribute('href'));
				if (link.target) additionalParameter += '&curUrl[target]=' + encodeURIComponent(link.target);
				if (link.className) additionalParameter += '&curUrl[class]=' + encodeURIComponent(link.className);
				if (link.title) additionalParameter += '&curUrl[title]=' + encodeURIComponent(link.title);
				url += additionalParameter;
			}

			var vHWin = window.open(url,'FEquickEditWindow', 'width=690,height=500,status=0,menubar=0,scrollbars=1,resizable=1');
			vHWin.focus();
		},

		/**
		 * Insert a new link at the current selection. When the selection is
		 * collapsed, the link will have a default link text, otherwise the
		 * selected text will be the link text.
		 */
		insertTypolink: function ( linkAttr ) {
			var that = this,
			    range = Aloha.Selection.getRangeObject(),
			    link = this.findLinkMarkup( range ),
			    buildLink,
			    linkText,
			    newLink;

			if ( !( range.startContainer && range.endContainer ) ) {
				return;
			}

			// check if it is a header -> set range to whole header-text
			var activeEditable = Aloha.getActiveEditable().obj[0];
			var id = jQuery(activeEditable).attr('id');
			var infos = id.split('--');
			if (infos[0] == 'tt_content' && infos[1] == 'header') {
				range = this.selectDomNode( jQuery(activeEditable).get(0) );
			}

			if ( link ) {
				link.setAttribute('href', linkAttr['href']);
				linkAttr['target'] ? link.setAttribute('target', linkAttr['target']) : link.removeAttribute('target');
				linkAttr['class'] ? link.setAttribute('class', linkAttr['class']) : link.removeAttribute('class');
				linkAttr['title'] ? link.setAttribute('title', linkAttr['title']) : link.removeAttribute('title');
			} else {
				GENTICS.Utils.Dom.extendToWord( range );
				buildLink = '<a href="' + linkAttr['href'] + '"';
				if (linkAttr['target']) buildLink += ' target="' + linkAttr['target'] + '"';
				if (linkAttr['class']) buildLink += ' class="' + linkAttr['class'] + '"';
				if (linkAttr['title']) buildLink += ' title="' + linkAttr['title'] + '"';
				if ( range.isCollapsed() ) {
					// insert a link with text here
					linkText = i18n.t('newlink.defaulttext');
					buildLink += '>' + linkText + '</a>';
					newLink = jQuery(buildLink);
					GENTICS.Utils.Dom.insertIntoDOM( newLink, range, jQuery( Aloha.activeEditable.obj ) );
					range.startContainer = range.endContainer = newLink.contents().get( 0 );
					range.startOffset = 0;
					range.endOffset = linkText.length;
				} else {
					buildLink += '></a>';
					newLink = jQuery(buildLink);
					GENTICS.Utils.Dom.addMarkup( range, newLink, false );
				}

				range.select();

				// because the Aloha Selection is deprecated I need to convert it to a range
				var apiRange = Aloha.createRange();
				apiRange.setStart(range.startContainer, range.startOffset);
				apiRange.setEnd(range.endContainer, range.endOffset);
			}
		},

		/**
		 *
		 */
		removeTypolink: function ( terminateLinkScope ) {
			var that = this,
			    range = Aloha.Selection.getRangeObject(),
			    foundMarkup = that.findLinkMarkup();

			if ( !( range.startContainer && range.endContainer ) ) {
				return;
			}

			if ( foundMarkup ) {
				// remove the link
				GENTICS.Utils.Dom.removeFromDOM( foundMarkup, range, true );

				range.startContainer = range.endContainer;
				range.startOffset = range.endOffset;

				// select the (possibly modified) range
				range.select();

				if ( typeof terminateLinkScope == 'undefined' ||
				              terminateLinkScope === true ) {
				      Scopes.setScope('Aloha.continuoustext');
				}
			}
		},

		/**
		 * Check whether inside a link tag
		 * @param {GENTICS.Utils.RangeObject} range range where to insert the
		 *			object (at start or end)
		 * @return markup
		 * @hide
		 */
		findLinkMarkup: function ( range ) {
			if ( typeof range == 'undefined' ) {
				range = Aloha.Selection.getRangeObject();
			}

			if ( Aloha.activeEditable ) {
				// If the anchor element itself is the editable, we
				// still want to show the link tab.
				var limit = Aloha.activeEditable.obj;
				if (limit[0] && limit[0].nodeName === 'A') {
					limit = limit.parent();
				}
				return range.findMarkup(function () {
					return this.nodeName == 'A';
				}, limit);
			} else {
				return null;
			}
		},

		/**
		 * Select a DOM node
		 * will create a new range which spans the provided dom node and selects it afterwards
		 * @param domObject DOM object
		 * @method
		 */
		selectDomNode: function (domObject) {
			var newRange = new GENTICS.Utils.RangeObject();
			newRange.startContainer = newRange.endContainer = domObject.parentNode;
			newRange.startOffset = GENTICS.Utils.Dom.getIndexInParent(domObject);
			newRange.endOffset = newRange.startOffset + 1;
			newRange.select();
			return newRange;
		},

		/**
		 * Subscribe for events
		 */
		subscribeEvents: function () {
			var that = this;
			Aloha.bind(
				'aloha-editable-activated', function (jEvent, params) {
					//that.disableSaveButton();

					jQuery.each(that.buttons, function(index, button) {
						if (jQuery(params.editable.obj).hasClass('action-' + index)) {
							that.buttons[index].show();
						} else {
							that.buttons[index].hide();
						}
					});
				}
			);

			Aloha.bind(
				'aloha-smart-content-changed',
				function (jEvent, args) {
					that.saveEditable(args.editable);
				}
			);

			Aloha.bind(
				'aloha-typolink-created',
				function (jEvent, args) {
					that.insertTypolink(args['link']);
				}
			);

			Aloha.bind(
				'aloha-typolink-removed',
				function (jEvent, args) {
					that.removeTypolink(args);
				}
			);
		},

		ajaxRequest: function (button) {
			$.ajax({
				url: alohaUrl,
				data: ({
					'action' : button ,
					'identifier': Aloha.activeEditable.getId()
				}),
				cache: false,
				dataType : 'html',
				type: 'POST',

				success: function(xhr){
					$().el7r_notify({
						'text':xhr,
						'skin':'silver'
					});
				},
				error: function(xhr){
					$().el7r_notify({
						'text':xhr.statusText,
						'skin':'darkred'
					});
				}
			});
		},

		saveEditable:function(editable) {
			if (editable.isModified()) {
				var that = this;
				$.ajax({
					url: alohaUrl,
					data: ({
						content: editable.getContents(),
						'identifier': editable.getId(),
						'action':'save'
					}),
					cache: false,
					type: 'POST',

					success: function(xhr){
						$().el7r_notify({
							'text':xhr,
							'skin':'silver'
						});
						editable.setUnmodified();
					},
					error: function(xhr){
						$().el7r_notify({
							'text':xhr.responseText,
							'skin':'darkred'
						});
					}
				});
			}
		},

		enableSaveButton:function() {
			jQuery('#aloha-saveButton').show();
		},
		disableSaveButton:function() {
			jQuery('#aloha-saveButton').hide();
		},

		openLightbox:function(url) {
			Shadowbox.open({ content: url, player:'iframe'});
		}

	});
});