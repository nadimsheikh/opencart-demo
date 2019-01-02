<?php
class ControllerExtensionModuleTestimonials extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/testimonials');

        	$this->load->model('tool/image');


		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
          
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_testimonials', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/testimonials', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/testimonials', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['module_testimonials_status'])) {
			$data['module_testimonials_status'] = $this->request->post['module_testimonials_status'];
		} else {
			$data['module_testimonials_status'] = $this->config->get('module_testimonials_status');
		}

		if (isset($this->request->post['module_testimonials_aheight'])) {
			$data['module_testimonials_aheight'] = $this->request->post['module_testimonials_aheight'];
		} else {
			$data['module_testimonials_aheight'] = $this->config->get('module_testimonials_aheight');
		}

		if (isset($this->request->post['module_testimonials_heading'])) {
			$data['module_testimonials_heading'] = $this->request->post['module_testimonials_heading'];
		} else {
			$data['module_testimonials_heading'] = $this->config->get('module_testimonials_heading');
		}

		if (isset($this->request->post['module_testimonials_awidth'])) {
			$data['module_testimonials_awidth'] = $this->request->post['module_testimonials_awidth'];
		} else {
			$data['module_testimonials_awidth'] = $this->config->get('module_testimonials_awidth');
		}

		if (isset($this->request->post['module_testimonials_bgclr'])) {
			$data['module_testimonials_bgclr'] = $this->request->post['module_testimonials_bgclr'];
		} else {
			$data['module_testimonials_bgclr'] = $this->config->get('module_testimonials_bgclr');
		}

		if (isset($this->request->post['module_testimonials_fontclr'])) {
			$data['module_testimonials_fontclr'] = $this->request->post['module_testimonials_fontclr'];
		} else {
			$data['module_testimonials_fontclr'] = $this->config->get('module_testimonials_fontclr');
		}
        
       

        $data['placeholder']= $this->model_tool_image->resize('no_image.png',100,100);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/testimonials', $data));
	}

	public function install() {
	   
		$this->load->model('extension/module/testimonials');

		$this->model_extension_module_testimonials->install();
	}

	public function uninstall() {
	   
		$this->load->model('extension/module/testimonials');

		$this->model_extension_module_testimonials->uninstall();
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/testimonials')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}