<rss xmlns:smly="https://sendsmaily.net/schema/editor/rss.xsd" version="2.0">
<channel>
  <title>Store</title>
  <link><?php echo htmlspecialchars($store_url); ?></link>
  <description>Product Feed</description>
  <lastBuildDate><?php echo htmlspecialchars($last_build_date); ?></lastBuildDate>
  <?php foreach ($items as $item): ?>
    <item>
    <title><![CDATA[<?php echo $item['title']; ?>]]></title>
    <link><?php echo htmlspecialchars($item['link']); ?></link>
    <guid isPermalink="True"><?php echo htmlspecialchars($item['link']); ?></guid>
    <pubDate><?php echo htmlspecialchars($item['published_at']); ?></pubDate>
    <description><![CDATA[<?php echo $item['description']; ?>]]></description>
    <enclosure url="<?php echo $item['image']; ?>"/>
    <smly:price><?php echo htmlspecialchars($item['price']); ?></smly:price>
    <?php if (isset($item['discount'])): ?>
      <smly:old_price><?php echo htmlspecialchars($item['old_price']); ?></smly:old_price>
      <smly:discount><?php echo htmlspecialchars($item['discount']); ?>%</smly:discount>
    <?php endif; ?>
    </item>
  <?php endforeach; ?>
</channel>
</rss>
