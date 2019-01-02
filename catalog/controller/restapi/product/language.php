<?php

class ControllerRestApiProductLanguage extends Controller {

    public function index() {
        $this->load->language('common/language');
        $data['status'] = TRUE;
        $data['code'] = $this->session->data['language'];

        $this->load->model('localisation/language');

        $data['languages'] = array();

        $results = $this->model_localisation_language->getLanguages();

        if ($results) {            
            foreach ($results as $result) {
                if ($result['status']) {
                    $data['languages'][] = array(
                        'name' => $result['name'],
                        'code' => $result['code']
                    );
                }
            }
        } else {
            $data['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function change() {
        $this->load->language('common/language');
        $data['status'] = TRUE;
        if (isset($this->request->post['code'])) {
            $this->session->data['language'] = $this->request->post['code'];
        }else{
            $data['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
