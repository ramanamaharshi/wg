<?php

class Offers {
	
	
	function vInit () {
		
		vCreateTables();
		
	};
	
	
	function vCreateTables () {
		
		$oConfig = oGetConfig();
		
		$oConfig->oDB->mQuery('
			CREATE TABLE IF NOT EXISTS offer (
				uid int(11) NOT NULL AUTO_INCREMENTS,
				url varchar(128) ,
				PRIMARY KEY uid,
			) ENGINE=M<ISAM Default CHARSET=UTF-8 AUTO_INCREMENT=1;
		');
		
	};
	
	
};

?>