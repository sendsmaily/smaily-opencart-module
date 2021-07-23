<?php

class ControllerExtensionSmailyForOpencartRss extends Controller {
	const RSS_DATE_FORMAT = 'r';

	public function index() {
		$this->load->model('extension/smailyforopencart/config');
		$config_model = $this->model_extension_smailyforopencart_config;

		// Ensure page content type is set with correct character set.
		$this->response->addHeader('Content-Type: text/xml; charset=utf-8');

		$data = array(
			'last_build_date' => date(self::RSS_DATE_FORMAT),
			'store_url' => (isset($this->request->server['HTTPS']) && in_array($this->request->server['HTTPS'], array('on', '1')))
				? $this->config->get('config_ssl')
				: $this->config->get('config_url'),
		);

		// Compile product filters.
		$filters = $this->collectFilters();
		$product_filters = array(
			'sort' => $filters['sort_by'],
			'order' => $filters['sort_order'],
			'start' => 0,
			'limit' => $filters['limit'],
		);

		if (!empty($filters['category'])) {
			$product_filters['filter_category_id'] = $filters['category'];
			$product_filters['filter_sub_category'] = true;
		}

		// Fetch products.
		$this->load->model('catalog/product');
		$product_model = $this->model_catalog_product;

		$this->load->model('tool/image');
		$image_model = $this->model_tool_image;

		$currency_code = $this->config->get('config_currency');

		$data['items'] = array();
		foreach ($product_model->getProducts($product_filters) as $product) {
			$item = array(
				'title' => trim(html_entity_decode($product['name'])),
				'link' => $this->url->link('product/product', array('product_id' => $product['product_id']), true),
				'published_at' => date(self::RSS_DATE_FORMAT, strtotime($product['date_available'])),
				'description' => trim(html_entity_decode($product['description'])),
				'image' => $image_model->resize($product['image'], 300, 300),
			);

			if (!empty($product['special']) && $product['special'] > 0) {
				$item['price'] = $this->currency->format($product['special'], $currency_code);
				$item['old_price'] = $this->currency->format($product['price'], $currency_code);
				$item['discount'] = round(100 - (($product['special'] / $product['price']) * 100), 2);
			}
			else {
				$item['price'] = $this->currency->format($product['price'], $currency_code);
			}

			$data['items'][] = $item;
		}

		$this->response->setOutput($this->load->view('extension/smailyforopencart/rss', $data));
	}

	protected function collectFilters() {
		$filters = array(
			'category' => 0,
			'limit' => 50,
			'sort_by' => 'p.date_added',
			'sort_order' => 'desc',
		);

		if (isset($this->request->get['category'])) {
			$filters['category'] = (int)$this->request->get['category'];
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
			$filters['limit'] = ($limit >= 1 && $limit < 250) ? $limit : 50;
		}

		$allowed_sort_by_options = array('pd.name', 'p.model', 'p.price', 'p.status', 'p.sort_order');
		if (isset($this->request->get['limit'])) {
			$sort_by = trim($this->request->get['sort_by']);
			$filters['sort_by'] = in_array($sort_by, $allowed_sort_by_options, true) ? $sort_by : 'p.date_added';
		}

		$allowed_sort_order_options = array('asc', 'desc');
		if (isset($this->request->get['sort_order'])) {
			$sort_order = trim($this->request->get['sort_order']);
			$filters['sort_order'] = in_array($sort_order, $allowed_sort_order_options, true) ? $sort_order : 'desc';
		}

		return $filters;
	}
}
