<?php

class ControllerRestApiModuleTestimonials extends Controller {

    public function index() {
        $this->load->language('extension/module/testimonials');
        $this->load->model('extension/module/testimonials');

        $this->load->model('design/banner');
        $this->load->model('tool/image');

        $result = $this->model_extension_module_testimonials->getTestimonials();

        $data['module_testimonials_awidth'] = $this->config->get('module_testimonials_awidth');
        $data['module_testimonials_aheight'] = $this->config->get('module_testimonials_aheight');
        $data['testimonials'] = array();
        foreach ($result as $results) {
            $data['testimonials'][] = array(
                'author' => $results['author'],
                'image' => $results['image'],
                'thumb' => $this->model_tool_image->resize($results['image'], $data['module_testimonials_awidth'], $data['module_testimonials_aheight']),
                'description' => $results['description'],
                'status' => $results['status'],
                'sort_order' => $results['sort_order'],
                'designation' => $results['designation']
            );
        }
        //echo'<pre>';print_r($data);die;
        $data['module_testimonials_status'] = $this->config->get('module_testimonials_status');

        $data['module_testimonials_aheight'] = $this->config->get('module_testimonials_aheight');

        $data['module_testimonials_awidth'] = $this->config->get('module_testimonials_awidth');

        $data['module_testimonials_heading'] = $this->config->get('module_testimonials_heading');

        $data['module_testimonials_bgclr'] = $this->config->get('module_testimonials_bgclr');

        $data['module_testimonials_fontclr'] = $this->config->get('module_testimonials_fontclr');

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
