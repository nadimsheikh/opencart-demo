<?php

class ControllerRestApiCheckoutCart extends Controller {

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
        $this->load->language('checkout/cart');

        $this->payment_methods();
        $this->payment_method();
        $this->shipping_methods();
        $this->shipping_method();

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
            if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
                $data['error_warning'] = $this->language->get('error_stock');
            } elseif (isset($this->session->data['error'])) {
                $data['error_warning'] = $this->session->data['error'];

                unset($this->session->data['error']);
            } else {
                $data['error_warning'] = '';
            }

            if (isset($this->session->data['success'])) {
                $data['success'] = $this->session->data['success'];

                unset($this->session->data['success']);
            } else {
                $data['success'] = '';
            }

            if ($this->config->get('config_cart_weight')) {
                $data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
            } else {
                $data['weight'] = '';
            }

            $this->load->model('tool/image');
            $this->load->model('tool/upload');

            $data['total_quantity'] = $this->cart->countProducts();

            $data['products'] = array();

            $products = $this->cart->getProducts();

            foreach ($products as $product) {
                $product_total = 0;

                foreach ($products as $product_2) {
                    if ($product_2['product_id'] == $product['product_id']) {
                        $product_total += $product_2['quantity'];
                    }
                }

                if ($product['minimum'] > $product_total) {
                    $data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
                }

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
                } else {
                    $image = '';
                }

                $option_data = array();

                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }

                    $option_data[] = array(
                        'name' => $option['name'],
                        'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                    );
                }

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                    $price = $this->currency->format($unit_price, $this->session->data['currency']);
                    $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
                } else {
                    $price = false;
                    $total = false;
                }

                $recurring = '';

                if ($product['recurring']) {
                    $frequencies = array(
                        'day' => $this->language->get('text_day'),
                        'week' => $this->language->get('text_week'),
                        'semi_month' => $this->language->get('text_semi_month'),
                        'month' => $this->language->get('text_month'),
                        'year' => $this->language->get('text_year'),
                    );

                    if ($product['recurring']['trial']) {
                        $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                    }

                    if ($product['recurring']['duration']) {
                        $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                    } else {
                        $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                    }
                }

                $data['products'][] = array(
                    'cart_id' => $product['cart_id'],
                    'thumb' => $image,
                    'name' => $product['name'],
                    'model' => $product['model'],
                    'option' => $option_data,
                    'recurring' => $recurring,
                    'quantity' => $product['quantity'],
                    'stock' => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
                    'reward' => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
                    'price' => $price,
                    'total' => $total,
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                );
            }

            // Gift Voucher
            $data['vouchers'] = array();

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $key => $voucher) {
                    $data['vouchers'][] = array(
                        'key' => $key,
                        'description' => $voucher['description'],
                        'amount' => $this->currency->format($voucher['amount'], $this->session->data['currency']),
                        'remove' => $this->url->link('checkout/cart', 'remove=' . $key),
                    );
                }
            }

            // Totals
            $this->load->model('setting/extension');

            $totals = array();
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array.
            $total_data = array(
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total,
            );


            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
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

                foreach ($totals as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $totals);
            }

            $data['totals'] = array();

            foreach ($totals as $total) {
                $data['totals'][] = array(
                    'title' => $total['title'],
                    'text' => $this->currency->format($total['value'], $this->session->data['currency']),
                );
            }

            $data['status'] = true;
        } else {
            $data['text_error'] = $this->language->get('text_empty');
            $data['status'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function add() {
        $this->load->language('checkout/cart');

        $json = array();


        if (isset($this->request->post['product_id'])) {
            $product_id = (int) $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            if (isset($this->request->post['quantity'])) {
                $quantity = (int) $this->request->post['quantity'];
            } else {
                $quantity = 1;
            }

            if (isset($this->request->post['option'])) {
                $option = array_filter($this->request->post['option']);
            } else {
                $option = array();
            }

            $product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

            foreach ($product_options as $product_option) {
                if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                    $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                }
            }

            if (isset($this->request->post['recurring_id'])) {
                $recurring_id = $this->request->post['recurring_id'];
            } else {
                $recurring_id = 0;
            }

            $recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

            if ($recurrings) {
                $recurring_ids = array();

                foreach ($recurrings as $recurring) {
                    $recurring_ids[] = $recurring['recurring_id'];
                }

                if (!in_array($recurring_id, $recurring_ids)) {
                    $json['error']['recurring'] = $this->language->get('error_recurring_required');
                }
            }

            if (!$json) {
                $this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

                $json['success'] = sprintf($this->language->get('text_api_success'), $product_info['name']);

                // Unset all shipping and payment methods
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);

                // Totals
                $this->load->model('setting/extension');

                $totals = array();
                $taxes = $this->cart->getTaxes();
                $total = 0;

                // Because __call can not keep var references so we put them into an array.
                $total_data = array(
                    'totals' => &$totals,
                    'taxes' => &$taxes,
                    'total' => &$total,
                );

                // Display prices
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
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

                    foreach ($totals as $key => $value) {
                        $sort_order[$key] = $value['sort_order'];
                    }

                    array_multisort($sort_order, SORT_ASC, $totals);
                }

                $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
            }

            $json['status'] = true;
        } else {
            $json['status'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function edit() {
        $this->load->language('checkout/cart');

        $json = array();

        // Update
        if (!empty($this->request->post['quantity']) && !empty($this->request->post['key'])) {

            $this->cart->update($this->request->post['key'], $this->request->post['quantity']);

            $json['success'] = $this->language->get('text_remove');

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            $json['status'] = true;
        } else {
            $json['status'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function remove() {
        $this->load->language('checkout/cart');

        $json = array();

        // Remove
        if (isset($this->request->post['key'])) {
            $this->cart->remove($this->request->post['key']);

            unset($this->session->data['vouchers'][$this->request->post['key']]);

            $json['success'] = $this->language->get('text_remove');

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['reward']);

            // Totals
            $this->load->model('setting/extension');

            $totals = array();
            $taxes = $this->cart->getTaxes();
            $total = 0;

            // Because __call can not keep var references so we put them into an array.
            $total_data = array(
                'totals' => &$totals,
                'taxes' => &$taxes,
                'total' => &$total,
            );

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
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

                foreach ($totals as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $totals);
            }

            $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

            $json['status'] = true;
        } else {
            $json['status'] = false;
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
