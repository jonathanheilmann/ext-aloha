<?php

interface Tx_Aloha_Interfaces_ToolbarPostProcess {

	/**
	 * Postprocess of toolbar's content
	 *
	 * @param string $content toolbar content
	 * @param Tx_Aloha_Hooks_ContentPostProc $parentObject
	 * @return void
	 */
	public function postProcess(&$content, Tx_Aloha_Hooks_ContentPostProc &$parentObject);

}

?>
