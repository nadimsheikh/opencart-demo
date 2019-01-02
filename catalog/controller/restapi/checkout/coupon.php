<?php

class ControllerRestApiCheckoutCoupon extends Controller {

    public function __construct($registry) {
        parent::__construct($registry);
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
        if (isset($this->request->post['language'])) {
            $this->session->data['language'] = $this->request->post['language'];
        }
        if (isset($this->request->post['currency'])) {
            $this->session->data['currency'] = $this->request->post['currency'];
        }
    }

    public function index() {
        $this->load->language('api/coupon');
       
        // Delete past coupon in case there is an error
        unset($this->session->data['coupon']);

        $json = array();

        $this->load->model('extension/total/coupon');

        if (isset($this->request->post['coupon'])) {
            $coupon = $this->request->post['coupon'];
        } else {
            $coupon = '';
        }

        $coupon_info = $this->model_extension_total_coupon->getCoupon($coupon);

        if ($coupon_info) {
            $this->session->data['coupon'] = $this->request->post['coupon'];

            $json['success'] = $this->language->get('text_success');
            $json['status'] = TRUE;
        } else {
            $json['error'] = $this->language->get('error_coupon');
            $json['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
