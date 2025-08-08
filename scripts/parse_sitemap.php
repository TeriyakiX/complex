<?php

// Путь к папке с sitemap (предположим, что скрипт лежит в scripts/)
$dir = __DIR__ . '/../public/sitemaps';

if (!is_dir($dir)) {
    die("Папка sitemap не найдена: $dir\n");
}

$files = glob($dir . '/*.xml.gz');

if (!$files) {
    die("Файлы sitemap не найдены в: $dir\n");
}

foreach ($files as $file) {
    echo "Файл: " . basename($file) . "\n";

    $content = gzopen($file, 'r');
    $xmlString = '';

    while (!gzeof($content)) {
        $xmlString .= gzread($content, 4096);
    }

    gzclose($content);

    $xml = simplexml_load_string($xmlString);

    if ($xml === false) {
        echo "  Ошибка чтения XML\n";
        continue;
    }

    // Если это sitemap-index.xml
    if ($xml->getName() == 'sitemapindex') {
        foreach ($xml->sitemap as $sitemap) {
            echo "  " . (string)$sitemap->loc . "\n";
        }
    }

    // Если это обычный urlset
    if ($xml->getName() == 'urlset') {
        foreach ($xml->url as $url) {
            echo "  " . (string)$url->loc . "\n";
        }
    }

    echo "\n";
}
