<rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0">
<channel>
<title>Store</title>
<link><?php echo $store_url; ?></link>
<description>Product Feed</description>
<lastBuildDate><?php echo $last_build_date; ?></lastBuildDate>
<?php foreach($items as $item) { ?>
    <item>
    <title><![CDATA[ <?php echo html_entity_decode($item['title']); ?> ]]></title>
    <link><?php echo $item['link']; ?></link>
    <guid isPermalink="True"><?php echo $item['link']; ?></guid>
    <pubDate><?php echo $item['pubDate']; ?></pubDate>
    <description><![CDATA[ <?php echo html_entity_decode($item['description']); ?> ]]></description>
    <enclosure><?php echo $item['enclosure']; ?></enclosure>
    <smly:price><?php echo $item['price']; echo $currency; ?></smly:price>
    <?php if (isset($item['discount'])) : ?>
        <smly:old_price><?php echo $item['old_price']; echo $currency; ?></smly:old_price>
        <smly:discount><?php echo $item['discount']; ?> % </smly:discount>
    <?php endif; ?>
    </item>
<?php } ?>
</channel>
</rss>