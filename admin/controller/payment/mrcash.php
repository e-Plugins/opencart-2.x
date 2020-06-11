<?php

/**
 *
 *	iDEALplugins.nl
 *  TargetPay plugin for Opencart 2.0+
 *
 *  (C) Copyright Yellow Melon 2014
 *
 *	@file 		TargetPay Admin Controller
 *	@author		Yellow Melon B.V. / www.mrcashplugins.nl
 *
 */

class ControllerPaymentmrcash extends Controller 
{
	private $error = array(); 

	public function index() 
	{

		$this->load->language('payment/mrcash');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('mrcash', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}


		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');

		$data['entry_rtlo'] = $this->language->get('entry_rtlo');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_transaction'] = $this->language->get('entry_transaction');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['entry_canceled_status'] = $this->language->get('entry_canceled_status');
		$data['entry_pending_status'] = $this->language->get('entry_pending_status');

		$data['help_test'] = $this->language->get('help_test');
		$data['help_debug'] = $this->language->get('help_debug');
		$data['help_total'] = $this->language->get('help_total');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['tab_general'] = $this->language->get('tab_general');
		$data['tab_status'] = $this->language->get('tab_status');

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['rtlo'])) {
			$data['error_rtlo'] = $this->error['rtlo'];
		} else {
			$data['error_rtlo'] = '';
		}

		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/mrcash', 'token=' . $this->session->data['token'], 'SSL'),
		);

		$data['action'] = $this->url->link('payment/mrcash', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->request->post['mrcash_rtlo'])) {
			$data['mrcash_rtlo'] = $this->request->post['mrcash_rtlo'];
		} else {
			$data['mrcash_rtlo'] = $this->config->get('mrcash_rtlo');
		}

        if (!isset($data['mrcash_rtlo'])) {
            $data['mrcash_rtlo'] = 93929; // Default TargetPay
        }

		if (isset($this->request->post['mrcash_test'])) {
			$data['mrcash_test'] = $this->request->post['mrcash_test'];
		} else {
			$data['mrcash_test'] = $this->config->get('mrcash_test');
		}

		if (isset($this->request->post['mrcash_total'])) {
			$data['mrcash_total'] = $this->request->post['mrcash_total'];
		} else {
			$data['mrcash_total'] = $this->config->get('mrcash_total');
	   	}
	   		
   		if (!isset($data['mrcash_total'])) {
   			$data['mrcash_total'] = 2;
   		}	   		

		if (isset($this->request->post['mrcash_canceled_id'])) {
			$data['mrcash_canceled_status_id'] = $this->request->post['mrcash_canceled_status_id'];
		} else {
			$data['mrcash_canceled_status_id'] = $this->config->get('mrcash_canceled_status_id');
		}

		if (isset($this->request->post['mrcash_pending_status_id'])) {
			$data['mrcash_pending_status_id'] = $this->request->post['mrcash_pending_status_id'];
		} else {
			$data['mrcash_pending_status_id'] = $this->config->get('mrcash_pending_status_id');
		}

		// Bug fix for 2.0.0.0 ... everything defaults to canceled, not user friendly

		if (is_null($data['mrcash_pending_status_id'])) $data['mrcash_pending_status_id'] = 1;
		if (is_null($data['mrcash_canceled_status_id'])) $data['mrcash_canceled_status_id'] = 7;

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['mrcash_geo_zone_id'])) {
			$data['mrcash_geo_zone_id'] = $this->request->post['mrcash_geo_zone_id'];
		} else {
			$data['mrcash_geo_zone_id'] = $this->config->get('mrcash_geo_zone_id');
		}
		
		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['mrcash_status'])) {
			$data['mrcash_status'] = $this->request->post['mrcash_status'];
		} else {
			$data['mrcash_status'] = $this->config->get('mrcash_status');
		}
		
		if (isset($this->request->post['mrcash_sort_order'])) {
			$data['mrcash_sort_order'] = $this->request->post['mrcash_sort_order'];
		} else {
			$data['mrcash_sort_order'] = $this->config->get('mrcash_sort_order');
		}
			
		if (!isset($data['mrcash_sort_order'])) {
			$data['mrcash_sort_order'] = 1;
		}			

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('payment/mrcash.tpl', $data));
	}

	private function validate() 
	{
		if (!$this->user->hasPermission('modify', 'payment/mrcash')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['mrcash_rtlo']) {
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
       $this->load->model('payment/mrcash');
       $this->model_payment_mrcash->createTable();

       $this->load->model('setting/setting');
       $this->model_setting_setting->editSetting('mrcash', array('mrcash_status'=>1));
	   }
	}
?>
