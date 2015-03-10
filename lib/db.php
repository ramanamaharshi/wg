<?php




class DB {
	
	
	
	
	private $oDB = null;
	
	public $bLog = false;
	public $sLogFile = '';
	public $aTableMap = array();
	
	private $aDoNotEscape = array('NOW()');
	
	
	
	
	public function __construct ($sHost, $sDaba, $sUser, $sPass) {
		
		$this->oDB = mysqli_connect($sHost, $sUser, $sPass, $sDaba);
		if (mysqli_connect_errno($this->oDB)) {
			exit('failed to connect to ' . $sDaba . ' on ' . $sHost);
		}
		
	}
	
	
	
	
	public function __destruct () {
		
		mysqli_close($this->oDB);
		
	}
	
	
	
	
	public function bCreateTable ($sTable, $sPrimary, $aColums) {
		
		$sCI = "\t\t\t";
		
		foreach ($aColums as $sName => $sAttrs) {
			if (is_array($sAttrs)) {
				$sLine = implode(' ', array(
					$aAttrs['sField'],
					$aAttrs['sType'],
					$aAttrs['bNull'] ? 'NULL' : 'NOT NULL',
					($aAttrs['sDefault'] === '') ? '' : 'DEFAULT ' . $aAttrs['sDefault'],
					$aAttrs['sExtra'],
				));
				$aColumnQueries []= $sCI . $sAttrs;
			} else {
				$sLine = $sName . ' ' . $sAttrs;
				$aColumnQueries []= $sCI . $sLine;
			}
		}
		
		$sQuery = '
			CREATE TABLE ' . $sTable . ' (
				' . $sPrimary . ' INT NOT AUTO INCREMENT,
				' . implode("\n", $aColumnsQueries) . ',
				PRIMARY KEY (' . $sPrimary . ')
			);
		';
		
		$bSuccess = $this->mQuery($sQuery);
		
		return $bSuccess;
		
	}
	
	
	
	
	public function bAddColumn ($sTable, $aColumn) {
		
		
		
	}
	
	
	
	
	public function aGetTableColumns ($sTable) {
		
		$aColumnData = $this->aSelectQuery("SHOW COLUMNS FROM " . $sTable);
		
	}
	
	
	
	
	public function aGetTables () {
		
		$aTablesResult = $this->aSelectQuery("SHOW TABLES;");
		
		$aTables = array();
		foreach ($aTablesResult as $oTable) {
			$sTable = $oTable->{'Tables_in_' . $this->sDaba};
			$aTables []= $sTable;
		}
		
		return $aTables;
		
	}
	
	
	
	
	public function oSelectOne ($sTableName, $mWhere = array(), $sSelectFields = '*', $sExtra = '') {
		
		$aRows = $this->aSelect($sTableName, $mWhere, $sSelectFields, $sExtra);
		if (count($aRows) == 0) {
			return null;
		} else {
			return $aRows[0];
		}
		
	}
	
	
	
	
	public function aSelect ($sTableName, $mWhere = array(), $sSelectFields = '*', $sExtra = '') {
		
		$sTableName = $this->sConvertTableName($sTableName);
		
		$sWhere = $this->sMakeWhere($mWhere);
		
		$sQuery = "
			SELECT " . $sSelectFields . " FROM " . $sTableName . "
			" . $sWhere . "
			" . $sExtra . "
		";
		
		$this->vLog($sQuery);
		$aResult = $this->aSelectQuery($sQuery);
		$this->vLog($aResult);
		
		return $aResult;
		
	}
	
	
	
	
	public function aSelectQuery ($sQuery) {
		
		$oResult = $this->mQuery($sQuery);
		
		if ($oResult === false) {
			ODT::ec('ERROR: ' . mysqli_error($this->oDB));
		}
		
		$aReturn = array();
		while ($aRawRow = mysqli_fetch_array($oResult)) {
			$oRow = new stdClass();
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
		
		$sValues = "";
		$sColumns = "";
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
		
		$this->vLog($sQuery);
		$bSuccess = $this->mQuery($sQuery);
		$this->vLog($bSuccess);
		if ($bSuccess === false) {
			ODT::ec('ERROR: ' . mysqli_error($this->oDB));
		}
		if ($bSuccess === false) {
			return false;
		}
		
		return mysqli_insert_id($this->oDB);
		
	}
	
	
	
	
	
	public function bUpdate ($sTableName, $aData, $mWhere = array()) {
		
		$sTableName = $this->sConvertTableName($sTableName);
		
		$sSet = "";
		$bFirst = true;
		foreach ($aData as $sKey => $sValue) {
			if ($bFirst) {
				$bFirst = false;
			} else {
				$sSet .= ", ";
			}
			if (in_array($sValue, $this->aDoNotEscape)) {
				$sEscapedValue = $sValue;
			} else {
				$sEscapedValue = "'" . $this->sEscape($sValue) . "'";
			}
			$sSet .= self::sProcessKey($sKey) . " = " . $sEscapedValue;
		}
		
		$sWhere = $this->sMakeWhere($mWhere);
		
		$sQuery = "
			UPDATE " . $sTableName . "
			SET " . $sSet . "
			" . $sWhere . "
		";
		
		$this->vLog($sQuery);
		$mReturn = $this->mQuery($sQuery);
		$this->vLog($mReturn);
		if ($mReturn === false) {
			ODT::ec('ERROR: ' . mysqli_error($this->oDB));
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
		
		$this->vLog($sQuery);
		$mReturn = $this->mQuery($sQuery);
		$this->vLog($mReturn);
		if ($mReturn === false) {
			ODT::ec('ERROR: ' . mysqli_error($this->oDB));
		}
		
		return $mReturn;
		
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
					$sWhere .= self::sProcessKey($sKey) . " = '" . $this->sEscape($sValue) . "'";
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
	
	
	
	
	public function sEscape ($sInput) {
		
		#return esc_sql($sInput);
		return mysqli_real_escape_string($this->oDB, $sInput);
		
	}
	
	
	
	
	public function vLog ($mInfo) {
		
		if ($this->sLogFile) ODT::log($mInfo, $this->sLogFile);
		if ($this->bLog) ODT::dump($mInfo);
		
	}
	
	
	
	
	public function mQuery ($sQuery) {
		
		$this->vLog($sQuery);
		return mysqli_query($this->oDB, $sQuery);
		
	}
	
	
	
	
}