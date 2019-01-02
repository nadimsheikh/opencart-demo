<?php

class ControllerExtensionModuleBrandFilter extends Controller {

    public function index() {
        $this->load->language('extension/module/brand_filter');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_filter'] = $this->language->get('button_filter');


        if (isset($this->request->get['brand_filter'])) {
            $brand_filter = explode(',', (string) $this->request->get['brand_filter']);
        } else {
            $brand_filter = array();
        }

        if (isset($this->request->get['manufacturer_id'])) {
            $manufacturer_id = (int) $this->request->get['manufacturer_id'];
        } else {
            $manufacturer_id = 0;
        }

        $this->load->language('product/manufacturer');

        $this->load->model('catalog/manufacturer');

        $this->load->model('tool/image');

        $data['categories'] = array();

        $results = $this->model_catalog_manufacturer->getManufacturers();

        foreach ($results as $result) {
            if (is_numeric(utf8_substr($result['name'], 0, 1))) {
                $key = '0 - 9';
            } else {
                $key = utf8_substr(utf8_strtoupper($result['name']), 0, 1);
            }

            if (!isset($data['categories'][$key])) {
                $data['categories'][$key]['name'] = $key;
            }

            $checked = TRUE;

            if (in_array($result['manufacturer_id'], $brand_filter)) {
                $checked = TRUE;
            } else {
                $checked = FALSE;
            }

            $data['categories'][$key]['manufacturer'][] = array(
                'manufacturer_id' => $result['manufacturer_id'],
                'checked' => $checked,
                'name' => $result['name'],
                'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
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

        if (isset($this->request->get['pr'])) {
            $url .= '&pr=' . $this->request->get['pr'];
        }

        if (isset($this->request->get['country_origin_filter'])) {
            $url .= '&country_origin_filter=' . $this->request->get['country_origin_filter'];
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

        return $this->load->view('extension/module/brand_filter', $data);
    }

}
