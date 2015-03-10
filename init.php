<?php

require_once('config.php');
require_once('lib/simple_html_dom.php');
require_once('lib/db.php');

$oConfig = oGetConfig();

$oConfig->oDB = new DB($oConfig->oSql->sHost, $oConfig->oSql->sDaba, $oConfig->oSql->sUser, $oConfig->oSql->sPass);

?>