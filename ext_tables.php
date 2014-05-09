<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/* * *************
 * TypoScript Files
 */
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Basic', 'Aloha Basic');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript/Modification', 'Aloha Modification');

?>