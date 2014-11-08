<?php


$sOdtDir = '/aaa/tools/odt';
if (file_exists($sOdtDir . '/init.php') && !class_exists('OlliInit')) {
	require_once($sOdtDir . '/init.php');
	if (class_exists('OlliInit')) {
		OlliInit::init(dirname(__FILE__), $sOdtDir . '/log');
	}
}


require_once('simple_html_dom.php');


function sCurlGet ($sUrl) {
	
	$sAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';
	
	$oCurl = curl_init();
	
	curl_setopt($oCurl,CURLOPT_URL, $sUrl);
	curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($oCurl, CURLOPT_HEADER, true);
	curl_setopt($oCurl, CURLOPT_USERAGENT, $sAgent);
	curl_setopt($oCurl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($oCurl, CURLOPT_MAXREDIRS, 20); 
	
	$sResponse = curl_exec($oCurl);
	
	curl_close($oCurl);
	
	return $sResponse;
	
}


$aLists = array();
$aLists['1-zimmer-wohnungen-in-Aachen.1.1.0.0.html'] = new StdClass();
$aLists['wohnungen-in-Aachen.1.2.0.0.html'] = new StdClass();

foreach ($aLists as $sFilter => $oList) {
	$oList->aPages = array();
	$sNextPageUrl = 'http://www.wg-gesucht.de/' . $sFilter;
	while ($sNextPageUrl) {
		
		$oPage = new StdClass();
		
		$oPage->sUrl = $sNextPageUrl;
		$oPage->sContent = sCurlGet($oPage->sUrl);
		
		$oPage->oContent = str_get_html($oPage->sContent);
		
		$oTable = $oPage->oContent->find('table#table-compact-list', 0);
		$aColumnLabels = array();
#ODT::vExit($oTable->find('thead tr', 0)->find('th'));
		foreach ($oTable->find('thead tr', 0)->find('th') as $oCol) {
			$aColumnLabels []= trim($oCol->plaintext);
		}
		
		$aItems = array();
		
		$aRows = $oTable->find('tbody tr');
		foreach ($aRows as $oRow) {
			
			$oItem = new StdClass();
			
			$oItem->aColumns = array();
			$aCols = $oRow->find('td');
			for ($iC = 0; $iC < count($aColumnLabels); $iC ++) {
				$sColumnLabel = $aColumnLabels[$iC];
				if (!isset($aCols[$iC])) continue;
				$oCol = $aCols[$iC];
				if ($sColumnLabel) {
					$oItem->aColumns[$sColumnLabel] = trim($oCol->plaintext);
				}
				$oLink = $oCol->find('a', 0);
				if ($oLink) $oItem->sHref = $oLink->href;
			}
			if (!isset($oItem->sHref) || preg_match('/^https?:\/\//', $oItem->sHref)) continue;
			
			$aItems []= $oItem;
			
		}
ODT::vExit($aItems);
		
#ODT::vExit($oPage->sContent, 3);
		
		break;
		
	}
}

