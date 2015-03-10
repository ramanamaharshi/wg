<?php


class MyCurl {
	
	
	
	
	static $sDefaultClient = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	
	
	
	
	static function oGet ($sUrl, $sCookieJarFile = null) {
		
		$sResponse = self::sGet($sUrl, $sCookieJarFile);
		
		$oDom = str_get_html($sResponse);
		
		return $oDom;
		
	}
	
	
	
	
	static function sGet ($sUrl, $sCookieJarFile = null) {
		
		$oCurl = curl_init();
		
		curl_setopt($oCurl, CURLOPT_URL, $sUrl);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($oCurl, CURLOPT_USERAGENT, self::$sDefaultClient);
		
		if ($sCookieJarFile) {
			curl_setopt($oCurl, CURLOPT_COOKIEJAR, $sCookieJarFile);
			curl_setopt($oCurl, CURLOPT_COOKIEFILE, $sCookieJarFile);
		}
		
		$sResponse = curl_exec($oCurl);
		
		curl_close($oCurl);
		
		return $sResponse;
		
	}
	
	
	
	
	static function sCreateCookieJar () {
		
		$sFile = self::sBaseDir() . '/cookiejars/' . self::sMicrotime() . '_' . self::sRandomString(8) . '.cookie';
		
		file_put_contents($sFile, '');
		
		return $sFile;
		
	}
	
	

	
	function sRandomString ($iLength, $sChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
		
		$sReturn = '';
		
		for ($i = 0; $i < $iLength; $i++) {
			$sReturn .= $sChars[rand(0, strlen($sChars) - 1)];
		}
		
		return $sReturn;
		
	}
	
	
	
	
	function sMicrotime () {
		
		preg_match('/^(\d+)\.(\d+) (\d+)$/', microtime(), $aMatches);
		
		return $aMatches[3] . $aMatches[2];
		
	}
	
	
	
	
	function sBaseDir () {
		
		return dirname(__FILE__);
		
	}
	
	
	
	
}


?>