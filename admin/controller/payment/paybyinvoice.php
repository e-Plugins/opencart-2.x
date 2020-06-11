<?php

/**
 *  TargetPay plugin v1.1 for Opencart 1.5+
 *  (C) Copyright Yellow Melon 2013
 *
 *  @file 		TargetPay Admin Controller
 *  @author		Yellow Melon B.V. 
 */ 

class ControllerPaymentpaybyinvoice extends Controller 
{
	private $error = array(); 

	public function index() 
	{
		$this->language->load('payment/paybyinvoice');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('paybyinvoice', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		$this->data['entry_rtlo'] = $this->language->get('entry_rtlo');
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_transaction'] = $this->language->get('entry_transaction');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['rtlo'])) {
			$this->data['error_rtlo'] = $this->error['rtlo'];
		} else {
			$this->data['error_rtlo'] = '';
		}

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/paybyinvoice', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->data['action'] = $this->url->link('payment/paybyinvoice', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['paybyinvoice_rtlo'])) {
			$this->data['paybyinvoice_rtlo'] = $this->request->post['paybyinvoice_rtlo'];
			} else {
			$this->data['paybyinvoice_rtlo'] = $this->config->get('paybyinvoice_rtlo');
		}

        if (!isset($this->data['paybyinvoice_rtlo'])) {
            $this->data['paybyinvoice_rtlo'] = 93929; // Default TargetPay
        }

		if (isset($this->request->post['paybyinvoice_test'])) {
			$this->data['paybyinvoice_test'] = $this->request->post['paybyinvoice_test'];
		} else {
			$this->data['paybyinvoice_test'] = $this->config->get('paybyinvoice_test');
		}


		if (isset($this->request->post['paybyinvoice_total'])) {
			$this->data['paybyinvoice_total'] = $this->request->post['paybyinvoice_total'];
		} else {
			$this->data['paybyinvoice_total'] = $this->config->get('paybyinvoice_total');
	   	}
	   		
   		if (!isset($this->data['paybyinvoice_total'])) {
   			$this->data['paybyinvoice_total'] = 1;
   		}	   		

		if (isset($this->request->post['paybyinvoice_order_status_id'])) {
			$this->data['paybyinvoice_order_status_id'] = $this->request->post['paybyinvoice_order_status_id'];
		} else {
			$this->data['paybyinvoice_order_status_id'] = $this->config->get('paybyinvoice_order_status_id');
		}
			
		if (!isset($this->data['paybyinvoice_order_status_id'])) {
			$this->data['paybyinvoice_order_status_id'] = 1; 
		}				

		$this->load->model('localisation/order_status');
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['paybyinvoice_geo_zone_id'])) {
			$this->data['paybyinvoice_geo_zone_id'] = $this->request->post['paybyinvoice_geo_zone_id'];
		} else {
			$this->data['paybyinvoice_geo_zone_id'] = $this->config->get('paybyinvoice_geo_zone_id');
		}
		
		$this->load->model('localisation/geo_zone');
										
		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['paybyinvoice_status'])) {
			$this->data['paybyinvoice_status'] = $this->request->post['paybyinvoice_status'];
		} else {
			$this->data['paybyinvoice_status'] = $this->config->get('paybyinvoice_status');
		}
		
		if (isset($this->request->post['paybyinvoice_sort_order'])) {
			$this->data['paybyinvoice_sort_order'] = $this->request->post['paybyinvoice_sort_order'];
		} else {
			$this->data['paybyinvoice_sort_order'] = $this->config->get('paybyinvoice_sort_order');
		}
			
		if (!isset($this->data['paybyinvoice_sort_order'])) {
			$this->data['paybyinvoice_sort_order'] = 4;
		}			

		$this->template = 'payment/paybyinvoice.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
			);
				
		$this->response->setOutput($this->render());
		}

	protected function validate() 
	{
		if (!$this->user->hasPermission('modify', 'payment/paybyinvoice')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['paybyinvoice_rtlo']) {
			$this->error['rtlo'] = $this->language->get('error_rtlo');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}


	public function install() 
	{
       $this->load->model('payment/paybyinvoice');
       $this->model_payment_paybyinvoice->createTable();

       $this->load->model('setting/setting');
       $this->model_setting_setting->editSetting('paybyinvoice', array('paybyinvoice_status'=>1));
	   }
	}
