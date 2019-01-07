<?php

class ControllerRestApiInformationInformation extends Controller {

    public function index() {
        $this->load->language('information/information');

        $this->load->model('catalog/information');
        $data['status'] = true;
        $data['informations'] = array();

        $informations = $this->model_catalog_information->getInformations();

        if ($informations) {
            foreach ($informations as $information) {
                $data['informations'][] = array(
                    'information_id' => $information['information_id'],
                    'title' => $information['title'],
                    'description' => $information['description'],
                    'bottom' => $information['bottom'],
                    'sort_order' => $information['sort_order'],
                    'status' => $information['status'],
                );
            }
        } else {
            $data['status'] = false;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function detail() {
        $this->load->language('information/information');

        $this->load->model('catalog/information');

        $data['status'] = true;

        if (isset($this->request->get['information_id'])) {
            $information_id = (int) $this->request->get['information_id'];
        } else {
            $information_id = 0;
        }

        $information_info = $this->model_catalog_information->getInformation($information_id);

        if ($information_info) {
            $data['heading_title'] = $information_info['title'];
            $data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');
        } else {
            $data['status'] = false;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
