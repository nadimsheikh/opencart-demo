<?php

class ControllerRestApiCheckoutPayment extends Controller {

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

    public function methods() {
        $this->load->language('api/payment');

        // Delete past shipping methods and method just in case there is an error
        unset($this->session->data['payment_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_address']);

        $json = array();

        if (isset($this->request->post['address_id'])) {
            $address_id = $this->request->post['address_id'];
            $this->load->model('account/address');
            $address_info = $this->model_account_address->getAddress($address_id);

            $this->session->data['payment_address'] = array(
                'firstname' => $address_info['firstname'],
                'lastname' => $address_info['lastname'],
                'company' => $address_info['company'],
                'address_1' => $address_info['address_1'],
                'address_2' => $address_info['address_2'],
                'postcode' => $address_info['postcode'],
                'city' => $address_info['city'],
                'zone_id' => $address_info['zone_id'],
                'zone' => $address_info['zone'],
                'zone_code' => $address_info['zone_code'],
                'country_id' => $address_info['country_id'],
                'country' => $address_info['country'],
                'iso_code_2' => $address_info['iso_code_2'],
                'iso_code_3' => $address_info['iso_code_3'],
                'address_format' => $address_info['address_format'],
                'custom_field' => isset($address_info['custom_field']) ? $address_info['custom_field'] : array()
            );
        } else {
            $json['error'] = $this->language->get('error_address');
        }

        // Payment Address
        if (!isset($this->session->data['payment_address'])) {
            $json['error'] = $this->language->get('error_address');
        }

        if (!$json) {
            // Totals
            $totals = array();
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array. 
            $total_data = array(
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total
            );

            $this->load->model('setting/extension');

            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }

            // Payment Methods
            $json['payment_methods'] = array();

            $this->load->model('setting/extension');

            $results = $this->model_setting_extension->getExtensions('payment');

            $recurring = $this->cart->hasRecurringProducts();

            foreach ($results as $result) {
                if ($this->config->get('payment_' . $result['code'] . '_status')) {
                    $this->load->model('extension/payment/' . $result['code']);

                    $method = $this->{'model_extension_payment_' . $result['code']}->getMethod($this->session->data['payment_address'], $total);

                    if ($method) {
                        if ($recurring) {
                            if (property_exists($this->{'model_extension_payment_' . $result['code']}, 'recurringPayments') && $this->{'model_extension_payment_' . $result['code']}->recurringPayments()) {
                                $json['payment_methods'][] = $method;
                                $payment_methods[$result['code']] = $method;
                            }
                        } else {
                            $json['payment_methods'][] = $method;
                            $payment_methods[$result['code']] = $method;
                        }
                    }
                }
            }

            $sort_order = array();

            foreach ($json['payment_methods'] as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $json['payment_methods']);

            $json['status'] = TRUE;

            if ($json['payment_methods']) {
                $json['status'] = TRUE;
                $this->session->data['payment_methods'] = $payment_methods;
            } else {
                $json['status'] = FALSE;
                $json['error'] = $this->language->get('error_no_payment');
            }
        } else {
            $json['status'] = TRUE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function method() {
        $this->load->language('api/payment');

        // Delete old payment method so not to cause any issues if there is an error
        unset($this->session->data['payment_method']);

        $json = array();


        // Payment Address
        if (!isset($this->session->data['payment_address'])) {
            $json['error'] = $this->language->get('error_address');
        }

        // Payment Method
        if (empty($this->session->data['payment_methods'])) {
            $json['error'] = $this->language->get('error_no_payment');
        } elseif (!isset($this->request->post['payment_method'])) {
            $json['error'] = $this->language->get('error_method');
        } elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
            $json['error'] = $this->language->get('error_method');
        }

        if (!$json) {
            $this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
            $json['status'] = TRUE;
            $json['success'] = $this->language->get('text_method');
        } else {
            $json['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
