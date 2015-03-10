<?php




class Store {
	
	
	
	
	public static function vSave ($sUrl, $bPreload) {
		
		$sCookieJar = self::sCreateCookieJar();
		$sResponse = self::sCurl($sUrl, $sCookieJar);
		$sResponse = self::sCurl($sUrl, $sCookieJar);
		
		ODT::vExit($sResponse);
		
	}
	
	
	
	
	public static function sWrite ($sUrl, $sContent) {
		
		$sPagesDir = self::sBaseDir() . '/pages';
		$sFolder = $sPagesDir . '/' . self::sEncodeFilename($sUrl);
		if (!file_exists($sFolder)) mkdir($sFolder);
		
		
		
	}
	
	
	
	
	public static function sRead ($sUrl) {
		
		
		
	}
	
	
	
	
	static function sEncodeFilename ($sFilename) {
		
		$sReturn = '';
		
		$aDontEscape = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
		
		$aFilename = self::aUnicodeStringSplit($sFilename);
		
		foreach ($aFilename as $sChar) {
			if (in_array($sChar, $aDontEscape)) {
				$sReturn .= $sChar;
			} else {
				$sReturn .= '_' . self::iCharToCode($sChar) . '_';
			}
		}
		
		return $sReturn;
		
	}
	
	
	
	
	static function sDecodeFilename ($sFilename) {
		
		$sReturn = '';
		
		$aFilename = Store::aUnicodeStringSplit($sFilename);
		
		for ($iC = 0; $iC < count($aFilename); $iC ++) {
			$sChar = $aFilename[$iC];
			if ($sChar == '_') {
				$iC ++;
				$sCode = '';
				while ($aFilename[$iC] != '_') {
					$sCode .= $aFilename[$iC];
					$iC ++;
				}
				$sChar = self::sCodeToChar(intval($sCode));
			}
			$sReturn .= $sChar;
		}
		
		return $sReturn;
		
	}
	
	
	
	
	static function sCurl ($sUrl, $sCookieJarFile = null) {
		
		$oCurl = curl_init();
		
		curl_setopt($oCurl, CURLOPT_URL, $sUrl);
		
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($oCurl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
		
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
	
	
	
	
	function aUnicodeStringSplit ($sString) {
		
		preg_match_all('/./u', $sString, $aResults);
		
		return $aResults[0];
		
	}
	
	
	
	
	function sCodeToChar ($iCode) {
		
		$sReturn = chr(intval($iCode));
		
		if (function_exists('mb_convert_encoding')) {
			$sReturn = mb_convert_encoding('&#' . intval($iCode).';', 'UTF-8', 'HTML-ENTITIES');
		}
		
		return $sReturn;
		
	}
	
	
	
	
	function iCharToCode ($sChar) {
		
		$iReturn = -1;
		
		$sCharString = mb_substr($sChar, 0, 1, 'utf-8');
		$iSize = strlen($sCharString);        
		$iReturn = ord($sCharString[0]) & (0xFF >> $iSize);
		for ($i = 1; $i < $iSize; $i ++){
			$iReturn = $iReturn << 6 | (ord($sCharString[$i]) & 127);
		}
		
		return $iReturn;
		
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