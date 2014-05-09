<?php

if (!defined("TYPO3_MODE")) {
	die("Access denied.");
}

	// Add additional stdWrap properties
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['stdWrap'][$_EXTKEY] =
	'EXT:aloha/Classes/Hooks/EditIcons.php:&Tx_Aloha_Hooks_Editicons';

	// Extend admin panel to add extra option
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_adminpanel.php']['extendAdminPanel'][$_EXTKEY] =
	'EXT:aloha/Classes/Hooks/Adminpanel.php:&Tx_Aloha_Hooks_Adminpanel';

	// XCLASS for < 4.6.0 to integrate missing hook
if( t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) <  t3lib_utility_VersionNumber::convertVersionNumberToInteger('4.6.0')) {
	$GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['typo3/sysext/cms/tslib/class.tslib_adminpanel.php'] =
		t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Xclass/Adminpanel.php';
}

	// Override locallang file of admin panel to get own elements into it
$GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride']['EXT:lang/locallang_tsfe.php'][$_EXTKEY] =
	'EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml';

	// Hook to render menu panel
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][$_EXTKEY] =
	'EXT:aloha/Classes/Hooks/ContentPostProc.php:&Tx_Aloha_Hooks_ContentPostProc->main';

/**
 * Hooks to change content before saving back into DB
 */

	// Cleanup
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cleanup'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/Cleanup.php:&Tx_Aloha_Hooks_RequestPreProcess_Cleanup';
	// Save core content element "Bullets"
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjbullets'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeBullets.php:&Tx_Aloha_Hooks_RequestPreProcess_CeBullets';
	// Save core content element "Table"
$GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'][$_EXTKEY . '-cobjtable'] =
	'EXT:aloha/Classes/Hooks/RequestPreProcess/CeTable.php:&Tx_Aloha_Hooks_RequestPreProcess_CeTable';

?>