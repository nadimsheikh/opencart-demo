<?php
class ModelCatalogTestimonials extends Model {
	public function addTestimonial($data) { 
   //echo "INSERT INTO " . DB_PREFIX . "testimonials SET sort_order = '" .$data['sort_order'] . "',  status = '" .$data['status'] . "', author='".$data['author']."', image='".$data['image']."', description='".$data['description']."', author='".$data['image']."', status='".$data['status']."', sort_order='".$data['sort_order']."' "; die;
	//die('yes');
		//echo'<pre>';print_r($data);die;
		$this->db->query("INSERT INTO " . DB_PREFIX . "testimonials_description SET  status = '" .$data['status'] . "',   designation='".$data['designation']."', sort_order='".$data['sort_order']."' ");
      
      $testimonial_id = $this->db->getLastId();


		foreach ($data['testimonials'] as $language_id => $value) {
			//echo "INSERT INTO " . DB_PREFIX . "testimonials SET testimonial_id = '" . (int)$testimonial_id . "', language_id = '" . (int)$language_id . "', author = '" . $this->db->escape($value['title']) . "', image = '" . $this->db->escape($value['image']) . "', description = '" . $this->db->escape($value['description']) . "'";die;
			//echo '<pre>';print_r($value);
			//echo (int)$language_id.(int)$testimonial_id;
			//echo "INSERT INTO " . DB_PREFIX . "testimonials SET testimonial_id = '" . (int)$testimonial_id . "', language_id = '" . (int)$language_id . "', author = '" . $this->db->escape($value['title']) . "', image = '" . $this->db->escape($value['image']) . "', description = '" . $this->db->escape($value['description']) . "'";die;
			$this->db->query("INSERT INTO " . DB_PREFIX . "testimonials SET testimonial_id = '" . (int)$testimonial_id . "', language_id = '" . (int)$language_id . "', author = '" . $this->db->escape($value['title']) . "', image = '" . $this->db->escape($value['image']) . "', description = '" . $this->db->escape($value['description']) . "'");
        
		}

		//die;
  }

  public function getTotalTestimonials() { 
  // die;
       $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "testimonials_description");

		return $query->row['total'];
  }

  public function getTestimonials($data = array()) {

		if ($data) {
			$sql ="SELECT * FROM " . DB_PREFIX . "testimonials t LEFT JOIN " . DB_PREFIX . "testimonials_description td ON (t.testimonial_id = td.testimonial_id) WHERE t.language_id = '" . (int)$this->config->get('config_language_id') . "'";

			$sort_data = array(
				't.author',
				'td.sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY t.author";
			}

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}

			$query = $this->db->query($sql);

			return $query->rows;

		} else {
			$testimonials_data = $this->cache->get('testimonials.' . (int)$this->config->get('config_language_id'));

			if (!$testimonials_data) {

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "testimonials  WHERE id.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY title");

				$testimonials_data = $query->rows;

				$this->cache->set('testimonials.' . (int)$this->config->get('config_language_id'), $testimonials_data);
			}

			return $testimonials_data;
		}
	}

      public function getTestimonial($testimonial_id) {
      	 //echo $testimonial_id;die('sss');
      //	echo "SELECT DISTINCT * FROM " . DB_PREFIX . "testimonals WHERE testimonial_id = '" . (int)$testimonial_id . "'";die;
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "testimonials WHERE testimonial_id = '" . (int)$testimonial_id . "'");
    
		return $query->row;
	}


    public function getTestimonialDescriptions($testimonial_id) {
    //die('db');
    $testimonial_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "testimonials_description WHERE testimonial_id = '" . (int)$testimonial_id . "'");

		return $query->rows;
    }
     public function getTestimonialss($testimonial_id) {
    //die('db');
    $testimonial_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "testimonials WHERE testimonial_id = '" . (int)$testimonial_id . "'");

		return $query->rows;
    }


    public function editTestimonials($testimonial_id,$data) { 
     // echo '<pre>'; print_r($data);die;
		$this->db->query("UPDATE " . DB_PREFIX . "testimonials_description SET  status = '" .$data['status'] . "',   designation='".$data['designation']."', sort_order='".$data['sort_order']."' WHERE testimonial_id='".$testimonial_id."' ");
      
       $this->db->query("DELETE FROM " . DB_PREFIX . "testimonials WHERE testimonial_id='".$testimonial_id."'");


		foreach ($data['testimonials'] as $language_id => $value) {
			
			$this->db->query("INSERT INTO " . DB_PREFIX . "testimonials SET testimonial_id = '" . (int)$testimonial_id . "', language_id = '" . (int)$language_id . "', author = '" . $this->db->escape($value['title']) . "', image = '" . $this->db->escape($value['image']) . "', description = '" . $this->db->escape($value['description']) . "' ");
        
		}
 
    }	


     public function deleteTestimonial($testimonial_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "testimonials` WHERE testimonial_id = '" . (int)$testimonial_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "testimonials_description` WHERE testimonial_id = '" . (int)$testimonial_id . "'");
	

		$this->cache->delete('testimonials');
	}


	
}
