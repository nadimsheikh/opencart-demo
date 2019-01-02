<?php

class ControllerRestApiCheckoutCustomer extends Controller {

    public function index() {
        $this->load->language('api/payment');
        
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
       
        unset($this->session->data['customer']);
       

        $json = array();


        if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
            $json['error']['firstname'] = $this->language->get('error_firstname');
        }

        if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
            $json['error']['lastname'] = $this->language->get('error_lastname');
        }

        if ((utf8_strlen(trim($this->request->post['email'])) < 3) || (utf8_strlen(trim($this->request->post['email'])) > 128)) {
            $json['error']['email'] = $this->language->get('error_email');
        }

        if ((utf8_strlen($this->request->post['telephone']) < 2) || (utf8_strlen($this->request->post['telephone']) > 32)) {
            $json['error']['telephone'] = $this->language->get('error_telephone');
        }


        if (!$json) {
          
            $this->session->data['customer'] = array(
                'firstname' => $this->request->post['firstname'],
                'lastname' => $this->request->post['lastname'],
                'email' => $this->request->post['email'],
                'telephone' => $this->request->post['telephone'],                          
            );
            $json['status'] = TRUE;
            $json['success'] = $this->language->get('text_address');            
        } else {
            $json['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
   
}
