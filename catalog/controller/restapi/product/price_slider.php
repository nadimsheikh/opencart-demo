<?php

class ControllerRestApiProductPriceSlider extends Controller {

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

        $this->load->language('extension/module/price_slider');

        if ($this->config->get('module_price_slider_status')) {
            $this->load->model('catalog/category');
            $this->load->model('catalog/product');

            $min_max = '';

            if (isset($this->request->get['manufacturer_id'])) {
                $manufacturer_id = (int) $this->request->get['manufacturer_id'];
            } else {
                $manufacturer_id = 0;
            }

            if (isset($this->request->get['category_id'])) {
                $category_id = (int) $this->request->get['category_id'];
            } else {
                $category_id = 0;
            }

            if ($category_id) {
                $category_info = $this->model_catalog_category->getCategory($category_id);
            } else {
                $category_info = array();
            }

            if ($category_info) {
                $filter_data = array(
                    'filter_category_id' => $category_id,
                );
                $results = $this->model_catalog_product->getMinMaxProduct($filter_data);

                foreach ($results as $result) {
                    if (!$min_max) {
                        $min_max = $result;
                    } else {
                        $min_max .= '-' . $result;
                    }
                }
            } else {
                $results = $this->model_catalog_product->getMinMaxProduct();

                foreach ($results as $result) {
                    if (!$min_max) {
                        $min_max = $result;
                    } else {
                        $min_max .= '-' . $result;
                    }
                }
            }

            $data['status'] = TRUE;
            $data['price_slider_title'] = $this->config->get('module_price_slider_heading');

            if (!$min_max) {
                $range = explode('-', '0-0');
            } else {
                $range = explode('-', $min_max);
            }

            $data['min_max'] = $min_max;
            $data['price_range_min'] = $this->currency->format($range[0], $this->session->data['currency'], '', false);
            $data['price_range_max'] = $this->currency->format($range[1], $this->session->data['currency'], '', false);

            $data['price_min'] = $this->currency->format($range[0], $this->session->data['currency']);
            $data['price_max'] = $this->currency->format($range[1], $this->session->data['currency']);
        } else {
            $data['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
