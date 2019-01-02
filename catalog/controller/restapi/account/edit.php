<?php

class ControllerRestApiAccountEdit extends Controller {

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
       
        $this->load->language('account/edit');

        $this->load->model('account/customer');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_account_customer->editCustomer($this->customer->getId(), $this->request->post);

            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_success');
        } else {
            $data['status'] = FALSE;
            if (isset($this->error['warning'])) {
                $data['error_warning'] = $this->error['warning'];
            } else {
                $data['error_warning'] = '';
            }

            if (isset($this->error['firstname'])) {
                $data['error_firstname'] = $this->error['firstname'];
            } else {
                $data['error_firstname'] = '';
            }

            if (isset($this->error['lastname'])) {
                $data['error_lastname'] = $this->error['lastname'];
            } else {
                $data['error_lastname'] = '';
            }

            if (isset($this->error['email'])) {
                $data['error_email'] = $this->error['email'];
            } else {
                $data['error_email'] = '';
            }

            if (isset($this->error['telephone'])) {
                $data['error_telephone'] = $this->error['telephone'];
            } else {
                $data['error_telephone'] = '';
            }

            if (isset($this->error['custom_field'])) {
                $data['error_custom_field'] = $this->error['custom_field'];
            } else {
                $data['error_custom_field'] = array();
            }
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    protected function validate() {
        if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email');
        }

        if (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
            $this->error['warning'] = $this->language->get('error_exists');
        }

        if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields('account', $this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'account') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        }

        return !$this->error;
    }

}
