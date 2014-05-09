<?php

/**************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Georg Ringer <typo3@ringerge.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

require_once(PATH_typo3 . '/sysext/cms/tslib/class.tslib_adminpanel.php');

/**
 * XCLASS into the AdminPanel to show an additional configuration
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class ux_tslib_AdminPanel extends tslib_AdminPanel {

	/**
	 * Creates and returns the HTML code for the Admin Panel in the TSFE frontend.
	 *
	 * @return	string		HTML for the Admin Panel
	 */
	public function display() {
		$GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_tsfe.php');

		$moduleContent = '';

		if ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_top']) {
			if ($this->isAdminModuleEnabled('preview')) {
				$moduleContent .= $this->getPreviewModule();
			}
			if ($this->isAdminModuleEnabled('cache')) {
				$moduleContent .= $this->getCacheModule();
			}
			if ($this->isAdminModuleEnabled('edit')) {
				$moduleContent .= $this->getEditModule();
			}
			if ($this->isAdminModuleEnabled('tsdebug')) {
				$moduleContent .= $this->getTSDebugModule();
			}
			if ($this->isAdminModuleEnabled('info')) {
				$moduleContent .= $this->getInfoModule();
			}
		}

			// XCLASS begin: Add the hook which is available with 4-6
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_adminpanel.php']['extendAdminPanel'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_adminpanel.php']['extendAdminPanel'] as $classRef) {
				$hookObject = t3lib_div::getUserObj($classRef);

				if (!($hookObject instanceof tslib_adminPanelHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface tslib_adminPanelHook', 1311942539);
				}

				$moduleContent .= $hookObject->extendAdminPanel($moduleContent, $this);
			}
		}
			// XCLASS end

		$row = $this->extGetLL('adminPanelTitle') . ': <span class="typo3-adminPanel-beuser">' .
			htmlspecialchars($GLOBALS['BE_USER']->user['username']) . '</span>';

		$isVisible = $GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_top'];
		$cssClassName = 'typo3-adminPanel-panel-' . ($isVisible ? 'open' : 'closed');
		$header = '<tr class="typo3-adminPanel-hRow">' .
				'<td colspan="2" id="typo3-adminPanel-header" class="' . $cssClassName . '">' .
					'<span class="typo3-adminPanel-header-title">' . $row . '</span>' .
					$this->linkSectionHeader('top', '<span class="typo3-adminPanel-header-button"></span>', 'typo3-adminPanel-header-buttonWrapper') .
					'<input type="hidden" name="TSFE_ADMIN_PANEL[display_top]" value="' . $GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_top'] . '" /></td>' .
			'</tr>';

		if ($moduleContent) {
			$footer = '<tr class="typo3-adminPanel-fRow">' .
					'<td colspan="2" id="typo3-adminPanel-footer">' .
						($this->extNeedUpdate ? ' <input class="typo3-adminPanel-update" type="submit" value="' . $this->extGetLL('update') . '" />' : '') . '</td>' .
				'</tr>';
		} else {
			$footer = '';
		}

		$query = !t3lib_div::_GET('id') ? ('<input type="hidden" name="id" value="' . $GLOBALS['TSFE']->id . '" />') : '';
			// the dummy field is needed for Firefox: to force a page reload on submit with must change the form value with JavaScript (see "onsubmit" attribute of the "form" element")
		$query .= '<input type="hidden" name="TSFE_ADMIN_PANEL[DUMMY]" value="" />';
		foreach (t3lib_div::_GET() as $key => $value) {
			if ($key != 'TSFE_ADMIN_PANEL') {
				if (is_array($value)) {
					$query .= $this->getHiddenFields($key, $value);
				} else {
					$query .= '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '" />';
				}
			}
		}

		$out = '
<!--
	TYPO3 admin panel start
-->
<a id="TSFE_ADMIN"></a>
<form id="TSFE_ADMIN_PANEL_FORM" name="TSFE_ADMIN_PANEL_FORM" action="' . htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_REQUEST_SCRIPT')) . '#TSFE_ADMIN" method="get" onsubmit="document.forms.TSFE_ADMIN_PANEL_FORM[\'TSFE_ADMIN_PANEL[DUMMY]\'].value=Math.random().toString().substring(2,8)">' .
$query . '<table class="typo3-adminPanel">' .
		$header . $moduleContent . $footer . '</table></form>';

		if ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_top']) {
			$out .= '<script type="text/javascript" src="t3lib/jsfunc.evalfield.js"></script>';
			$out .= '<script type="text/javascript">/*<![CDATA[*/' . t3lib_div::minifyJavaScript('
				var evalFunc = new evalFunc();
					// TSFEtypo3FormFieldSet()
				function TSFEtypo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue) {	//
					var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
					var theValue = document.TSFE_ADMIN_PANEL_FORM[theField].value;
					if (checkbox && theValue==checkboxValue) {
						document.TSFE_ADMIN_PANEL_FORM[theField+"_hr"].value="";
						alert(theField);
						document.TSFE_ADMIN_PANEL_FORM[theField+"_cb"].checked = "";
					} else {
						document.TSFE_ADMIN_PANEL_FORM[theField+"_hr"].value = evalFunc.outputObjValue(theFObj, theValue);
						if (document.TSFE_ADMIN_PANEL_FORM[theField+"_cb"]) {
							document.TSFE_ADMIN_PANEL_FORM[theField+"_cb"].checked = "on";
						}
					}
				}
					// TSFEtypo3FormFieldGet()
				function TSFEtypo3FormFieldGet(theField, evallist, is_in, checkbox, checkboxValue, checkbox_off) {	//
					var theFObj = new evalFunc_dummy (evallist,is_in, checkbox, checkboxValue);
					if (checkbox_off) {
						document.TSFE_ADMIN_PANEL_FORM[theField].value=checkboxValue;
					}else{
						document.TSFE_ADMIN_PANEL_FORM[theField].value = evalFunc.evalObjValue(theFObj, document.TSFE_ADMIN_PANEL_FORM[theField+"_hr"].value);
					}
					TSFEtypo3FormFieldSet(theField, evallist, is_in, checkbox, checkboxValue);
				}') . '/*]]>*/</script><script language="javascript" type="text/javascript">' .
				$this->extJSCODE . '</script>';
		}

		$out .= '<script src="' . t3lib_div::locationHeaderUrl('t3lib/js/adminpanel.js') . '" type="text/javascript"></script><script type="text/javascript">/*<![CDATA[*/' .
			'typo3AdminPanel = new TYPO3AdminPanel();typo3AdminPanel.init("typo3-adminPanel-header", "TSFE_ADMIN_PANEL_FORM");' .
			'/*]]>*/</script>
<!--
	TYPO3 admin panel end
-->
';

		return $out;
	}

	/**
	 * Wrapper function to make the method public
	 *
	 * @param string $sectionSuffix
	 * @return string
	 */
	public function extGetHead($sectionSuffix) {
		return parent::extGetHead($sectionSuffix);
	}

	/**
	 * Wrapper function to make the method public
	 *
	 * @param string $key
	 * @return string
	 */
	public function extGetLL($key) {
		return parent::extGetLL($key);
	}

}

?>