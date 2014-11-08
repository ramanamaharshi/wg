<?php




class DirectDB {
	
	
	
	
	private $oMysqli = null;
	
	private $aDoNotEscape = array('NOW()');
	
	public $aTableMap = array();
	
	public $sEscapeTable = '';
	
	public $bDump = false;
	public $sLogFile = '';
	
	
	
	
	public function __construct ($aAccessData) {
		
		if (!isset($aAccessData['sHost'])) {
			$aAccessData['sHost'] = 'localhost';
		}
		
		$this->oMysqli = mysqli_connect($aAccessData['sHost'], $aAccessData['sUser'], $aAccessData['sPass'], $aAccessData['sDaba']);
		
	}
	
	
	
	
	public function aGetTables () {
		
		$aTablesResult = $this->aSelectQuery("SHOW TABLES;");
		
		$aTables = array();
		foreach ($aTablesResult as $oTable) {
			foreach ($oTable as $sKey => $sValue) {
				$sTable = $sValue;
				break;
			}
			$aTables []= $sTable;
		}
		
		return $aTables;
		
	}
	
	
	
	
	public function oSelectOne ($sTableName, $mWhere = array(), $sSelectFields = '*') {
		
		$aRows = $this->aSelect($sTableName, $mWhere, $sSelectFields);
		if (count($aRows) == 0) {
			return null;
		} else {
			return $aRows[0];
		}
		
	}
	
	
	
	
	
	public function aSelect ($sTableName, $mWhere = array(), $sSelectFields = '*') {
		
		$sTableName = $this->sConvertTableName($sTableName);
		
		$sWhere = $this->sMakeWhere($mWhere);
		
		$sQuery = "
			SELECT " . $sSelectFields . " FROM " . $sTableName . "
			" . $sWhere . "
		";
		$this->info($sQuery);
		$aResult = $this->aSelectQuery($sQuery);
		$this->info($aResult);
		return $aResult;
		
	}
	
	
	
	
	public function aSelectQuery ($sQuery) {
		
		$oResult = $this->query($sQuery);
		if ($oResult === false) {
			$this->vAutoError();
		}
		$aReturn = array();
		while ($aRawRow = mysqli_fetch_array($oResult)) {
			$oRow = new \stdClass();
			foreach ($aRawRow as $sKey => $sValue) {
				if (is_string($sKey)) {
					$oRow->$sKey = $sValue;
				}
			}
			$aReturn []= $oRow;
		}
		return $aReturn;
		
	}
	
	
	
	
	
	public function iInsert ($sTableName, $mData) {
		
		$sTableName = $this->sConvertTableName($sTableName);
		
		$sColumns = "";
		$sValues = "";
		$bFirst = true;
		foreach ($mData as $sKey => $sValue) {
			if ($bFirst) {
				$bFirst = false;
			} else {
				$sColumns .= ",";
				$sValues .= ",";
			}
			if (in_array($sValue, $this->aDoNotEscape)) {
				$sEscapedValue = $sValue;
			} else {
				$sEscapedValue = "'" . $this->sEscape($sValue) . "'";
			}
			$sColumns .= self::sProcessKey($sKey);
			$sValues .= $sEscapedValue;
		}
		$sColumns = "(" . $sColumns . ")";
		$sValues = "(" . $sValues . ")";
		
		$sQuery = "
			INSERT INTO " . $sTableName . "
			" . $sColumns . "
			VALUES " . $sValues . "
		";
		
		$this->info($sQuery);
		$bSuccess = $this->query($sQuery);
		$this->info($bSuccess);
		if ($bSuccess === false) {
			$this->vAutoError();
		}
		if ($bSuccess === false) {
			return false;
		}
		return mysqli_insert_id($this->getDB());
		
	}
	
	
	
	
	
	public function bUpdate ($sTableName, $mData, $mWhere = array()) {
		
		$sTableName = $this->sConvertTableName($sTableName);
		
		$sSet = "";
		$bFirst = true;
		foreach ($mData as $sKey => $sValue) {
			if ($bFirst) {
				$bFirst = false;
			} else {
				$sSet .= ",";
			}
			if (in_array($sValue, $this->aDoNotEscape)) {
				$sEscapedValue = $sValue;
			} else {
				$sEscapedValue = "'" . $this->sEscape($sValue) . "'";
			}
			$sSet .= self::sProcessKey($sKey) . "=" . $sEscapedValue;
		}
		
		$sWhere = $this->sMakeWhere($mWhere);
		
		$sQuery = "
			UPDATE " . $sTableName . "
			SET " . $sSet . "
			" . $sWhere . "
		";
		
		$this->info($sQuery);
		$mReturn = $this->query($sQuery);
		$this->info($mReturn);
		if ($mReturn === false) {
			$this->vAutoError();
		}
		return $mReturn;
		
	}
	
	
	
	
	
	public function bDelete ($sTableName, $mWhere = array()) {
		
		$sTableName = $this->sConvertTableName($sTableName);
		
		$sWhere = $this->sMakeWhere($mWhere);
		
		$sQuery = "
			DELETE FROM `" . $sTableName . "`
			" . $sWhere . "
		";
		
		$this->info($sQuery);
		$mReturn = $this->query($sQuery);
		$this->info($mReturn);
		if ($mReturn === false) {
			$this->vAutoError();
		}
		return $mReturn;
		
	}
	
	
	
	
	public function vAutoError () {
		if (class_exists('\ODT')) {
			if (get_class($this->getDB()) == 'mysqli') {
				$sError = $this->getDB()->error;
			} else {
				$sError = $this->getDB()->sql_error();
			}
			\ODT::ec('ERROR: ' . $sError);
			\ODT::ec($sQuery);
		}
	}
	
	
	
	
	public function query ($sQuery) {
		if (get_class($this->getDB()) == 'mysqli') {
			return $this->getDB()->query($sQuery);
		} else {
			return $this->getDB()->sql_query($sQuery);
		}
	}
	
	
	
	
	public function sEscape ($mValue) {
		if (get_class($this->getDB()) == 'mysqli') {
			$mValue = $this->getDB()->real_escape_string($mValue);
		} else {
			$mValue = $this->getDB()->quoteStr($mValue, $this->sEscapeTable);
		}
		return $mValue;
	}
	
	
	
	
	public function getDB () {
		return $this->oMysqli;
	}
	
	
	
	
	public function __destruct () {
		mysqli_close($this->oMysqli);
	}
	
	
	
	
	public function sMakeWhere ($mWhere) {
		
		if (is_array($mWhere)) {
			$sWhere = "";
			$bFirst = true;
			foreach ($mWhere as $sKey => $mValue) {
				if ($bFirst) {
					$bFirst = false;
					$sWhere .= "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				if (is_array($mValue)) {
					$aValues = array();
					foreach ($mValue as $sValue) {
						$aValues []= "'" . $this->sEscape($sValue) . "'";
					}
					$sWhere .= self::sProcessKey($sKey) . " IN (" . implode(',', $aValues) . ")";
				} else {
					$sValue = $mValue;
					$sWhere .= self::sProcessKey($sKey) . "='" . $this->sEscape($sValue) . "'";
				}
			}
		} else {
			$sWhere = "WHERE " . $mWhere;
		}
		return $sWhere;
		
	}
	
	
	
	
	private static function sProcessKey ($sKey) {
		
		if (strstr($sKey, '.' === false)) {
			$sKey = "`" . $sKey . "`";
		} else {
			$aKeyParts = explode('.', $sKey);
			foreach ($aKeyParts as $i => $sKeyPart) {
				$aKeyParts[$i] = "`" . $sKeyPart . "`";
			}
			$sKey = implode('.', $aKeyParts);
		}
		return $sKey;
		
	}
	
	
	
	
	public function sConvertTableName ($sTableName) {
		
		if (isset($this->aTableMap[$sTableName])) {
			$sTableName = $this->aTableMap[$sTableName];
		}
		return $sTableName;
		
	}
	
	
	
	
	
	public function info ($mInfo) {
		if (class_exists('\ODT')) {
			if ($this->bDump) \ODT::dump($mInfo);
			if ($this->sLogFile) \ODT::log($mInfo, $this->sLogFile);
		}
	}
	
	
	
	
}

