<?php

/**
 *  TargetPay plugin v1.1 for Opencart 1.5+
 *  (C) Copyright Yellow Melon 2013
 *
 *  @file 		TargetPay Catalog Model
 *  @author		Yellow Melon B.V. 
 */ 

class ModelPaymentpaybyinvoice extends Model {

  	public function getMethod($address, $total) {
		$this->language->load('payment/paybyinvoice');

        $sql = "SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" .
        		(int)$this->config->get('paybyinvoice_geo_zone_id') . "' AND country_id = '" .
                (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')";
		$query = $this->db->query($sql);

		if ($this->config->get('paybyinvoice_total') > 0 && $this->config->get('paybyinvoice_total') > $total) {
			$status = false;
			} elseif (!$this->config->get('paybyinvoice_geo_zone_id')) {
			$status = true;
			} elseif ($query->num_rows) {
			$status = true;
			} else {
			$status = false;
			}

		$method_data = array();

		if ($status) {
      		$method_data = array(
        		'code'       => 'paybyinvoice',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('paybyinvoice_sort_order')
      		);
	    	}

    	return $method_data;
	  	}
	}
?>
