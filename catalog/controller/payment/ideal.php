<?php

/**
 *  iDEALplugins.nl
 *  TargetPay plugin for Opencart 2.0+
 *
 *  (C) Copyright Yellow Melon 2014
 *
 *  @file       TargetPay Catalog Controller
 *  @author     Yellow Melon B.V. / www.sofortplugins.nl
 *  @release    5 nov 2014
 */

require_once ("system/helper/targetpay.class.php");

class ControllerPaymentIdeal extends Controller 
    {

    /**
     *		Select bank
     */

	public function index() 
    {
    	$this->language->load('payment/ideal');
		
		$data['text_title'] = $this->language->get('text_title');
		$data['text_wait'] = $this->language->get('text_wait');

		$data['entry_bank_id'] = $this->language->get('entry_bank_id');
		$data['button_confirm'] = $this->language->get('button_confirm');

        $data['custom'] = $this->session->data['order_id'];

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ideal.tpl')) {
            return $this->load->view($this->config->get('config_template') . '/template/payment/ideal.tpl', $data);
        } else {
            return $this->load->view('default/template/payment/ideal.tpl', $data);
        }
	}

    /**
     *		Save txid/order_id pair in database
     */

    public function storeTxid ($method, $txid, $order_id) 
    {
        $sql = "INSERT INTO `".DB_PREFIX."ideal` SET ".
               "`order_id`='".$this->db->escape($order_id)."', ".
               "`method`='".$this->db->escape($method)."', ".
               "`ideal_txid`='".$this->db->escape($txid)."'";
        $this->db->query ($sql);
    }

    /**
     *		Get txid/order_id pair from database
     */

    public function getTxid ($order_id, $txid) 
    {
        $sql = "SELECT * FROM `".DB_PREFIX."ideal` WHERE `order_id`='".$this->db->escape($order_id)."' AND `ideal_txid`='".$this->db->escape($txid)."'";
    	$result = $this->db->query ($sql);
        return $result->rows[0];
    }

    /**
     *		Update txid/order_id pair in database
     */

    public function updateTxid ($order_id, $paid, $tpResponse=false) 
    {
    	if ($paid) 
        {
			$sql = "UPDATE `".DB_PREFIX."ideal` SET `paid`=now() WHERE `order_id`='".$this->db->escape($order_id)."'";
        } else {
			$sql = "UPDATE `".DB_PREFIX."ideal` SET `ideal_response`='".$this->db->escape($tpResponse)."' WHERE `order_id`='".$this->db->escape($order_id)."'";
        }
    	$result = $this->db->query ($sql);
    }

    /**
     *		Start payment
     */

	public function send() 
    {
		$payment_type = 'Sale';

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $rtlo = ($this->config->get('ideal_rtlo')) ? $this->config->get('ideal_rtlo') : 93929; // Default TargetPay

        if ($order_info['currency_code']!="EUR") 
        {
			$this->log->write("Invalid currency code ".$order_info['currency_code']);
            $json['error'] = "Invalid currency code ".$order_info['currency_code'];
        } else 
        {
			$targetPay = new TargetPayCore ("IDE", $rtlo, "bc4ea48b2540494ec38cbfa99aef6617", "nl", false);
			$targetPay->setAmount ( round($order_info['total'] * 100));
			$targetPay->setDescription ( "Order #". $this->session->data['order_id'] );
			if (!empty($this->request->post['bank_id'])) {
            	 $targetPay->setBankId ( $this->request->post['bank_id'] );
            }

			$targetPay->setCancelUrl ( $this->url->link('checkout/cart', '', 'SSL') );
			$targetPay->setReturnUrl ( $this->url->link('checkout/success', '', 'SSL') );
			$targetPay->setReportUrl ( $this->url->link('payment/ideal/callback', 'order_id='.$this->session->data['order_id'], 'SSL') );

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

	public function callback() 
    {
    	$order_id = 0;
    	if (!empty($_GET["order_id"])) {
	     	$order_id = (int) $_GET["order_id"];
        }

    	if (!empty($_GET["amp;order_id"])) {
	     	$order_id = (int) $_GET["amp;order_id"]; // Buggy redirects
        }

        if ($order_id==0) {
			$this->log->write('TargetPay callback(), no order_id passed');
            echo "NoOrderId ";
            die();
        }

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);

        $targetPayTx = $this->getTxid ($order_id, $_POST["trxid"]);

        if (!$targetPayTx) {
			$this->log->write('Could not find TargetPay transaction data for order_id='.$order_id);
            echo "TxNotFound ";
            die();
        }

        $rtlo = ($this->config->get('ideal_rtlo')) ? $this->config->get('ideal_rtlo') : 93929; // Default TargetPay

		$targetPay = new TargetPayCore ("IDE", $rtlo, "bc4ea48b2540494ec38cbfa99aef6617", "nl", false);
		$targetPay->checkPayment ($targetPayTx["ideal_txid"]);

        if ($targetPay->getPaidStatus() || $this->config->get('ideal_test')) {
            $this->updateTxid ($order_id, true);
            $order_status_id = $this->config->get('ideal_pending_status_id');
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