<?php

class ControllerRestApiAccountWishList extends Controller {

    public function __construct($registry) {
        parent::__construct($registry);
        if (isset($this->request->post['customer_id'])) {
            $this->customer->setId($this->request->post['customer_id']);
        }
        if (isset($this->request->post['language'])) {
            $this->session->data['language'] = $this->request->post['language'];
        }
        if (isset($this->request->post['currency'])) {
            $this->session->data['currency'] = $this->request->post['currency'];
        }
    }

    public function index() {       

        $this->load->language('account/wishlist');

        $this->load->model('account/wishlist');

        $this->load->model('catalog/product');

        $this->load->model('tool/image');


        $data['products'] = array();

        $results = $this->model_account_wishlist->getWishlist();
        if ($results) {
            $data['status'] = TRUE;
            foreach ($results as $result) {
                $product_info = $this->model_catalog_product->getProduct($result['product_id']);

                if ($product_info) {
                    if ($product_info['image']) {
                        $image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_wishlist_height'));
                    } else {
                        $image = false;
                    }

                    if ($product_info['quantity'] <= 0) {
                        $stock = $product_info['stock_status'];
                    } elseif ($this->config->get('config_stock_display')) {
                        $stock = $product_info['quantity'];
                    } else {
                        $stock = $this->language->get('text_instock');
                    }

                    if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                        $price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $price = false;
                    }

                    if ((float) $product_info['special']) {
                        $special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    } else {
                        $special = false;
                    }

                    $data['products'][] = array(
                        'product_id' => $product_info['product_id'],
                        'thumb' => $image,
                        'name' => $product_info['name'],
                        'model' => $product_info['model'],
                        'stock' => $stock,
                        'price' => $price,
                        'special' => $special,
                        'href' => $this->url->link('product/product', 'product_id=' . $product_info['product_id']),
                        'remove' => $this->url->link('account/wishlist', 'remove=' . $product_info['product_id'])
                    );
                } else {
                    $this->model_account_wishlist->deleteWishlist($result['product_id']);
                }
            }
        } else {
            $data['status'] = FALSE;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    public function add() {
        $this->load->language('account/wishlist');

        $json = array();      

        if (isset($this->request->post['product_id'])) {
            $product_id = $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            if ($this->customer->isLogged()) {
                // Edit customers cart
                $this->load->model('account/wishlist');

                $this->model_account_wishlist->addWishlist($this->request->post['product_id']);

                $json['success'] = sprintf($this->language->get('text_api_success'), $product_info['name']);

                $json['total'] = sprintf($this->language->get('text_wishlist'), $this->model_account_wishlist->getTotalWishlist());
            }
            $json['status'] = TRUE;
        } else {
            $json['status'] = FALSE;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function remove() {
        $this->load->language('account/wishlist');
        $this->load->model('account/wishlist');
        $data = array();

        

        if (isset($this->request->post['product_id'])) {
            // Remove Wishlist
            $this->model_account_wishlist->deleteWishlist($this->request->post['product_id']);

            $data['success'] = $this->language->get('text_remove');
            $data['status'] = TRUE;
        } else {
            $data['status'] = FALSE;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

}
