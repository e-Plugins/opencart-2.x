<?php

/**
 *
 *	iDEALplugins.nl
 *  TargetPay plugin for Opencart 2.0+
 *
 *  (C) Copyright Yellow Melon 2014
 *
 *	@file 		TargetPay Admin Model
 *	@author		Yellow Melon B.V. / www.idealplugins.nl
 *
 */

class ModelPaymentideal extends Model 
{

  	public function createTable() 
  	{
		$sql = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ideal` (
				`order_id` VARCHAR(64) DEFAULT NULL,
			    `method` VARCHAR(6) DEFAULT NULL,
				`ideal_txid` VARCHAR(64) DEFAULT NULL,
			    `ideal_response` VARCHAR(128) DEFAULT NULL,
			    `paid` DATETIME DEFAULT NULL,
				PRIMARY KEY (`order_id`, `ideal_txid`))";

    	$result = $this->db->query ($sql);
	}

}
