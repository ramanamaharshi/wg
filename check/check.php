<?php

require_once('mycurl.php');

require_once('sites/WgGesucht.php');

class Check {
	
	
	
	
	public function vCheck () {
		
		$aList = WgGesucht::aGetList();
		
		foreach ($aList as $oItem) {
			WgGesucht::oGetItemData($oItem);
		}
		
		ODT::vExit($aList);
		
	}
	
	
	
	
}
