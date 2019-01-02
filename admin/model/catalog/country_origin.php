<?php

class ModelCatalogCountryOrigin extends Model {

    public function addCountryOrigin($data) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "country_origin SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int) $data['sort_order'] . "'");

        $country_origin_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "country_origin SET image = '" . $this->db->escape($data['image']) . "' WHERE country_origin_id = '" . (int) $country_origin_id . "'");
        }

        if (isset($data['country_origin_store'])) {
            foreach ($data['country_origin_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "country_origin_to_store SET country_origin_id = '" . (int) $country_origin_id . "', store_id = '" . (int) $store_id . "'");
            }
        }

        // SEO URL
        if (isset($data['country_origin_seo_url'])) {
            foreach ($data['country_origin_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int) $store_id . "', language_id = '" . (int) $language_id . "', query = 'country_origin_id=" . (int) $country_origin_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }

        $this->cache->delete('country_origin');

        return $country_origin_id;
    }

    public function editCountryOrigin($country_origin_id, $data) {
        $this->db->query("UPDATE " . DB_PREFIX . "country_origin SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int) $data['sort_order'] . "' WHERE country_origin_id = '" . (int) $country_origin_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "country_origin SET image = '" . $this->db->escape($data['image']) . "' WHERE country_origin_id = '" . (int) $country_origin_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "country_origin_to_store WHERE country_origin_id = '" . (int) $country_origin_id . "'");

        if (isset($data['country_origin_store'])) {
            foreach ($data['country_origin_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "country_origin_to_store SET country_origin_id = '" . (int) $country_origin_id . "', store_id = '" . (int) $store_id . "'");
            }
        }

        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'country_origin_id=" . (int) $country_origin_id . "'");

        if (isset($data['country_origin_seo_url'])) {
            foreach ($data['country_origin_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO `" . DB_PREFIX . "seo_url` SET store_id = '" . (int) $store_id . "', language_id = '" . (int) $language_id . "', query = 'country_origin_id=" . (int) $country_origin_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                    }
                }
            }
        }

        $this->cache->delete('country_origin');
    }

    public function deleteCountryOrigin($country_origin_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "country_origin` WHERE country_origin_id = '" . (int) $country_origin_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "country_origin_to_store` WHERE country_origin_id = '" . (int) $country_origin_id . "'");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE query = 'country_origin_id=" . (int) $country_origin_id . "'");

        $this->cache->delete('country_origin');
    }

    public function getCountryOrigin($country_origin_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "country_origin WHERE country_origin_id = '" . (int) $country_origin_id . "'");

        return $query->row;
    }

    public function getCountryOrigins($data = array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "country_origin";

        if (!empty($data['filter_name'])) {
            $sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

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
    }

    public function getCountryOriginStores($country_origin_id) {
        $country_origin_store_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "country_origin_to_store WHERE country_origin_id = '" . (int) $country_origin_id . "'");

        foreach ($query->rows as $result) {
            $country_origin_store_data[] = $result['store_id'];
        }

        return $country_origin_store_data;
    }

    public function getCountryOriginSeoUrls($country_origin_id) {
        $country_origin_seo_url_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'country_origin_id=" . (int) $country_origin_id . "'");

        foreach ($query->rows as $result) {
            $country_origin_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
        }

        return $country_origin_seo_url_data;
    }

    public function getTotalCountryOrigins() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "country_origin");

        return $query->row['total'];
    }

}
