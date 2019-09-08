<?php

class ControllerSmailyForOpencartRss extends Controller {

    public function index() {
        // Load models.
        $this->load->model('catalog/product');
        $this->load->model('tool/image');
        $this->load->model('localisation/currency');

        // Get params from url for filtering products.
        if (isset($this->request->get['category'])) {
            $category = $this->request->get['category'];
        } else {
            $category = null;
        }
        if (isset($this->request->get['limit'])) {
            $limit = (int) $this->request->get['limit'];
        } else {
            $limit = 50;
        }

        // Xml header.
        $this->response->addHeader('Content-Type: text/xml; charset=utf-8');
        // Store url.
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['store_url'] = $this->config->get('config_ssl');
        } else {
            $data['store_url'] = $this->config->get('config_url');
        }
        // Build date.
        $data['last_build_date'] = date('D, d M Y H:i:s');
        // Currency symbol.
        $data['currency'] = $this->session->data['currency'];
        // Filter for query.
        $filter = array(
            'sort' => 'p.date_added',
            'order' => 'DESC',
            'start' => 0,
            'limit' => $limit
        );
        // Add category if set.
        if ($category) {
            $filter['filter_category_id'] = $this->getCategoryIdByName($category);
            $filter['filter_sub_category'] = true;
        }
        // Get items based on filer.
        $products = $this->model_catalog_product->getProducts($filter);
        // Items array for template.
        $items = [];
        foreach ($products as $product) {
            $item = [];
            // Title.
            $item['title'] = $product['name'];
            // Link, guid
            $item['link'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);
            // Created date.
            $item['pubDate'] = $product['date_available'];
            // Description.
            $item['description'] = $product['description'];
            // Enclosure.
            $item['enclosure'] = $this->model_tool_image->resize($product['image'], 300, 300);
            // Price.
            $item['price'] = round($product['price'], 2);
            // Check if product is on sale.
            if (!empty($product['special']) && $product['special'] > 0) {
                $item['price'] = round($product['special'], 2);
                $item['old_price'] = round($product['price'], 2);
                $item['discount'] = round(100 - ($product['special'] / $product['price'] * 100), 2);
            }
            $items[] = $item;
        }
        $data['items'] = $items;
        // Return output.
        $this->response->setOutput($this->load->view('smailyforopencart/rss', $data));
    }

    /**
     * Returns category ID based on category name. Name must be url-encoded.
     * When no category found with that name, returns empty string.
     *
     * @param string $name Category name
     * @return string $id  Category id.
     */
    public function getCategoryIdByName($name) {
        $this->load->model('catalog/category');
        $id = '';
        // All categories.
        $categories = $this->model_catalog_category->getCategories();
        foreach ($categories as $category) {
            if ($category['name'] == $name) {
                // Get id if name matches.
                $id = $category['category_id'];
            }
        }
        return $id;
    }
}
