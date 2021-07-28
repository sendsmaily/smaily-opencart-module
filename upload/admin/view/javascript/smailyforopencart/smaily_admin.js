(function ($) {
	$(document).ready(function () {
		$("#smaily-rss-options").change(function () {
			var $category = $('#rss-category'),
				$limit = $('#rss-limit'),
				$sort_by = $('#rss-sort-by'),
				$sort_order = $('#rss-sort-order');

			$('#rss-url').html(smailyforopencart_settings.rss_base_url + '&' + $.param({
				'category': $category.val(),
				'limit': $limit.val(),
				'sort_by': $sort_by.val(),
				'sort_order': $sort_order.val(),
			}));
		});
	});
})(jQuery);
