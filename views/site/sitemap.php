<?php
/* @var $urls */
/* @var $host */
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="https://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach($urls as $index => $url): ?>
        <url>
            <loc><?= $host.$url['url'];?></loc>
            <changefreq><?= $url['freq'];?></changefreq>
            <lastmod><?= $url['updated_at']; ?></lastmod>
            <priority>0.5</priority>
        </url>
    <?php endforeach; ?>
</urlset>