<?php

$sOdtDir = '/zzz/tools/odt';
if (file_exists($sOdtDir . '/init.php') && !class_exists('OlliInit')) {
	require_once($sOdtDir . '/init.php');
	if (class_exists('OlliInit')) {
		OlliInit::init(dirname(__FILE__), 'log');
	}
}

include('check/check.php');
include('store/store.class.php');

require_once('lib/simple_html_dom.php');
require_once('lib/db.php');

#Store::vSave('http://www.wg-gesucht.de/wohnungen-in-Aachen-Aachen.4793153.html');

Check::vCheck();

?>