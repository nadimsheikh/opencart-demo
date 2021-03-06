<?php

class ModelCatalogCountryOrigin extends Model {

    public function getCountryOrigin($country_origin_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country_origin m LEFT JOIN " . DB_PREFIX . "country_origin_to_store m2s ON (m.country_origin_id = m2s.country_origin_id) WHERE m.country_origin_id = '" . (int) $country_origin_id . "' AND m2s.store_id = '" . (int) $this->config->get('config_store_id') . "'");

        return $query->row;
    }

    public function getCountryOrigins($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM " . DB_PREFIX . "country_origin m LEFT JOIN " . DB_PREFIX . "country_origin_to_store m2s ON (m.country_origin_id = m2s.country_origin_id) WHERE m2s.store_id = '" . (int) $this->config->get('config_store_id') . "'";

            $sort_data = array(
                'name',
                'sort_order'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY name";
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

                $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $country_origin_data = $this->cache->get('country_origin.' . (int) $this->config->get('config_store_id'));

            if (!$country_origin_data) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country_origin m LEFT JOIN " . DB_PREFIX . "country_origin_to_store m2s ON (m.country_origin_id = m2s.country_origin_id) WHERE m2s.store_id = '" . (int) $this->config->get('config_store_id') . "' ORDER BY name");

                $country_origin_data = $query->rows;

                $this->cache->set('country_origin.' . (int) $this->config->get('config_store_id'), $country_origin_data);
            }

            return $country_origin_data;
        }
    }

}
