<?php

class ControllerRestApiAccountAddress extends Controller {

    private $error = array();

    public function index() {
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }

        $this->load->language('account/address');

        $this->load->model('account/address');

        $this->getList();
    }

    public function add() {
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
        $this->load->language('account/address');

        $this->load->model('account/address');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_address->addAddress($this->customer->getId(), $this->request->post);

            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_add');
        } else {
            $data['status'] = FALSE;
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

            if (isset($this->error['address_1'])) {
                $data['error_address_1'] = $this->error['address_1'];
            } else {
                $data['error_address_1'] = '';
            }

            if (isset($this->error['city'])) {
                $data['error_city'] = $this->error['city'];
            } else {
                $data['error_city'] = '';
            }

            if (isset($this->error['postcode'])) {
                $data['error_postcode'] = $this->error['postcode'];
            } else {
                $data['error_postcode'] = '';
            }

            if (isset($this->error['country'])) {
                $data['error_country'] = $this->error['country'];
            } else {
                $data['error_country'] = '';
            }

            if (isset($this->error['zone'])) {
                $data['error_zone'] = $this->error['zone'];
            } else {
                $data['error_zone'] = '';
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

    public function edit() {
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }

        $this->load->language('account/address');

        $this->load->model('account/address');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_address->editAddress($this->request->get['address_id'], $this->request->post);

            // Default Shipping Address
            if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
                $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            // Default Payment Address
            if (isset($this->session->data['payment_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address']['address_id'])) {
                $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
            }
            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_edit');
        } else {
            $data['status'] = FALSE;
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

            if (isset($this->error['address_1'])) {
                $data['error_address_1'] = $this->error['address_1'];
            } else {
                $data['error_address_1'] = '';
            }

            if (isset($this->error['city'])) {
                $data['error_city'] = $this->error['city'];
            } else {
                $data['error_city'] = '';
            }

            if (isset($this->error['postcode'])) {
                $data['error_postcode'] = $this->error['postcode'];
            } else {
                $data['error_postcode'] = '';
            }

            if (isset($this->error['country'])) {
                $data['error_country'] = $this->error['country'];
            } else {
                $data['error_country'] = '';
            }

            if (isset($this->error['zone'])) {
                $data['error_zone'] = $this->error['zone'];
            } else {
                $data['error_zone'] = '';
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

    public function delete() {
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }

        $this->load->language('account/address');

        $this->load->model('account/address');

        if (isset($this->request->get['address_id']) && $this->validateDelete()) {
            $this->model_account_address->deleteAddress($this->request->get['address_id']);

            // Default Shipping Address
            if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
                unset($this->session->data['shipping_address']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            // Default Payment Address
            if (isset($this->session->data['payment_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address']['address_id'])) {
                unset($this->session->data['payment_address']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
            }

            $data['status'] = TRUE;
            $data['success'] = $this->language->get('text_delete');
        } else {
            $data['status'] = FALSE;
            if (isset($this->error['warning'])) {
                $data['error_warning'] = $this->error['warning'];
            } else {
                $data['error_warning'] = '';
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    protected function getList() {
        $data['addresses'] = array();

        $results = $this->model_account_address->getAddresses();
        if ($results) {
            $data['status'] = TRUE;
            foreach ($results as $result) {

                if ($result['address_format']) {
                    $format = $result['address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $result['firstname'],
                    'lastname' => $result['lastname'],
                    'company' => $result['company'],
                    'address_1' => $result['address_1'],
                    'address_2' => $result['address_2'],
                    'city' => $result['city'],
                    'postcode' => $result['postcode'],
                    'zone' => $result['zone'],
                    'zone_code' => $result['zone_code'],
                    'country' => $result['country']
                );


                $data['addresses'][] = array(
                    'address_id' => $result['address_id'],
                    'address' => str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format)))),
                );
            }
        } else {
            $data['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function getDetail() {
        $json = array();
        $data = array();
        $this->load->language('account/address');

        $this->load->model('account/address');
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }

        $address_info = $this->model_account_address->getAddress($this->request->get['address_id']);

        if ($address_info) {
            if (!empty($address_info)) {
                $data['firstname'] = $address_info['firstname'];
            } else {
                $data['firstname'] = '';
            }

            if (!empty($address_info)) {
                $data['lastname'] = $address_info['lastname'];
            } else {
                $data['lastname'] = '';
            }

            if (!empty($address_info)) {
                $data['company'] = $address_info['company'];
            } else {
                $data['company'] = '';
            }

            if (!empty($address_info)) {
                $data['address_1'] = $address_info['address_1'];
            } else {
                $data['address_1'] = '';
            }

            if (!empty($address_info)) {
                $data['address_2'] = $address_info['address_2'];
            } else {
                $data['address_2'] = '';
            }

            if (!empty($address_info)) {
                $data['postcode'] = $address_info['postcode'];
            } else {
                $data['postcode'] = '';
            }

            if (!empty($address_info)) {
                $data['city'] = $address_info['city'];
            } else {
                $data['city'] = '';
            }

            if (!empty($address_info)) {
                $data['country_id'] = $address_info['country_id'];
            } else {
                $data['country_id'] = $this->config->get('config_country_id');
            }

            if (!empty($address_info)) {
                $data['country'] = $address_info['country'];
            } else {
                $data['country'] = '';
            }

            if (!empty($address_info)) {
                $data['zone_id'] = $address_info['zone_id'];
            } else {
                $data['zone_id'] = '';
            }

            if (!empty($address_info)) {
                $data['zone'] = $address_info['zone'];
            } else {
                $data['zone'] = '';
            }

            // Custom fields
            $data['custom_fields'] = array();

            $this->load->model('account/custom_field');

            $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

            foreach ($custom_fields as $custom_field) {
                if ($custom_field['location'] == 'address') {
                    $data['custom_fields'][] = $custom_field;
                }
            }

            if (isset($this->request->post['custom_field']['address'])) {
                $data['address_custom_field'] = $this->request->post['custom_field']['address'];
            } elseif (isset($address_info)) {
                $data['address_custom_field'] = $address_info['custom_field'];
            } else {
                $data['address_custom_field'] = array();
            }

            if (isset($this->request->post['default'])) {
                $data['default'] = $this->request->post['default'];
            } elseif (isset($this->request->get['address_id'])) {
                $data['default'] = $this->customer->getAddressId() == $this->request->get['address_id'];
            } else {
                $data['default'] = false;
            }

            $json['data'] = $data;
            $json['status'] = TRUE;
        } else {
            $json['data'] = $data;
            $json['status'] = FALSE;
        }



        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validateForm() {
        if ((utf8_strlen(trim($this->request->post['firstname'])) < 1) || (utf8_strlen(trim($this->request->post['firstname'])) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((utf8_strlen(trim($this->request->post['lastname'])) < 1) || (utf8_strlen(trim($this->request->post['lastname'])) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((utf8_strlen(trim($this->request->post['address_1'])) < 3) || (utf8_strlen(trim($this->request->post['address_1'])) > 128)) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }

        if ((utf8_strlen(trim($this->request->post['city'])) < 2) || (utf8_strlen(trim($this->request->post['city'])) > 128)) {
            $this->error['city'] = $this->language->get('error_city');
        }

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

        if ($country_info && $country_info['postcode_required'] && (utf8_strlen(trim($this->request->post['postcode'])) < 2 || utf8_strlen(trim($this->request->post['postcode'])) > 10)) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }

        if ($this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
            $this->error['zone'] = $this->language->get('error_zone');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if ($this->model_account_address->getTotalAddresses() == 1) {
            $this->error['warning'] = $this->language->get('error_delete');
        }

        if ($this->customer->getAddressId() == $this->request->get['address_id']) {
            $this->error['warning'] = $this->language->get('error_default');
        }

        return !$this->error;
    }

    public function countries() {
        $this->load->model('localisation/country');
        $data['countries'] = array();
        $countries = $this->model_localisation_country->getCountries();

        if ($countries) {
            $data['status'] = TRUE;
            $data['countries'] = $countries;
        } else {
            $data['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function zones() {
        $data = array();
        $data['data'] = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $data['data'] = array(
                'country_id' => $country_info['country_id'],
                'name' => $country_info['name'],
                'iso_code_2' => $country_info['iso_code_2'],
                'iso_code_3' => $country_info['iso_code_3'],
                'address_format' => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status' => $country_info['status']
            );
            $data['status'] = TRUE;
        } else {
            $data['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
