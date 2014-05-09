<?php
/***************************************************************
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
***************************************************************/

/**
 * Basic helper class
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Utility_Helper {

	/**
	 * Get a unique string by given arguments
	 *
	 * @param string $table table name
	 * @param string $field fieldname
	 * @param string $id uid of element
	 * @return string
	 */
	public static function getUniqueId($table, $field, $id) {
		$out = implode('--', array($table, $field, $id));

		return $out;
	}

	/**
	 * Helper function to translate
	 *
	 * @param string $key
	 * @return string
	 */
	public static function ll($key, $file = 'locallang.xml') {
		$text = $GLOBALS['LANG']->sL('LLL:EXT:aloha/Resources/Private/Language/' . $file . ':' . $key);
		$text = (!empty($text)) ? $text : '??? ' . $key . ' ???';

		return $text;
	}
}
?>
