(function(window, undefined) {
	window.Aloha = window.Aloha || {};
	// Manually set the version of jQuery for aloha and free the namespace
	var jQuery = window.jQuery.noConflict(true);
	// Hack, so that page edit doesn't throw js error
	window.TS = {
		PATH_typo3: ' '
	};
	window.Aloha.settings = {
		jQuery: jQuery,
		logLevels: {
			'error': true,
			'warn': true,
			'info': true,
			'deprecated': true,
			'debug': false
		},
		errorhandling : false,
		ribbon: false,

		"placeholder": {
			'*': '',
			'#typo3span': 'Placeholder for span'
		},
		"i18n": {
			// you can either let the system detect the users language (set acceptLanguage on server)
			// In PHP this would would be '<?=$_SERVER['HTTP_ACCEPT_LANGUAGE']?>' resulting in
			// "acceptLanguage": 'de-de,de;q=0.8,it;q=0.6,en-us;q=0.7,en;q=0.2'
			// or set current on server side to be in sync with your backend system
			"current": "en"
		},
		toolbar: {
			tabs: [
				// Format Tab
				{
					label: 'tab.format.label',
					showOn: { scope: 'Aloha.continuoustext' },
					components: [
						[
							'bold', 'strong', 'italic', 'emphasis', 'underline', '\n',
							'subscript', 'superscript', 'strikethrough', 'quote', 'link'
						], [
							'formatAbbr', 'formatNumeratedHeaders', 'toggleDragDrop', '\n',
							'toggleMetaView', 'wailang', 'toggleFormatlessPaste'
						], [
							'orderedList', 'unorderedList', 'alignCenter', 'alignJustify', '\n',
							'indentList', 'outdentList', 'alignLeft', 'alignRight', 'colorPicker'
						], [
							'formatBlock'
						], [
							'up', 'newContentElementBelow', 'edit', '\n',
							'down', 'unhide', 'hide', 'delete'
						]
					]
				},
				// Insert Tab
				/*
				{
					label: "tab.insert.label",
					showOn: { scope: 'Aloha.continuoustext' },
					components: [
						[ "createTable", "characterPicker", "insertLink",
						  "insertImage", "insertAbbr", "insertToc",
						  "insertHorizontalRule", "insertTag"]
					]
				}
				*/
			],
			exclude: [ 'tab.format.label', 'tab.insert.label' ]
		},
		"plugins": {
			"format": {
				//'h3' : ['fo', 'bar'],
				// all elements with no specific configuration get this configuration
				//config : [ 'b', 'i', 'h1', 'h2', 'h3', 'h4', 'p', 'edit', 'up', 'down', 'hide', 'unhide', 'newContentElementBelow', 'move','link','delete'],
				config : [ 'b', 'i', 'h1', 'h2', 'h3', 'h4', 'p' ],
				editables : {
					// no formatting allowed for title
					'.nostyles'	: [ ],
					'.heading'	: [ 'h1', 'h2', 'h3', 'h4' ]
				}
			},
			"list": {
				// all elements with no specific configuration get an UL, just for fun :)
				config : [ 'ul', 'ol' ],
				editables : {
					'.nostyles'	: [ ]
				}
			},
			"link": {
				// all elements with no specific configuration may insert links
				config : [ ],
				editables : {
					'.nostyles'	: [ ]
				},
				// all links that match the targetregex will get set the target
				// e.g. ^(?!.*aloha-editor.com).* matches all href except aloha-editor.com
				targetregex : '^(?!.*aloha-editor.com).*',
				// this target is set when either targetregex matches or not set
				// e.g. _blank opens all links in new window
				target : '_blank',
				// the same for css class as for target
				cssclassregex : '^(?!.*aloha-editor.com).*',
				cssclass : 'aloha',
				// use all resources of type website for autosuggest
				objectTypeFilter: ['website'],
				// handle change of href
				onHrefChange: function( obj, href, item ) {
					var jQuery = Aloha.jQuery;
					if ( item ) {
						jQuery(obj).attr('data-name', item.name);
					}
				}
			},
			"table": {
				// all elements with no specific configuration are not allowed to insert tables
				config : [ ],
				editables : {
					// Allow insert tables only into .article
					'.article'	: [ 'table' ],
					'.nostyles'	: [ ]
				},
				// [{name:'green', text:'Green', tooltip:'Green is cool', iconClass:'GENTICS_table GENTICS_button_green', cssClass:'green'}]
				tableConfig : [
					{ name:'hor-minimalist-a' },
					{ name:'box-table-a' },
					{ name:'hor-zebra' },
				],
				columnConfig : [
					{
						name:'bigbold',
						iconClass:'GENTICS_button_col_bigbold'
					},
					{
						name:'redwhite',
						iconClass:'GENTICS_button_col_redwhite'
					}
				],
				rowConfig : [
					{
						name:'bigbold',
						iconClass:'GENTICS_button_row_bigbold'
					},
					{
						name:'redwhite',
						iconClass:'GENTICS_button_row_redwhite'
					}
				]
			}
		}
	};
})(window);