<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

$GLOBALS['TYPO3_USER_SETTINGS']['columns']['aloha_enableFrontendEditing'] = array(
	'type' => 'check',
	'label' => 'LLL:EXT:aloha/Resources/Private/Language/locallang.xml:userConfiguration.enableFrontendEditing'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToUserSettings(
	'aloha_enableFrontendEditing',
	'before:edit_RTE'
);

/* * *************
 * TypoScript Files
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Basic', 'Aloha Basic');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Modification', 'Aloha Modification');

?>