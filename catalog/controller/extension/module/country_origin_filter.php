<?php

class ControllerExtensionModuleCountryOriginFilter extends Controller {

    public function index() {
        $this->load->language('extension/module/country_origin_filter');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_filter'] = $this->language->get('button_filter');


        if (isset($this->request->get['country_origin_filter'])) {
            $country_origin_filter = explode(',', (string) $this->request->get['country_origin_filter']);
        } else {
            $country_origin_filter = array();
        }

        if (isset($this->request->get['manufacturer_id'])) {
            $manufacturer_id = (int) $this->request->get['manufacturer_id'];
        } else {
            $manufacturer_id = 0;
        }

        $this->load->language('product/country_origin');

        $this->load->model('catalog/country_origin');

        $this->load->model('tool/image');

        $data['country_origins'] = array();

        $results = $this->model_catalog_country_origin->getCountryOrigins();

        foreach ($results as $result) {

            $checked = TRUE;

            if (in_array($result['country_origin_id'], $country_origin_filter)) {
                $checked = TRUE;
            } else {
                $checked = FALSE;
            }

            $data['country_origins'][] = array(
                'country_origin_id' => $result['country_origin_id'],
                'checked' => $checked,
                'name' => $result['name'],
            );
        }

        if (isset($this->request->get['path'])) {
            $path = $this->request->get['path'];
        } else {
            $path = '';
        }
        if (isset($this->request->get['route'])) {
            $route = $this->request->get['route'];
        } else {
            $route = '';
        }

        $url = '';

        if (isset($this->request->get['filter'])) {
            $url .= '&filter=' . $this->request->get['filter'];
        }

        if (isset($this->request->get['search'])) {
            $url .= '&search=' . $this->request->get['search'];
        }

        if (isset($this->request->get['brand_filter'])) {
            $url .= '&brand_filter=' . $this->request->get['brand_filter'];
        }

        if (isset($this->request->get['pr'])) {
            $url .= '&pr=' . $this->request->get['pr'];
        }

        if (isset($this->request->get['category_filter'])) {
            $url .= '&category_filter=' . $this->request->get['category_filter'];
        }

        if (isset($this->request->get['manufacturer'])) {
            $url .= '&manufacturer=' . $this->request->get['manufacturer'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $action = '';
        $actionUrl = '';

        if (isset($route)) {
            if (isset($path) && $path) {
                $actionUrl .= 'path=' . $path;
            }
            if (isset($manufacturer_id) && $manufacturer_id) {
                $actionUrl .= '&manufacturer_id=' . $manufacturer_id;
            }
            $action .= str_replace('&amp;', '&', $this->url->link($route, $actionUrl . $url));
        }


        $data['action'] = $action;

        return $this->load->view('extension/module/country_origin_filter', $data);
    }

}
