<?php

class ControllerRestApiCheckoutOrder extends Controller {

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

    public function add() {
        $this->load->language('api/order');

        $json = array();

        // Customer
        if ($this->request->post['customer_id']) {
            $this->load->model('account/customer');

            $customer_info = $this->model_account_customer->getCustomer($this->request->post['customer_id']);

            if (!$customer_info || !$this->customer->login($customer_info['email'], '', true)) {
                $json['error']['warning'] = $this->language->get('error_customer');
            } else {
                $this->session->data['customer'] = array(
                    'customer_id' => $customer_info['customer_id'],
                    'customer_group_id' => $customer_info['customer_group_id'],
                    'firstname' => $customer_info['firstname'],
                    'lastname' => $customer_info['lastname'],
                    'email' => $customer_info['email'],
                    'telephone' => $customer_info['telephone'],
                    'custom_field' => isset($customer_info['custom_field']) ? $customer_info['custom_field'] : array()
                );
            }
        }

        // Customer
        if (!isset($this->session->data['customer'])) {
            $json['error'] = $this->language->get('error_customer');
        }


        $this->payment_methods();


        // Payment Address
        if (!isset($this->session->data['payment_address'])) {
            $json['error'] = $this->language->get('error_payment_address');
        }


        // Payment Method
        if (!$json && !empty($this->request->post['payment_method'])) {

            if (empty($this->session->data['payment_methods'])) {
                $json['error'] = $this->language->get('error_no_payment');
            } elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
                $json['error'] = $this->language->get('error_payment_method');
            }


            if (!$json) {
                $this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
            }
        }


        if (!isset($this->session->data['payment_method'])) {
            $json['error'] = $this->language->get('error_payment_method');
        }


        // Shipping
        if ($this->cart->hasShipping()) {

            $this->shipping_methods();

            // Shipping Address
            if (!isset($this->session->data['shipping_address'])) {
                $json['error'] = $this->language->get('error_shipping_address');
            }

            // Shipping Method
            if (!$json && !empty($this->request->post['shipping_method'])) {
                if (empty($this->session->data['shipping_methods'])) {
                    $json['error'] = $this->language->get('error_no_shipping');
                } else {
                    $shipping = explode('.', $this->request->post['shipping_method']);

                    if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]])) {
                        $json['error'] = $this->language->get('error_shipping_method');
                    }
                }

                if (!$json) {
                    $this->session->data['shipping_method'] = $this->session->data['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
                }
            }

            // Shipping Method
            if (!isset($this->session->data['shipping_method'])) {
                $json['error'] = $this->language->get('error_shipping_method');
            }
        } else {
            unset($this->session->data['shipping_address']);
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
        }

        // Cart
        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            $json['error'] = $this->language->get('error_stock');
        }

        // Validate minimum quantity requirements.
        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $json['error'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);

                break;
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_success');

            $order_data = array();

            // Store Details
            $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
            $order_data['store_id'] = $this->config->get('config_store_id');
            $order_data['store_name'] = $this->config->get('config_name');
            $order_data['store_url'] = $this->config->get('config_url');

            // Customer Details
            $order_data['customer_id'] = $this->session->data['customer']['customer_id'];
            $order_data['customer_group_id'] = $this->session->data['customer']['customer_group_id'];
            $order_data['firstname'] = $this->session->data['customer']['firstname'];
            $order_data['lastname'] = $this->session->data['customer']['lastname'];
            $order_data['email'] = $this->session->data['customer']['email'];
            $order_data['telephone'] = $this->session->data['customer']['telephone'];
            $order_data['custom_field'] = $this->session->data['customer']['custom_field'];

            // Payment Details
            $order_data['payment_firstname'] = $this->session->data['payment_address']['firstname'];
            $order_data['payment_lastname'] = $this->session->data['payment_address']['lastname'];
            $order_data['payment_company'] = $this->session->data['payment_address']['company'];
            $order_data['payment_address_1'] = $this->session->data['payment_address']['address_1'];
            $order_data['payment_address_2'] = $this->session->data['payment_address']['address_2'];
            $order_data['payment_city'] = $this->session->data['payment_address']['city'];
            $order_data['payment_postcode'] = $this->session->data['payment_address']['postcode'];
            $order_data['payment_zone'] = $this->session->data['payment_address']['zone'];
            $order_data['payment_zone_id'] = $this->session->data['payment_address']['zone_id'];
            $order_data['payment_country'] = $this->session->data['payment_address']['country'];
            $order_data['payment_country_id'] = $this->session->data['payment_address']['country_id'];
            $order_data['payment_address_format'] = $this->session->data['payment_address']['address_format'];
            $order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());

            if (isset($this->session->data['payment_method']['title'])) {
                $order_data['payment_method'] = $this->session->data['payment_method']['title'];
            } else {
                $order_data['payment_method'] = '';
            }

            if (isset($this->session->data['payment_method']['code'])) {
                $order_data['payment_code'] = $this->session->data['payment_method']['code'];
            } else {
                $order_data['payment_code'] = '';
            }

            // Shipping Details
            if ($this->cart->hasShipping()) {
                $order_data['shipping_firstname'] = $this->session->data['shipping_address']['firstname'];
                $order_data['shipping_lastname'] = $this->session->data['shipping_address']['lastname'];
                $order_data['shipping_company'] = $this->session->data['shipping_address']['company'];
                $order_data['shipping_address_1'] = $this->session->data['shipping_address']['address_1'];
                $order_data['shipping_address_2'] = $this->session->data['shipping_address']['address_2'];
                $order_data['shipping_city'] = $this->session->data['shipping_address']['city'];
                $order_data['shipping_postcode'] = $this->session->data['shipping_address']['postcode'];
                $order_data['shipping_zone'] = $this->session->data['shipping_address']['zone'];
                $order_data['shipping_zone_id'] = $this->session->data['shipping_address']['zone_id'];
                $order_data['shipping_country'] = $this->session->data['shipping_address']['country'];
                $order_data['shipping_country_id'] = $this->session->data['shipping_address']['country_id'];
                $order_data['shipping_address_format'] = $this->session->data['shipping_address']['address_format'];
                $order_data['shipping_custom_field'] = (isset($this->session->data['shipping_address']['custom_field']) ? $this->session->data['shipping_address']['custom_field'] : array());

                if (isset($this->session->data['shipping_method']['title'])) {
                    $order_data['shipping_method'] = $this->session->data['shipping_method']['title'];
                } else {
                    $order_data['shipping_method'] = '';
                }

                if (isset($this->session->data['shipping_method']['code'])) {
                    $order_data['shipping_code'] = $this->session->data['shipping_method']['code'];
                } else {
                    $order_data['shipping_code'] = '';
                }
            } else {
                $order_data['shipping_firstname'] = '';
                $order_data['shipping_lastname'] = '';
                $order_data['shipping_company'] = '';
                $order_data['shipping_address_1'] = '';
                $order_data['shipping_address_2'] = '';
                $order_data['shipping_city'] = '';
                $order_data['shipping_postcode'] = '';
                $order_data['shipping_zone'] = '';
                $order_data['shipping_zone_id'] = '';
                $order_data['shipping_country'] = '';
                $order_data['shipping_country_id'] = '';
                $order_data['shipping_address_format'] = '';
                $order_data['shipping_custom_field'] = array();
                $order_data['shipping_method'] = '';
                $order_data['shipping_code'] = '';
            }

            // Products
            $order_data['products'] = array();

            foreach ($this->cart->getProducts() as $product) {
                $option_data = array();

                foreach ($product['option'] as $option) {
                    $option_data[] = array(
                        'product_option_id' => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'option_id' => $option['option_id'],
                        'option_value_id' => $option['option_value_id'],
                        'name' => $option['name'],
                        'value' => $option['value'],
                        'type' => $option['type']
                    );
                }

                $order_data['products'][] = array(
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $option_data,
                    'download' => $product['download'],
                    'quantity' => $product['quantity'],
                    'subtract' => $product['subtract'],
                    'price' => $product['price'],
                    'total' => $product['total'],
                    'tax' => $this->tax->getTax($product['price'], $product['tax_class_id']),
                    'reward' => $product['reward']
                );
            }

            // Gift Voucher
            $order_data['vouchers'] = array();

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $order_data['vouchers'][] = array(
                        'description' => $voucher['description'],
                        'code' => token(10),
                        'to_name' => $voucher['to_name'],
                        'to_email' => $voucher['to_email'],
                        'from_name' => $voucher['from_name'],
                        'from_email' => $voucher['from_email'],
                        'voucher_theme_id' => $voucher['voucher_theme_id'],
                        'message' => $voucher['message'],
                        'amount' => $voucher['amount']
                    );
                }
            }

            // Order Totals
            $this->load->model('setting/extension');

            $totals = array();
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array.
            $total_data = array(
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total
            );

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

            $sort_order = array();

            foreach ($total_data['totals'] as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $total_data['totals']);

            $order_data = array_merge($order_data, $total_data);

            if (isset($this->request->post['comment'])) {
                $order_data['comment'] = $this->request->post['comment'];
            } else {
                $order_data['comment'] = '';
            }

            if (isset($this->request->post['affiliate_id'])) {
                $subtotal = $this->cart->getSubTotal();

                // Affiliate
                $this->load->model('account/customer');

                $affiliate_info = $this->model_account_customer->getAffiliate($this->request->post['affiliate_id']);

                if ($affiliate_info) {
                    $order_data['affiliate_id'] = $affiliate_info['customer_id'];
                    $order_data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
                } else {
                    $order_data['affiliate_id'] = 0;
                    $order_data['commission'] = 0;
                }

                // Marketing
                $order_data['marketing_id'] = 0;
                $order_data['tracking'] = '';
            } else {
                $order_data['affiliate_id'] = 0;
                $order_data['commission'] = 0;
                $order_data['marketing_id'] = 0;
                $order_data['tracking'] = '';
            }

            $order_data['language_id'] = $this->config->get('config_language_id');
            $order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
            $order_data['currency_code'] = $this->session->data['currency'];
            $order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
            $order_data['ip'] = $this->request->server['REMOTE_ADDR'];

            if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
            } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                $order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
            } else {
                $order_data['forwarded_ip'] = '';
            }

            if (isset($this->request->server['HTTP_USER_AGENT'])) {
                $order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
            } else {
                $order_data['user_agent'] = '';
            }

            if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                $order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
            } else {
                $order_data['accept_language'] = '';
            }

            $this->load->model('checkout/order');

            $json['order_id'] = $this->model_checkout_order->addOrder($order_data);
            $json['total'] = $this->model_checkout_order->getOrderTotal($json['order_id']);

            // Set the order history
            if (isset($this->request->post['order_status_id'])) {
                $order_status_id = $this->request->post['order_status_id'];
            } else {
                $order_status_id = $this->config->get('config_order_status_id');
            }

            $this->model_checkout_order->addOrderHistory($json['order_id'], $order_status_id);

            // clear cart since the order has already been successfully stored.
            $this->cart->clear();

            $json['status'] = TRUE;
        } else {
            $json['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function payment_methods() {
        $this->load->language('api/payment');

        // Delete past shipping methods and method just in case there is an error
        unset($this->session->data['payment_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_address']);

        $json = array();

        if (isset($this->request->post['payment_address_id'])) {
            $address_id = $this->request->post['payment_address_id'];
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

    public function payment_method() {
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

    public function shipping_methods() {
        $this->load->language('api/shipping');

        // Delete past shipping methods and method just in case there is an error
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_address']);

        $json = array();

        if ($this->cart->hasShipping()) {

            if (isset($this->request->post['shipping_address_id'])) {
                $address_id = $this->request->post['shipping_address_id'];
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

    public function shipping_method() {
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
