<?php

class ControllerRestApiCheckoutCurrency extends Controller {

    public function index() {
        $this->load->language('api/currency');

        $json = array();

        $this->load->model('localisation/currency');

        $json['currencies'] = array();

        $results = $this->model_localisation_currency->getCurrencies();

        if ($results) {
            foreach ($results as $result) {
                if ($result['status']) {
                    $json['currencies'][] = array(
                        'title' => $result['title'],
                        'code' => $result['code'],
                        'symbol_left' => $result['symbol_left'],
                        'symbol_right' => $result['symbol_right']
                    );
                }
            }
            $json['status'] = TRUE;
        } else {
            $json['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function change() {
        $this->load->language('api/currency');

        $json = array();

        $this->load->model('localisation/currency');

        $currency_info = $this->model_localisation_currency->getCurrencyByCode($this->request->post['currency']);

        if ($currency_info) {
            $this->session->data['currency'] = $this->request->post['currency'];

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);

            $json['success'] = $this->language->get('text_success');
            $json['status'] = TRUE;
        } else {
            $json['error'] = $this->language->get('error_currency');
            $json['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
