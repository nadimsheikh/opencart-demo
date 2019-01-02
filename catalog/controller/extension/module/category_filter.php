<?php

class ControllerExtensionModuleCategoryFilter extends Controller {

    public function index() {
        $this->load->language('extension/module/category_filter');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['button_filter'] = $this->language->get('button_filter');

        if (isset($this->request->get['category_filter'])) {
            $category_filter = explode(',', (string) $this->request->get['category_filter']);
        } else {
            $category_filter = array();
        }

        if (isset($this->request->get['manufacturer_id'])) {
            $manufacturer_id = (int) $this->request->get['manufacturer_id'];
        } else {
            $manufacturer_id = 0;
        }


        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $data['categories'] = array();

        $categories = $this->model_catalog_category->getCategories(0);

        foreach ($categories as $category) {
            $children_data = array();

            $children = $this->model_catalog_category->getCategories($category['category_id']);

            foreach ($children as $child) {

                $children_data2 = array();

                $children2 = $this->model_catalog_category->getCategories($child['category_id']);

                foreach ($children2 as $child2) {
                    $filter_data2 = array('filter_category_id' => $child2['category_id'], 'filter_sub_category' => true);

                    if (in_array($child2['category_id'], $category_filter)) {
                        $checked = TRUE;
                    } else {
                        $checked = FALSE;
                    }

                    $children_data2[] = array(
                        'category_id' => $child2['category_id'],
                        'name' => $child2['name'],
                        'checked' => $checked,
                    );
                }

                $filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

                if (in_array($child['category_id'], $category_filter)) {
                    $checked = TRUE;
                } else {
                    $checked = FALSE;
                }

                $children_data[] = array(
                    'category_id' => $child['category_id'],
                    'name' => $child['name'],
                    'checked' => $checked,
                    'children' => $children_data2
                );
            }

            $filter_data = array(
                'filter_category_id' => $category['category_id'],
                'filter_sub_category' => true
            );

            if (in_array($category['category_id'], $category_filter)) {
                $checked = TRUE;
            } else {
                $checked = FALSE;
            }

            $data['categories'][] = array(
                'category_id' => $category['category_id'],
                'name' => $category['name'],
                'children' => $children_data,
                'checked' => $checked,
            );
        }

//        print_r($data);
//        exit;

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

        if (isset($this->request->get['country_origin_filter'])) {
            $url .= '&country_origin_filter=' . $this->request->get['country_origin_filter'];
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

//        print_r($data['action']);
//        exit;

        return $this->load->view('extension/module/category_filter', $data);
    }

}
