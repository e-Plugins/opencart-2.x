<?php

/**
 *  TargetPay plugin v1.1 for Opencart 1.5+
 *  (C) Copyright Yellow Melon 2013
 *
 *  @file       TargetPay Catalog Controller
 *  @author     Yellow Melon B.V. 
 */ 

require_once ("system/helper/targetpay.class.php");

class ControllerPaymentpaybyinvoice extends Controller 
{

    /**
     *		Select bank
     */

	protected function index() 
    {
    	$this->language->load('payment/paybyinvoice');
		
		$this->data['text_credit_card'] = $this->language->get('text_credit_card');
		$this->data['text_wait'] = $this->language->get('text_wait');

		$this->data['entry_bank_id'] = $this->language->get('entry_bank_id');
		$this->data['button_confirm'] = $this->language->get('button_confirm');

        $this->data['custom'] = $this->session->data['order_id'];

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paybyinvoice.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/paybyinvoice.tpl';
			} else {
			$this->template = 'default/template/payment/paybyinvoice.tpl';
		}

		$this->render();
	}

    /**
     *		Save txid/order_id pair in database
     */

    public function storeTxid ($method, $txid, $order_id) 
    {
    	$sql = "INSERT IGNORE INTO `".DB_PREFIX."paybyinvoice` SET ".
        	   "`order_id`='".$this->db->escape($order_id)."', ".
        	   "`method`='".$this->db->escape($method)."', ".
        	   "`paybyinvoice_txid`='".$this->db->escape($txid)."'";
    	$this->db->query ($sql);
   	}

    /**
     *		Get txid/order_id pair from database
     */

    public function getTxid ($order_id, $txid) 
    {
        $sql = "SELECT * FROM `".DB_PREFIX."paybyinvoice` WHERE `order_id`='".$this->db->escape($order_id)."' AND `paybyinvoice_txid`='".$this->db->escape($txid)."'";
    	$result = $this->db->query ($sql);
        return $result->rows[0];
   	}

    /**
     *		Update txid/order_id pair in database
     */

    public function updateTxid ($order_id, $paid, $tpResponse=false) 
    {
    	if ($paid) {
			$sql = "UPDATE `".DB_PREFIX."paybyinvoice` SET `paid`=now() WHERE `order_id`='".$this->db->escape($order_id)."'";
        } else {
			$sql = "UPDATE `".DB_PREFIX."paybyinvoice` SET `paybyinvoice_response`='".$this->db->escape($tpResponse)."' WHERE `order_id`='".$this->db->escape($order_id)."'";
        }
    	$result = $this->db->query ($sql);
    }

    /**
     *      Parse cart to order contents array
     */

    public function parseOrderContents ($cart, $amountToPay) 
    {
        $return = array();

        // Cart items

        $products = $cart->getProducts();
        foreach ($products as $id => $product) {
            $price = $this->tax->calculate($product["price"], $product['tax_class_id'], $this->config->get('config_tax'));
            $tax = $price - $product["price"];
            $tax_rate = $tax / $product["price"] * 100;

            $return[] = array(
                'type' => 1,
                'product' => $product["product_id"],
                'description' => $product["name"],
                'amount' => round($price*100), 
                'quantity' => $product["quantity"],
                'amountvat' => $tax_rate,
                'discount' => 0, // Not available
                'discountvat' => 0 // Not available
            );
        }

        // Calculate shipping etc.

        $shipping = $this->session->data["shipping_method"];
        $shipping_tax = $this->tax->calculate($shipping["cost"], $shipping['tax_class_id'], $this->config->get('config_tax')) - $shipping["cost"];
        $shipping_tax_rate = $shipping_tax / $shipping["cost"] * 100;
        
        $return[] = array(
            'type' => 2,
            'amount' => round(($shipping["cost"] + $shipping_tax)*100), 
            'amountvat' => $shipping_tax_rate,
            'discount' => 0, // Not available
            'discountvat' => 0 // Not available
        );

        // Rest?

        $rest = $amountToPay - $this->cart->getTotal() - $shipping["cost"] - $shipping_tax;

        if ($rest > 0.01) {
            $return[] = array(
                'type' => 4, // Actually we don't know...
                'amount' => round($rest*100),
                'amountvat' => 0, 
                'discount' => 0, // Not available
                'discountvat' => 0 // Not available
            );            
        }

        return $return;
    }


    /**
     *		Start payment
     */

	public function send() 
    {
		$payment_type = 'Sale';

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $rtlo = ($this->config->get('paybyinvoice_rtlo')) ? $this->config->get('paybyinvoice_rtlo') : 93929; // Default TargetPay

        if ($order_info['currency_code']!="EUR") {
			$this->log->write("Invalid currency code ".$order_info['currency_code']);
            $json['error'] = "Invalid currency code ".$order_info['currency_code'];
        } 
        elseif ($order_info['total'] > 1000) {
            $this->log->write("Het bedrag is te hoog, maximaal 1000 euro");
            $json['error'] = "Amount too large, max. 1000 euro";
        } else {            

			$targetPay = new TargetPayCore ("AFT", $rtlo, "e59dbd219e068daade7139be42c5dfd5", $order_info['language_code'], false);
			$targetPay->setAmount ( round($order_info['total'] * 100));
			$targetPay->setDescription ( "Order #". $this->session->data['order_id'] );
      
			$targetPay->setCancelUrl ( $this->url->link('checkout/cart', '', 'SSL') );
			$targetPay->setReturnUrl ( $this->url->link('checkout/success', '', 'SSL') );
    		$targetPay->setReportUrl ( $this->url->link('payment/paybyinvoice/callback', 'order_id='.$this->session->data['order_id'], 'SSL') );
            $targetPay->setCurrency ($order_info['currency_code']);

            $targetPay->bindParam ("cgender", "") // Resume
                    ->bindParam ("cinitials", ucfirst(substr($order_info['firstname'],0,1)).".") // Experimental
                    ->bindParam ("clastname", $order_info['lastname'])
                    ->bindParam ("cbirthdate", "") // Resume
                    ->bindParam ("cbank", "") // Resume
                    ->bindParam ("cphone", $order_info['telephone']) 
                    ->bindParam ("cmobilephone", $order_info['telephone']) 
                    ->bindParam ("cemail", $order_info['email']) 

                    ->bindParam ("order", $this->session->data['order_id']) 
                    ->bindParam ("ordercontents", json_encode($this->parseOrderContents($this->cart, $order_info['total'])))

                    ->bindParam ("invoiceaddress", $order_info['payment_address_1'])
                    ->bindParam ("invoicezip", $order_info['payment_postcode'])
                    ->bindParam ("invoicecity", $order_info['payment_city'])
                    ->bindParam ("invoicecountry", $order_info['payment_iso_code_2'])

                    ->bindParam ("deliveryaddress", $order_info['shipping_address_1'])
                    ->bindParam ("deliveryzip", $order_info['shipping_postcode'])
                    ->bindParam ("deliverycity", $order_info['shipping_city'])
                    ->bindParam ("deliverycountry", $order_info['payment_iso_code_2']);

			$bankUrl = $targetPay->startPayment();

            $this->storeTxid ($targetPay->getPayMethod(), $targetPay->getTransactionId(), $this->session->data['order_id']);

			if (!$bankUrl) {
				$this->log->write('TargetPay start payment failed: '.$targetPay->getErrorMessage());
	            $json['error'] = 'TargetPay start payment failed: '.$targetPay->getErrorMessage();
			} else {
	            $json['success'] = $bankUrl;
	        }
        }

		$this->response->setOutput(json_encode($json));
    }


    /**
     *        Handle payment result
	 */

	public function callback() {

    	$order_id = 0;
    	if (!empty($_GET["order_id"])) {
	     	$order_id = (int) $_GET["order_id"];
        }

    	if (!empty($_GET["amp;order_id"])) {
	     	$order_id = (int) $_GET["amp;order_id"]; // Buggy redirects
        }

        if ($order_id==0) {
			$this->log->write('TargetPay callback(), no order_id passed');
            die();
        }

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

        $targetPayTx = $this->getTxid ($order_id, $_POST["trxid"]);

        if (!$targetPayTx) {
			$this->log->write('Could not find TargetPay transaction data for order_id='.$order_id);
            die();
        }

        $rtlo = ($this->config->get('paybyinvoice_rtlo')) ? $this->config->get('paybyinvoice_rtlo') : 93929; // Default TargetPay

		$targetPay = new TargetPayCore ("AFT", $rtlo, "e59dbd219e068daade7139be42c5dfd5", "nl", false);
		$targetPay->checkPayment ($targetPayTx["paybyinvoice_txid"]);
		
		$order_status_id = $this->config->get('paybyinvoice_order_status_id');
        if (!$order_status_id) {
        	$order_status_id = 1; // Default to 'pending' after payment
        }

        if ($targetPay->getPaidStatus() || $this->config->get('paybyinvoice_test')) {
            $this->updateTxid ($order_id, true);
            $order_status_id = $this->config->get('paybyinvoice_pending_status_id');
            if (!$order_status_id) $order_status_id = 1; // Default to 'pending' after payment
            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
            echo "Paid... ";
            } else {
            echo "Not paid ". $targetPay->getErrorMessage()."... ";
            }

        echo "(Opencart-2.x, 23-04-2015)";
        die();        
        }
}
