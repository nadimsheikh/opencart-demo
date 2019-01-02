<?php

class ControllerRestApiAccountPassword extends Controller {

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

        $this->load->language('account/password');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('account/customer');

            $this->model_account_customer->editPassword($this->customer->getEmail(), $this->request->post['password']);

            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_success');
        } else {
            $data['status'] = FALSE;
            if (isset($this->error['password'])) {
                $data['error_password'] = $this->error['password'];
            } else {
                $data['error_password'] = '';
            }

            if (isset($this->error['confirm'])) {
                $data['error_confirm'] = $this->error['confirm'];
            } else {
                $data['error_confirm'] = '';
            }
        }



        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    protected function validate() {
        if ((utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) < 4) || (utf8_strlen(html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')) > 40)) {
            $this->error['password'] = $this->language->get('error_password');
        }

        if ($this->request->post['confirm'] != $this->request->post['password']) {
            $this->error['confirm'] = $this->language->get('error_confirm');
        }

        return !$this->error;
    }

    public function loginError() {
        $data['error_warning'] = 'Please login';
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
