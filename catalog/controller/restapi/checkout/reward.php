<?php

class ControllerRestApiCheckoutReward extends Controller {

    public function index() {
        $this->load->language('api/reward');
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
        // Delete past reward in case there is an error
        unset($this->session->data['reward']);

        $json = array();


        $points = $this->customer->getRewardPoints();

        $points_total = 0;

        foreach ($this->cart->getProducts() as $product) {
            if ($product['points']) {
                $points_total += $product['points'];
            }
        }

        if (empty($this->request->post['reward'])) {
            $json['error'] = $this->language->get('error_reward');
        }

        if ($this->request->post['reward'] > $points) {
            $json['error'] = sprintf($this->language->get('error_points'), $this->request->post['reward']);
        }

        if ($this->request->post['reward'] > $points_total) {
            $json['error'] = sprintf($this->language->get('error_maximum'), $points_total);
        }

        if (!$json) {
            $this->session->data['reward'] = abs($this->request->post['reward']);
            $json['status'] = TRUE;
            $json['success'] = $this->language->get('text_success');
        } else {
            $json['status'] = FALSE;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function maximum() {
        $this->load->language('api/reward');
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
        $json = array();

        $json['maximum'] = 0;

        if ($this->cart->getProducts()) {
            $json['status'] = TRUE;
            foreach ($this->cart->getProducts() as $product) {
                if ($product['points']) {
                    $json['maximum'] += $product['points'];
                }
            }
        } else {
            $json['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function available() {
        $this->load->language('api/reward');
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
        $json = array();
        $json['status'] = TRUE;
        $json['points'] = $this->customer->getRewardPoints();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

}
