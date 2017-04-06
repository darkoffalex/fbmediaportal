<?php
/* @var $urls */
/* @var $host */
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://rusturkey.com/robots.txt</loc>
        <priority>0.5</priority>
    </url>
    <?php foreach($urls as $index => $url): ?>
        <url>
            <loc><?= $host.$url['url'];?></loc>
            <changefreq><?= $url['freq'];?></changefreq>
            <?php $dt = DateTime::createFromFormat('Y-m-d H:i:s',$url['updated_at']); ?>
            <lastmod><?= $dt->format('c'); ?></lastmod>
            <priority>0.5</priority>
        </url>
    <?php endforeach; ?>
</urlset>