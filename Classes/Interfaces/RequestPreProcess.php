<?php

interface Tx_Aloha_Interfaces_RequestPreProcess {

	/**
	 * Preprocess the request
	 *
	 * @param array $request save request
	 * @param boolean $finished
	 * @param Tx_Aloha_Aloha_Save $parentObject
	 * @return array
	 */
	public function preProcess(array &$request, &$finished, Tx_Aloha_Aloha_Save &$parentObject);

}

?>
