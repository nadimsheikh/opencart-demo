<?php
class ControllerExtensionModuleCategoryHierarchy extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/category_hierarchy');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
//		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_category_hierarchy', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

                	$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}


		
//		$data['heading_title'] = $this->language->get('heading_title');
		
//		$data['text_edit'] = $this->language->get('text_edit');
//		$data['text_enabled'] = $this->language->get('text_enabled');
//		$data['text_disabled'] = $this->language->get('text_disabled');

//		$data['entry_status'] = $this->language->get('entry_status');

//		$data['button_save'] = $this->language->get('button_save');
//		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['action'] = $this->url->link('extension/module/category_hierarchy', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['user_token'], 'SSL')
		);

                $data['breadcrumbs'][] = array(
                        'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/category_hierarchy', 'user_token=' . $this->session->data['user_token'], true)
                );
//                if (!isset($this->request->get['module_id'])) {
//                    $data['breadcrumbs'][] = array(
//                            'text' => $this->language->get('heading_title'),
//                            'href' => $this->url->link('extension/module/category_hierarchy', 'token=' . $this->session->data['user_token'], 'SSL')
//                    );
//                }else{
//                    $data['breadcrumbs'][] = array(
//                            'text' => $this->language->get('heading_title'),
//                            'href' => $this->url->link('extension/module/category_hierarchy', 'token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL')
//                    );
//                }
		
//                if (!isset($this->request->get['module_id'])) {
//                    $data['action'] = $this->url->link('extension/module/category_hierarchy', 'token=' . $this->session->data['user_token'], 'SSL');
//		} else {
//                    $data['action'] = $this->url->link('extension/module/category_hierarchy', 'token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
//		}

//		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['user_token'], 'SSL');
//                if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
//                    $module_info = $this->model_extension_module->getModule($this->request->get['module_id']);
//		}
                

		$data['action'] = $this->url->link('extension/module/category_hierarchy', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

                if (isset($this->request->post['module_category_hierarchy_status'])) {
			$data['module_category_hierarchy_status'] = $this->request->post['module_category_hierarchy_status'];
		} else {
			$data['module_category_hierarchy_status'] = $this->config->get('module_category_hierarchy_status');
		}
		$data['module_cat_hierarchy_status'] = 1;
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/category_hierarchy', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/category_hierarchy')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
        
        protected function install() {
            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission($this->user->getId(), 'access', 'extension/module/category_hierarchy');
            $this->model_user_user_group->addPermission($this->user->getId(), 'modify', 'extension/module/category_hierarchy');
        }
}
