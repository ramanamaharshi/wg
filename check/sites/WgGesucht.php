<?php

class WgGesucht {
	
	
	
	
	public function aGetList () {
		
		$aReturn = array();
		
		$aListUrls = array(
			#'http://www.wg-gesucht.de/1-zimmer-wohnungen-in-Aachen.1.1.0.0.html',
			'http://www.wg-gesucht.de/wohnungen-in-Aachen.1.2.0.0.html',
		);
		
		foreach ($aListUrls as $sListUrl) {
			$oList = MyCurl::oGet($sListUrl);
			$aRows = $oList->find('table#table-compact-list tbody tr');
			foreach ($aRows as $oRow) {
				$oLink = $oRow->find('.row_click a.list', 0);
				if ($oLink) {
					$sHref = 'http://www.wg-gesucht.de/' . $oLink->href;
					$oItem = new StdClass();
					$oItem->sID = $sHref;
					$oItem->sHref = $sHref;
					$aReturn []= $oItem;
				}
			}
		}
		
		return $aReturn;
		
	}
	
	
	
	
	public function oGetItemData ($oItem) {
		
		$oData = new StdClass();
		
ODT::dump($oItem->sHref);
		$oDetail = MyCurl::oGet($oItem->sHref);
		
		$aInfoBlocks = array();
		$aInfoBlockHeads = $oDetail->find('#ang-datasheet h4');
		foreach ($aInfoBlockHeads as $oIBH) {
			$aLIs = array();
			foreach ($oIBH->next_sibling()->children() as $oChild) {
				$aLIs []= self::sNormalize($oChild->plaintext);
			}
			$aInfoBlocks[self::sNormalize($oIBH->plaintext)] = $aLIs;
		}
		$aColonData = array();
		foreach ($aInfoBlocks as $aInfoBlock) {
			foreach ($aInfoBlock as $sRow) {
				$aRow = explode(':', $sRow);
				if (isset($aRow[1])) {
					$aColonData[$aRow[0]] = trim($aRow[1]);
				}
			}
		}
		
		$oData->oCost = new StdClass();
		$oData->oPhysics = new StdClass();
		$oData->oAddress = new StdClass();
		$oData->oRentable = new StdClass();
		
		$aBlockA = $aInfoBlocks['Adresse'];
		$oData->oAddress->sCity = 'Aachen';
		preg_match('/^(?<plz>\d+) /', $aBlockA[0], $aMatchesA);
		$oData->oAddress->sZip = $aMatchesA['plz'];
		$oData->oAddress->sStreet = $aBlockA[1];
		preg_match('/^(?<size>\d+)m² (?<rooms>\d+)( |\-)Zimmer( |\-)Wohnung/', $aBlockA[2], $aMatchesB);
		$oData->oPhysics->iSize = intval($aMatchesB['size']);
		$oData->oPhysics->iRooms = intval($aMatchesB['rooms']);
		$oData->oRentable->sFrom = $aColonData['frei ab'];
		$oData->oRentable->sUntil = isset($aColonData['frei bis']) ? $aColonData['frei bis'] : '';
		$oData->oCost->iCold = intval(str_replace('€', '', $aColonData['Miete']));
		$oData->oCost->iPlus = intval(str_replace('€', '', $aColonData['Nebenkosten']));
		$oData->oCost->iOther = intval(str_replace('€', '', $aColonData['Sonstige Kosten']));
		$oData->oCost->iBail = intval(str_replace('€', '', $aColonData['Kaution']));
		
		$oItem->oData = $oData;
		
		return $oData;
		
	}
	
	
	
	
	private function sNormalize ($sString) {
		
		return preg_replace('/\s+/', ' ', trim(html_entity_decode($sString)));
		
	}
	
	
	
	
}
