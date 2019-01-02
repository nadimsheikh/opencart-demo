<?php
class ModelExtensionModuleTestimonials extends Model {
	public function getTestimonials() {
     //echo $this->config->get('config_language_id');die;
		    $sql = "SELECT * FROM " . DB_PREFIX . "testimonials_description td LEFT JOIN " . DB_PREFIX . "testimonials t ON (td.testimonial_id = t.testimonial_id)  WHERE td.status='1' AND t.language_id = '" . (int)$this->config->get('config_language_id') . "'";
		    //echo $sql;die;
		    $query = $this->db->query($sql);
			return $query->rows;	
	}
		
}