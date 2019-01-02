<?php

class ControllerCommonMenu extends Controller {

    public function index() {
        $this->load->language('common/menu');

        // Menu
        $this->load->model('catalog/category');

        $this->load->model('catalog/product');

        $data['categories'] = array();
        $results = $this->model_catalog_category->getCategories(0);
        $this->load->model('tool/image');

        foreach ($results as $result) {
            if ($result['top']) {
                $children_data = array();
                $children = $this->model_catalog_category->getCategories($result['category_id']);

                foreach ($children as $child) {
                    $gchildren_data = array();
                    $gchildren = $this->model_catalog_category->getCategories($child['category_id']);

                    foreach ($gchildren as $gchild) {
                        $gchildren_data[] = array(
                            'category_id' => $gchild['category_id'],
                            'name' => $gchild['name'],
                            'href' => $this->url->link('product/category', 'path=' . $result['category_id'] . '_' . $child['category_id'] . '_' . $gchild['category_id'])
                        );
                    }

                    if ($child['image']) {
                        $image = $this->model_tool_image->resize($child['image'], 80, 80);
                    } else {
                        $image = false;
                    }

                    $children_data[] = array(
                        'category_id' => $child['category_id'],
                        'name' => $child['name'],
                        'gchildren' => $gchildren_data,
                        'image' => $image,
                        'href' => $this->url->link('product/category', 'path=' . $result['category_id'] . '_' . $child['category_id'])
                    );
                }

                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], 80, 80);
                } else {
                    $image = false;
                }

                $CatText = html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8');

                $data['categories'][] = array(
                    'name' => $result['name'],
                    'category_id' => $result['category_id'],
                    'children' => $children_data,
                    'description' => $CatText,
                    'image' => $image,
                    'href' => $this->url->link('product/category', 'path=' . $result['category_id'])
                );
            }
        }

//        print_r($data['categories']);
//        exit;

        $this->load->model('catalog/information');

        $data['informations'] = array();

        foreach ($this->model_catalog_information->getInformations() as $result) {
            if ($result['bottom']) {
                $data['informations'][] = array(
                    'title' => $result['title'],
                    'href' => $this->url->link('information/information', 'information_id=' . $result['information_id'])
                );
            }
        }

        $this->load->model('catalog/manufacturer');

        $data['manufacturers'] = array();

        foreach ($this->model_catalog_manufacturer->getManufacturers() as $result) {
            $data['manufacturers'][] = array(
                'name' => $result['name'],
                'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
            );
        }

        $this->load->language('common/footer');
        $data['all_products'] = $this->url->link('product/category&path=all');
        $data['special'] = $this->url->link('product/special');
        $data['contact'] = $this->url->link('information/contact');

        return $this->load->view('common/menu', $data);
    }

}
