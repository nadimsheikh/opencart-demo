<?php

class ModelExtensionModuleTestimonials extends Model {

	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "testimonials` (
           `id` int(200) NOT NULL AUTO_INCREMENT,
           `testimonial_id` int(200) NOT NULL,
           `language_id` varchar(200) NOT NULL,
           `store_id` varchar(200) DEFAULT NULL,
           `author` varchar(100) NOT NULL,
           `description` varchar(500) NOT NULL,
           `image` varchar(100) NOT NULL,
           PRIMARY KEY (`id`)
           ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");

		$this->db->query("
   			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "testimonials_description` (
               `testimonial_id` int(200) NOT NULL AUTO_INCREMENT,
               `status` int(100) NOT NULL,
               `sort_order` int(100) NOT NULL,
               `designation` varchar(100) NOT NULL,
                PRIMARY KEY (`testimonial_id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");


    
       }

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "testimonials;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "testimonials_description;");
		
	}
}

