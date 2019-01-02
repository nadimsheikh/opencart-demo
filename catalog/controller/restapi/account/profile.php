<?php

class ControllerRestApiAccountProfile extends Controller {

    private $error = array();

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

        $this->load->language('account/account');

        $this->load->model('account/customer');


        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        if ($customer_info) {
            if (!empty($customer_info)) {
                $data['firstname'] = $customer_info['firstname'];
            } else {
                $data['firstname'] = '';
            }

            if (!empty($customer_info)) {
                $data['lastname'] = $customer_info['lastname'];
            } else {
                $data['lastname'] = '';
            }

            if (!empty($customer_info)) {
                $data['email'] = $customer_info['email'];
            } else {
                $data['email'] = '';
            }

            if (!empty($customer_info)) {
                $data['telephone'] = $customer_info['telephone'];
            } else {
                $data['telephone'] = '';
            }

            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_success');
        } else {
            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_error');
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
