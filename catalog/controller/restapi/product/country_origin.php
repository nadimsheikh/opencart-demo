<?php

class ControllerRestApiProductCountryOrigin extends Controller {

    public function index() {
        $this->load->language('product/country_origin');

        $this->load->model('catalog/country_origin');

        $data['country_origins'] = array();

        $results = $this->model_catalog_country_origin->getCountryOrigins();

        if ($results) {
            $data['status'] = TRUE;
            foreach ($results as $result) {
                $data['country_origins'][] = array(
                    'country_origin_id' => $result['country_origin_id'],
                    'name' => $result['name'],
                );
            }
        } else {
            $data['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
