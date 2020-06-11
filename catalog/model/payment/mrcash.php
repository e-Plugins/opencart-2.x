<?php

/**
 *
 *	iDEALplugins.nl
 *  TargetPay plugin for Opencart 2.0+
 *
 *  (C) Copyright Yellow Melon 2014
 *
 *	@file 		TargetPay Catalog Model
 *	@author		Yellow Melon B.V. / www.idealplugins.nl
 *
 */

class ModelPaymentMrcash extends Model 
{

	public $currencies = array('EUR');
	public $minimumAmount = 0.49;
	public $maximumAmount = 5000;

  	public function getMethod($address, $total) 
  	{
		$this->load->language('payment/mrcash');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . 
			(int)$this->config->get('mrcash_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . 
			"' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('mrcash_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('mrcash_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		if (!in_array(strtoupper($this->currency->getCode()), $this->currencies)) $status = false;
		
		$method_data = array();

		if ($status) {
      		$method_data = array(
        		'code'       => 'mrcash',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('mrcash_sort_order'),
				'terms'		 => '',
      		);
	    }

    	return $method_data;
  	}
}
