<?php

$GLOBALS['oConfig'] => new StdClass();
function oGetConfig () {
	return $GLOBALS['oConfig'];
}

$oConfig = oGetConfig();

$oConfig->oSql = new StdClass();
$oConfig->oSql->sHost = 'localhost';
$oConfig->oSql->sDaba = 'wohnungssuche';
$oConfig->oSql->sUser = 'wohnungssuche';
$oConfig->oSql->sPass = 'da79ad8cc587a9b26ddcfa254979ba70';

?>