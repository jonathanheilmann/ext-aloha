<?php

/* **************************************************************
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

require_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('aloha') . 'Classes/Interfaces/RequestPreProcess.php');

/**
 * Hook for saving content element "bullets"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Hooks_RequestPreProcess_CeBullets implements Tx_Aloha_Interfaces_RequestPreProcess {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param Tx_Aloha_Aloha_Save $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, Tx_Aloha_Aloha_Save &$parentObject) {
		$record = $parentObject->getRecord();

			// only allowed for bullet element
		if ($parentObject->getTable() === 'tt_content'
				&& $parentObject->getField() == 'bodytext'
				&& $record['CType'] === 'bullets') {

			$finished = TRUE;

			$domDocument = new DOMDocument();
			$domDocument->loadHTML($request['content']);

			$xPath = new DOMXpath($domDocument);

			$liCollection = $xPath->query('//ul/li');

			$tempLiElements = array();
			foreach ($liCollection as $class) {
				$value = trim($class->nodeValue);
				if (!empty($value)) {
					$tempLiElements[] = $value;
				}
			}
			$request['content'] = implode(LF, $tempLiElements);
		}

		return $request;
	}

}

?>