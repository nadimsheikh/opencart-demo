<?php

class ControllerRestApiCheckoutShipping extends Controller {

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
        $this->load->language('api/shipping');

        // Delete past shipping methods and method just in case there is an error
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_address']);

        $json = array();

        if ($this->cart->hasShipping()) {

            if (isset($this->request->post['address_id'])) {
                $address_id = $this->request->post['address_id'];
                $this->load->model('account/address');
                $address_info = $this->model_account_address->getAddress($address_id);


                $this->session->data['shipping_address'] = array(
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

            if (!isset($this->session->data['shipping_address'])) {
                $json['error'] = $this->language->get('error_address');
            }

            if (!$json) {
                // Shipping Methods
                $json['shipping_methods'] = array();

                $this->load->model('setting/extension');

                $results = $this->model_setting_extension->getExtensions('shipping');

                foreach ($results as $result) {
                    if ($this->config->get('shipping_' . $result['code'] . '_status')) {
                        $this->load->model('extension/shipping/' . $result['code']);

                        $quote = $this->{'model_extension_shipping_' . $result['code']}->getQuote($this->session->data['shipping_address']);

                        if ($quote) {
                            $quoteData = array();
                            foreach ($quote['quote'] as $quoteValue) {
                                $quoteData[] = $quoteValue;
                            }

                            $json['shipping_methods'][] = array(
                                'title' => $quote['title'],
                                'quote' => $quoteData,
                                'sort_order' => $quote['sort_order'],
                                'error' => $quote['error']
                            );

                            $shipping_methods[$result['code']] = array(
                                'title' => $quote['title'],
                                'quote' => $quote['quote'],
                                'sort_order' => $quote['sort_order'],
                                'error' => $quote['error']
                            );
                        }
                    }
                }

                $sort_order = array();

                foreach ($json['shipping_methods'] as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $json['shipping_methods']);

                $json['status'] = TRUE;
                if ($json['shipping_methods']) {
                    $json['status'] = TRUE;
                    $json['shipping_address'] = $this->session->data['shipping_address'];
                    $this->session->data['shipping_methods'] = $shipping_methods;
                } else {
                    $json['status'] = FALSE;
                    $json['error'] = $this->language->get('error_no_shipping');
                }
            }
        } else {
            $json['status'] = FALSE;
            $json['shipping_methods'] = array();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function method() {
        $this->load->language('api/shipping');

        // Delete old shipping method so not to cause any issues if there is an error
        unset($this->session->data['shipping_method']);

        $json = array();


        if ($this->cart->hasShipping()) {
            // Shipping Address
            if (!isset($this->session->data['shipping_address'])) {
                $json['error'] = $this->language->get('error_address');
            }

            // Shipping Method
            if (empty($this->session->data['shipping_methods'])) {
                $json['error'] = $this->language->get('error_no_shipping');
            } elseif (!isset($this->request->post['shipping_method'])) {
                $json['error'] = $this->language->get('error_method');
            } else {
                $shipping = explode('.', $this->request->post['shipping_method']);

                if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                    $json['error'] = $this->language->get('error_method');
                }
            }

            if (!$json) {
                $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                $json['status'] = TRUE;
                $json['success'] = $this->language->get('text_method');
            } else {
                $json['status'] = FALSE;
            }
        } else {
            $json['status'] = TRUE;
            unset($this->session->data['shipping_address']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
