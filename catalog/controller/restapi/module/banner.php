<?php

class ControllerRestApiModuleBanner extends Controller {

    public function index() {

        $this->load->model('design/banner');
        $this->load->model('tool/image');

        $data['banners'] = array();
        $data['status'] = true;

        $results = $this->model_design_banner->getBanners();
        if ($results) {
            foreach ($results as $result) {
                $data['banners'][] = array(
                    'banner_id' => $result['banner_id'],
                    'name' => $result['name'],
                );
            }
        } else {
            $data['status'] = false;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function detail() {
        if (isset($this->request->post['banner_id'])) {
            $banner_id = $this->request->post['banner_id'];
        } else {
            $banner_id = 0;
        }
        if (isset($this->request->post['width'])) {
            $width = $this->request->post['width'];
        } else {
            $width = 100;
        }
        if (isset($this->request->post['height'])) {
            $height = $this->request->post['height'];
        } else {
            $height = 100;
        }

        $this->load->model('design/banner');
        $this->load->model('tool/image');

        $data['banners'] = array();
        $data['status'] = true;
        $results = $this->model_design_banner->getBanner($banner_id);
        if ($results) {
            foreach ($results as $result) {
                if (is_file(DIR_IMAGE . $result['image'])) {
                    $data['banners'][] = array(
                        'title' => $result['title'],
                        'link' => $result['link'],
                        'image' => $this->model_tool_image->resize($result['image'], $width, $height)
                    );
                }
            }
        } else {
            $data['status'] = false;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
