<?php

/* * *************************************************************
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

define('TYPO3_MOD_PATH', '../typo3conf/ext/aloha/Classes/BrowseLink/');
$BACK_PATH='../../../../../typo3/';
require_once($BACK_PATH . 'init.php');
$LANG->includeLLFile('EXT:lang/locallang_browse_links.xlf');
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('recordlist') . 'Classes/Browser/ElementBrowser.php';
require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('recordlist') . 'Classes/Controller/ElementBrowserController.php';

class BrowseLinks extends \TYPO3\CMS\Recordlist\Controller\ElementBrowserController {
	/**
	 * 
	 *
	 * @var string
	 */
	public $content;
	/**
	 * Modified to use rte only with disabled removeLink
	 *
	 * @return void
	 * @todo Define visibility
	 */
	public function __construct(){
$this->init();
$this->main();
$this->addJsWrap();
$this->printContent();
}
	public function main() {
		// Clear temporary DB mounts
		$tmpMount = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('setTempDBmount');
		if (isset($tmpMount)) {
			$GLOBALS['BE_USER']->setAndSaveSessionData('pageTree_temporaryMountPoint', intval($tmpMount));
		}
		// Set temporary DB mounts
		$tempDBmount = intval($GLOBALS['BE_USER']->getSessionData('pageTree_temporaryMountPoint'));
		if ($tempDBmount) {
			$altMountPoints = $tempDBmount;
		}
		if ($altMountPoints) {
			$GLOBALS['BE_USER']->groupData['webmounts'] = implode(',', array_unique(\TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $altMountPoints)));
			$GLOBALS['WEBMOUNTS'] = $GLOBALS['BE_USER']->returnWebmounts();
		}
		$this->content = '';
		// Setting alternative browsing mounts (ONLY local to browse_links.php this script so they stay "read-only")
		$altMountPoints = trim($GLOBALS['BE_USER']->getTSConfigVal('options.pageTree.altElementBrowserMountPoints'));
		if ($altMountPoints) {
			$GLOBALS['BE_USER']->groupData['webmounts'] = implode(',', array_unique(\TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $altMountPoints)));
			$GLOBALS['WEBMOUNTS'] = $GLOBALS['BE_USER']->returnWebmounts();
		}
		// Setting additional read-only browsing file mounts
		// @todo: add this feature for FAL and TYPO3 6.0
		$altMountPoints = trim($GLOBALS['BE_USER']->getTSConfigVal('options.folderTree.altElementBrowserMountPoints'));
		if ($altMountPoints) {
			$altMountPoints = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $altMountPoints);
			foreach ($altMountPoints as $filePathRelativeToFileadmindir) {
				$GLOBALS['BE_USER']->addFileMount('', $filePathRelativeToFileadmindir, $filePathRelativeToFileadmindir, 1, 'readonly');
			}
			$GLOBALS['BE_USER']->getFileStorages();
			$GLOBALS['FILEMOUNTS'] = $GLOBALS['BE_USER']->groupData['filemounts'];
		}
		// Render type by user func
		$browserRendered = FALSE;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'] as $classRef) {
				$browserRenderObj = \TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($classRef);
				if (is_object($browserRenderObj) && method_exists($browserRenderObj, 'isValid') && method_exists($browserRenderObj, 'render')) {
					if ($browserRenderObj->isValid($this->mode, $this)) {
						$this->content .= $browserRenderObj->render($this->mode, $this);
						$browserRendered = TRUE;
						break;
					}
				}
			}
		}
		// if type was not rendered use default rendering functions
		if (!$browserRendered) {
			$this->browser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Recordlist\\Browser\\ElementBrowser');
			$this->browser->init();
			$modData = $GLOBALS['BE_USER']->getModuleData('browse_links.php', 'ses');
			list($modData, $store) = $this->browser->processSessionData($modData);
			$GLOBALS['BE_USER']->pushModuleData('browse_links.php', $modData);
			// Disable removeLink
			$this->content = $this->browser->main_rte(0);
		}
	}
	/**
	 * Add Js handler
	 *
	 * @return void
	 */
	public function addJsWrap() {
	$needle = '</body>';
	$replace = '
	<script type="text/javascript">
		function renderPopup_addLink(theLink, cur_target, cur_class, cur_title) {
			window.opener.Aloha.trigger("aloha-typolink-created", {
				"link": {
					"href": theLink,
					"target": cur_target,
					"class": cur_class,
					"title": cur_title
				}
			});
			self.close();
		}
	</script></body>';
	$pos = strripos($this->content, $needle);
	$this->content = substr_replace($this->content,$replace,$pos,strlen($needle));
	}
}

// Make instance:
#global $TYPO3_CONF_VARS;
TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('BrowseLinks',$TYPO3_CONF_VARS);
#$SOBE->main();
#$SOBE->addJsWrap();
#$SOBE->printContent();

?>
