<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/* * *************
 * TypoScript Files
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Basic', 'Aloha Basic');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Modification', 'Aloha Modification');

?>