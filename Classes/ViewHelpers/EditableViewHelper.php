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
 * Viewhelper to enable aloha for records in fluid
 *
 * Example:
 * <aloha:editable table="tx_news_domain_model_news" field="teaser" uid="{newsItem.uid}" configuration="{allow:'edit,move',class:'alohaeditable-block',nostyles: 1}">
 *		{newsItem.teaser}
 *	</aloha:editable>
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_ViewHelpers_EditableViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render aloha integration for a single field
	 *
	 * @param string $table table of record
	 * @param string $field database field of record
	 * @param integer $uid uid of record
	 * @param array $configuration
	 * @return string
	 */
	public function render($table, $field, $uid, $configuration = array()) {
		$content = $this->renderChildren();

		if (Tx_Aloha_Utility_Access::isEnabled()) {
			$finalConfiguration = array(
				'alohaProcess' => 1,
				'alohaProcess.' => array(
					'field' => $field,
				),
				'stdWrapProcess' => 'Tx_Aloha_Hooks_Editicons->render',
			);

				// add additional configuration
			foreach ($configuration as $key => $value) {
				$finalConfiguration['alohaProcess.'][$key] = $value;
			}

			// Since some templates don't have allow set, for backward compatibilty defaultly set allow 
			if (!isset($finalConfiguration['alohaProcess.']['allow'])) {
				$finalConfiguration['alohaProcess.']['allow'] = 'all';
			}

				// @todo maybe a caching is good
			$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $table, 'uid=' . (int)$uid);

			$cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
			$cObj->start($record, $table);

			$content = $cObj->stdWrap($content, $finalConfiguration);
		}

		return $content;
	}

}

?>