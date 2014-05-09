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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Save the changes by using TCEmain
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Aloha_Save {

	/**
	 * @var t3lib_tcemain
	 */
	protected $tce;

	/**
	 * @var t3lib_frontendedit
	 */
	protected $t3lib_frontendedit;

	/**
	 * If set, a javascript reload is added to the response
	 *
	 * @var boolean
	 */
	protected $forceReload = FALSE;

	protected $table;
	protected $uid;
	protected $field;

	/**
	 * Save method, can be none, direct or intermediate
	 * @var string
	 */
	protected $saveMethod;

	public function __construct() {
		$this->tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$this->tce->stripslashes_values = 0;

		$this->t3lib_frontendedit = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\FrontendEditing\\FrontendEditingController');

		$configurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);
		$this->saveMethod = $configurationArray['saveMethod'];
	}

	/**
	 * Initial function which is called by an additional page type
	 * Calls correct function to edit/hide/delete/... record
	 *
	 * @param string $content
	 * @param array $conf plugin configuration
	 * @return string
	 */
	public function start($content, $conf) {
		$request = GeneralUtility::_POST();
		$response = '';

			// aborting save
		if ($this->saveMethod === 'none') {
			return 'This is like saving';
		}

		try {
				// @todo check if this workaround can be solved differently
			$GLOBALS['PAGES_TYPES']['default']['allowedTables'] = 'pages,tt_content';

			if ($request['action'] === 'discardSavings') {
				$response = $this->discardSavings();
			} elseif($request['action'] === 'commitSavings') {
				$response = $this->commitSavings();
			} else {
				$this->init($request);

				switch ($request['action']) {
					case 'save':
						if ($this->saveMethod == 'direct') {
							$response = $this->directSave($request);
						} elseif ($this->saveMethod == 'intermediate') {
							$response = $this->intermediateSave($request);
						} else {
							throw new Exception(Tx_Aloha_Utility_Helper::ll('error.saveMethod'));
						}
						break;
					case 'up':
						$response = $this->move('up');
						break;
					case 'down':
						$response = $this->move('down');
						break;
					case 'hide':
						$response = $this->changeVisibility(1);
						break;
					case 'unhide':
						$response = $this->changeVisibility(0);
						break;
					case 'delete':
						$response = $this->delete();
						break;
					default:
						$errorMsg = sprintf(Tx_Aloha_Utility_Helper::ll('error.response-action'), $request['action']);
						throw new Exception($errorMsg);
				}
			}

				// Add JS reload if needed
			if ($this->forceReload) {
				$response .= '<script type="text/javascript">window.location.reload();</script>';
			}

		} catch (Exception $e) {
			$response = $e->getMessage();
			header('HTTP/1.1 404 Not Found');
		}

		return $response;
	}

	private function commitSavings() {
		$elements = $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id];
		if (!is_array($elements) || count($elements) == 0) {
			$response = 'nothing to be saved';
		} else {

			foreach($elements as $element) {
				$this->directSave(unserialize($element), TRUE);
			}

			$GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id] = array();
			$GLOBALS['BE_USER']->writeUC();

			$response = 'all done
						<script>
							window.alohaQuery("#count").text("0").removeClass("tobesaved");
						</script>';
		}

		return $response;
	}

	private function intermediateSave(array $request) {
		$GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id][$request['identifier']] = serialize($request);
		$GLOBALS['BE_USER']->writeUC();

		$countOfElements = Tx_Aloha_Utility_Integration::getCountOfUnsavedElements($GLOBALS['TSFE']->id);

		$response = 'Press Save button for a real save. <script>
window.alohaQuery("#count").text("' . $countOfElements . '").' . ($countOfElements > 0 ? 'add' : 'remove') . 'Class("tobesaved");
window.alohaQuery("#aloha-saveButton").show();
</script>';
		return $response;
	}

	private function discardSavings() {
		$elements = $GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id];
		if (!is_array($elements) || count($elements) == 0) {
			$response = 'No staged changes, nothing to do. you are fine!';
		} else {

			$GLOBALS['BE_USER']->uc['aloha'][$GLOBALS['TSFE']->id] = array();
			$GLOBALS['BE_USER']->writeUC();

			$this->forceReload = TRUE;

			$response = 'Changes have been discard<script>
							window.alohaQuery("#count").text("0").removeClass("tobesaved");
							</script>';
		}

		return $response;
	}



	/**
	 * Hide/Unhide record
	 *
	 * @param integer $visibility
	 * @return string
	 */
	private function changeVisibility($visibility) {
		$this->forceReload = TRUE;

		if ($visibility == 0) {
			$this->t3lib_frontendedit->doUnhide($this->table, $this->uid);
			return Tx_Aloha_Utility_Helper::ll('response.action.unhide');
		} elseif ($visibility == 1) {
			$this->t3lib_frontendedit->doHide($this->table, $this->uid);
			return Tx_Aloha_Utility_Helper::ll('response.action.hide');
		}
	}

	/**
	 * Delete a record
	 *
	 * @return string
	 */
	private function delete() {
		$this->forceReload = TRUE;
		$this->t3lib_frontendedit->doDelete($this->table, $this->uid);

		return Tx_Aloha_Utility_Helper::ll('response.action.delete');
	}

	/**
	 * Move a table, either up or down (set in $direction)
	 *
	 * @param string $direction
	 * @return string
	 */
	private function move($direction) {
		$this->forceReload = TRUE;

		if ($direction === 'down') {
			$this->t3lib_frontendedit->doDown($this->table, $this->uid);
			return Tx_Aloha_Utility_Helper::ll('response.action.moveDown');
		} elseif($direction === 'up') {
			$this->t3lib_frontendedit->doUp($this->table, $this->uid);
			return Tx_Aloha_Utility_Helper::ll('response.action.moveUp');
		} else {
			throw new Exception(sprintf(Tx_Aloha_Utility_Helper::ll('error.move-action.wrong-direction'), htmlspecialchars($direction)));
		}
	}

	/**
	 * True main function which starts to call tcemain
	 *
	 * @param array $request POST request
	 * @return string
	 */
	public function directSave(array $request, $initAgain = FALSE) {
// PIXELANT HACK - this function needs to be public
			// @todo do that nice again
		if ($initAgain) {
			$this->init($request);
		}

		if (is_array($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'])) {
			$finished = FALSE;
			foreach ($GLOBALS['TYPO3_CONF_VARS']['Aloha']['Classes/Save/Save.php']['requestPreProcess'] as $classData) {
				if (!$finished) {
					$hookObject = GeneralUtility::getUserObj($classData);
					if (!($hookObject instanceof Tx_Aloha_Interfaces_RequestPreProcess)) {
						throw new UnexpectedValueException(
							$classData . ' must implement interface Tx_Aloha_Interfaces_RequestPreProcess',
							1274563549
						);
					}
					$request = $hookObject->preProcess($request, $finished, $this);
				}
			}
		}

		/*
		Aloha automatically encodes entities, but typo3 automatically encodes them too,
		so we have to decode them from Aloha otherwise we would encode twice.
		CHANGED from html_entity_decode to urldecode, after problem with encoding on some servers which broke content and flexform.
		*/
		$htmlEntityDecode = true;

		$request['content'] = Tx_Aloha_Utility_Integration::rteModification($this->table, $this->field, $this->uid, $GLOBALS['TSFE']->id, $request['content']);

		// in case we want to write to flexform
		$fields = explode('-', $this->field, 2);
		if ($this->table == 'tt_content' && $fields[0] == 'pi_flexform' && !is_null($fields[1]))
		{
			$this->field = 'pi_flexform';
			$request['content'] = $this->processFlexform($request,$fields);
		}

		// in case we changed header
		if ($this->table == 'tt_content' && $this->field == 'header' && substr( $request['content'], 0, 2 ) === "<h")
		{
			$request['content'] = $this->processHeader($request);
		}

		if ($htmlEntityDecode) {
			$request['content'] = urldecode($request['content']);
				// Try to remove invalid utf-8 characters so content won't break if there are invalid characters in content
			$request['content'] = iconv("UTF-8", "UTF-8//IGNORE", $request['content']);
		}

		if (!empty($request)) {
			$data = array(
				$this->table => array(
					$this->uid => array(
						$this->field => $request['content']
					)
				)
			);
		}



		$this->tce->start($data, array());
		$this->tce->process_datamap();

		return Tx_Aloha_Utility_Helper::ll('response.action.save');
	}

	/**
	 * Initialize everything
	 *
	 * @param array $request POST request
	 * @return void
	 */
	private function init(array $request) {
			// request is only allowed for POST request and a BE_USER is available
		if (empty($request)) {
			throw new BadFunctionCallException(Tx_Aloha_Utility_Helper::ll('error.request.no-post'));
		} elseif (!Tx_Aloha_Utility_Access::isEnabled()) {
			throw new BadFunctionCallException(Tx_Aloha_Utility_Helper::ll('error.request.not-allowed'));
		}

		$split = explode('--', $request['identifier']);

		if (count($split) != 3) {
			throw new Exception(Tx_Aloha_Utility_Helper::ll('error.request.identifier'));
		} elseif(empty($split[0])) {
			throw new Exception(Tx_Aloha_Utility_Helper::ll('error.request.table'));
		} elseif(empty($split[1])) {
			throw new Exception(Tx_Aloha_Utility_Helper::ll('error.request.field'));
		} elseif (!ctype_digit($split[2])) {
			throw new Exception(Tx_Aloha_Utility_Helper::ll('error.request.uid'));
		}

		$this->table = $split[0];
		$this->field = $split[1];
		$this->uid = (int)$split[2];
		$this->content = $request['content'];
		$this->record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', $this->table, 'uid=' . $this->uid);
	}

	/**
	 * Wrapper function to get uid of record
	 *
	 * @return integer
	 */
	public function getUid() {
		return $this->uid;
	}

	/**
	 * Wrapper function to get field name
	 *
	 * @return string
	 */
	public function getField() {
		return $this->field;
	}

	/**
	 * Wrapper function to get the tablename
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Wrapper function to get record
	 *
	 * @return array
	 */
	public function getRecord() {
		return $this->record;
	}

	private function processFlexform($request,$fields) {
		$xml = new SimpleXMLElement($this->record['pi_flexform']);

			// @TODO: Maybe give possibility for fields to have html tags
		$fieldAllowedTags = '<sup><sub>';
		foreach ($xml->xpath('//T3FlexForms/data/sheet/language/field[@index = "'.$fields[1].'"]') as $entry) {
			$content = trim($request['content']);
			$content = strip_tags(urldecode($content), $fieldAllowedTags);

				// Try to remove invalid characters so save won't break xml if there are invalid characters in string
			$content = iconv("UTF-8", "UTF-8//IGNORE", $content);

			$node = dom_import_simplexml($entry->value);
			$node->nodeValue = "";
			$node->appendChild($node->ownerDocument->createCDATASection($content));
		}

		$request['content'] = $xml->saveXml();

		return $request['content'];
	}

	private function processHeader($request) {
		$content = urldecode(strip_tags($request['content']));
		switch (substr( $request['content'], 0, 4 ))
		{
			case '<h1>':
				$request['content'] = 1;
				break;
			case '<h2>':
				$request['content'] = 2;
				break;
			case '<h3>':
				$request['content'] = 3;
				break;
			case '<h4>':
				$request['content'] = 4;
				break;
			default:
				$request['content'] = 0;
				break;
		}
		$request['identifier'] = $this->table . '--header_layout--' . $this->uid;
		$this->directSave($request,TRUE);
		$this->field = 'header';

		return $content;
	}
}

?>