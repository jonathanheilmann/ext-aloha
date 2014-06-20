<?php

/**
 * Hook to enable additional stdWrap function "aloha"
 *
 * @package TYPO3
 * @subpackage tx_aloha
 */
class Tx_Aloha_Hooks_Adminpanel implements tslib_adminPanelHook {

	const ll = 'LLL:EXT:aloha/Resources/Private/Language/locallang.xml:';

	/**
	 * @var tslib_AdminPanel
	 */
	protected $tslib_AdminPanel = NULL;

	/**
	 *
	 * @param \TYPO3\CMS\Frontend\View\AdminPanelView $tslib_AdminPanel
	 * @return string
	 */
	public function extendAdminPanel ($moduleContent, \TYPO3\CMS\Frontend\View\AdminPanelView $tslib_AdminPanel) {
		$this->tslib_AdminPanel = $tslib_AdminPanel;

		$GLOBALS['TSFE']->set_no_cache();
		$GLOBALS['TSFE']->displayFieldEditIcons = 1;
		$GLOBALS['TSFE']->forceTemplateParsing = $GLOBALS['TSFE']->displayEditIcons = $GLOBALS['TSFE']->displayFieldEditIcons = 1;

		return $this->getAlohaModule();
	}


	/**
	 * Creates the content for the "tsdebug" section ("module") of the Admin Panel
	 *
	 * @return	string		HTML content for the section. Consists of a string with table-rows with four columns.
	 */
	protected function getAlohaModule() {
		$out = $this->tslib_AdminPanel->extGetHead('aloha');

		$GLOBALS['TSFE']->set_no_cache();

		if ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['display_aloha']) {

			$errors = array();
			if ($GLOBALS['BE_USER']->userTS['aloha'] != 1) {
				$errors[] = ' - ' . $GLOBALS['LANG']->sL(self::ll . 'admPanel.error_userTsConfig', TRUE);
			}
			if ($GLOBALS['TSFE']->config['config']['aloha'] != 1) {
				$errors[] = ' - ' . $GLOBALS['LANG']->sL(self::ll . 'admPanel.error_typoScript', TRUE);
			}

			$configurationArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['aloha']);
			if (!is_array($configurationArray) || empty($configurationArray['saveMethod'])) {
				$errors[] = ' - ' . $GLOBALS['LANG']->sL(self::ll . 'error.saveMethod', TRUE);
			}

			$innerContent = '';
			if (!empty($errors)) {
				$innerContent .= '<div style="line-height:18px;white-space:normal;color:#333;padding:12px 12px 8px 8px;">
									<strong style="font-weight:bold">' .
										$GLOBALS['LANG']->sL(self::ll . 'admPanel.error_title', TRUE) .
									'</strong><br />' .
									implode('<br />', $errors) .
								'</div>';
			} else {
				$enabled = ($GLOBALS['BE_USER']->uc['TSFE_adminConfig']['aloha'] ? ' checked="checked"' : '');
				$innerContent .= $GLOBALS['LANG']->sL(self::ll . 'admPanel.enable', TRUE) .
									'<input type="hidden" name="TSFE_ADMIN_PANEL[aloha]" value="0" />
									<input id="enablealohaeditor" type="checkbox" name="TSFE_ADMIN_PANEL[aloha]" value="1"' . $enabled . ' />
									<input class="typo3-adminPanel-update" style="float:none;" type="submit" value="' . $GLOBALS['LANG']->getLL('update') . '" />';
			}

				// Put all together
			$out .= '<tr class="typo3-adminPanel-itemRow">
						<td class="typo3-adminPanel-section-content aloha-adminPanel">
							<label for="enablealohaeditor">' .
								$innerContent .
							'</label>
						</td>
					</tr>';
		}

		return $out;
	}

}

?>
