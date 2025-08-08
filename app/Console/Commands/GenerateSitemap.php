<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Manufacturer;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Console\Command;
use XMLWriter;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate
                            {--chunk=20000 : Number of records per sitemap file (tune for performance)}';

    protected $description = 'Generate sitemap files (splitted) and sitemap-index.xml';

    public function handle()
    {
        $baseUrl = 'https://ekbcomplex.ru';
        $basePath = public_path('sitemaps');

        if (!is_dir($basePath)) {
            mkdir($basePath, 0755, true);
        }

        $this->info('Sitemap generation started...');
        $files = [];

        $chunkSize = (int) $this->option('chunk') ?: 20000;

        /**
         * 1) Static pages
         */
        $this->info('Writing static sitemap file...');
        $staticName = 'sitemap-static.xml';
        $this->writeXml(function (XMLWriter $xml) use ($baseUrl) {
            $now = now()->toAtomString();

            $this->writeUrl($xml, $baseUrl . '/', $now, 'daily', '1.0');

            $this->writeUrl($xml, $baseUrl . '/contacts', $now, 'monthly', '0.5');
            $this->writeUrl($xml, $baseUrl . '/about', $now, 'monthly', '0.5');

            $this->writeUrl($xml, $baseUrl . '/reviews', $now, 'weekly', '0.6');
            $this->writeUrl($xml, $baseUrl . '/marketplaces', $now, 'weekly', '0.6');
            $this->writeUrl($xml, $baseUrl . '/products', $now, 'weekly', '0.8');
            $this->writeUrl($xml, $baseUrl . '/manufacturers', $now, 'weekly', '0.6');
            $this->writeUrl($xml, $baseUrl . '/warehouses', $now, 'weekly', '0.6');
        }, $basePath . '/' . $staticName);
        $files[] = $staticName;

        /**
         * 2) Products (id)
         */
        $this->info('Writing products sitemaps...');
        $productChunk = 0;
        Product::select(['id', 'updated_at'])
            ->chunkById($chunkSize, function ($products) use (&$productChunk, &$files, $basePath, $baseUrl) {
                $productChunk++;
                $filename = "sitemap-products-{$productChunk}.xml";
                $path = $basePath . '/' . $filename;

                $this->writeXml(function (XMLWriter $xml) use ($products, $baseUrl) {
                    foreach ($products as $product) {
                        $loc = $baseUrl . '/products/' . $product->id;
                        $this->writeUrl($xml, $loc, optional($product->updated_at)->toAtomString(), 'weekly', '0.8');
                    }
                }, $path);

                $files[] = $filename;
                $this->info("  created: {$filename} ({$products->count()})");
            });

        /**
         * 3) Manufacturers (id)
         */
        $this->info('Writing manufacturers sitemap...');
        $manChunk = 0;
        Manufacturer::select(['id', 'updated_at'])
            ->chunkById(5000, function ($manufacturers) use (&$manChunk, &$files, $basePath, $baseUrl) {
                $manChunk++;
                $filename = "sitemap-manufacturers-{$manChunk}.xml";
                $path = $basePath . '/' . $filename;
                $this->writeXml(function (XMLWriter $xml) use ($manufacturers, $baseUrl) {
                    foreach ($manufacturers as $manufacturer) {
                        $loc = $baseUrl . '/manufacturers/' . $manufacturer->id;
                        $this->writeUrl($xml, $loc, optional($manufacturer->updated_at)->toAtomString(), 'weekly', '0.6');
                    }
                }, $path);
                $files[] = $filename;
                $this->info("  created: {$filename} ({$manufacturers->count()})");
            });

        /**
         * 4) Warehouses (id)
         */
        $this->info('Writing warehouses sitemap...');
        $whChunk = 0;
        Warehouse::select(['id', 'updated_at'])
            ->chunkById(5000, function ($warehouses) use (&$whChunk, &$files, $basePath, $baseUrl) {
                $whChunk++;
                $filename = "sitemap-warehouses-{$whChunk}.xml";
                $path = $basePath . '/' . $filename;
                $this->writeXml(function (XMLWriter $xml) use ($warehouses, $baseUrl) {
                    foreach ($warehouses as $warehouse) {
                        $loc = $baseUrl . '/warehouses/' . $warehouse->id;
                        $this->writeUrl($xml, $loc, optional($warehouse->updated_at)->toAtomString(), 'weekly', '0.6');
                    }
                }, $path);
                $files[] = $filename;
                $this->info("  created: {$filename} ({$warehouses->count()})");
            });

        /**
         * 5) Warehouse products (id)
         */
        $this->info('Writing warehouse products sitemaps...');
        $wpChunk = 0;
        WarehouseProduct::select(['id', 'updated_at'])
            ->chunkById(10000, function ($warehouseProducts) use (&$wpChunk, &$files, $basePath, $baseUrl) {
                $wpChunk++;
                $filename = "sitemap-warehouse-products-{$wpChunk}.xml";
                $path = $basePath . '/' . $filename;
                $this->writeXml(function (XMLWriter $xml) use ($warehouseProducts, $baseUrl) {
                    foreach ($warehouseProducts as $wp) {
                        $loc = $baseUrl . '/warehouses-products/' . $wp->id;
                        $this->writeUrl($xml, $loc, optional($wp->updated_at)->toAtomString(), 'weekly', '0.7');
                    }
                }, $path);
                $files[] = $filename;
                $this->info("  created: {$filename} ({$warehouseProducts->count()})");
            });

        /**
         * Sitemap index
         */
        $this->info('Writing sitemap-index.xml ...');
        $indexPath = $basePath . '/sitemap-index.xml';
        $xmlIndex = new XMLWriter();
        $xmlIndex->openMemory();
        $xmlIndex->startDocument('1.0', 'UTF-8');
        $xmlIndex->startElement('sitemapindex');
        $xmlIndex->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($files as $file) {
            $xmlIndex->startElement('sitemap');
            $xmlIndex->writeElement('loc', $baseUrl . '/sitemaps/' . $file);
            $xmlIndex->writeElement('lastmod', now()->toAtomString());
            $xmlIndex->endElement();
        }

        $xmlIndex->endElement();
        $xmlIndex->endDocument();
        file_put_contents($indexPath, $xmlIndex->outputMemory());

        $this->info('Sitemap generation completed. Files: ' . count($files));
        $this->info('Sitemap index: ' . $baseUrl . '/sitemaps/sitemap-index.xml');
    }

    protected function writeUrl(XMLWriter $xml, string $loc, ?string $lastmod = null, string $changefreq = 'weekly', ?string $priority = null)
    {
        $xml->startElement('url');
        $xml->writeElement('loc', $loc);
        if ($lastmod) {
            $xml->writeElement('lastmod', $lastmod);
        }
        if ($changefreq) {
            $xml->writeElement('changefreq', $changefreq);
        }
        if (!is_null($priority)) {
            $xml->writeElement('priority', $priority);
        }
        $xml->endElement();
    }

    protected function writeXml(callable $callback, string $filePath)
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $callback($xml);

        $xml->endElement();
        $xml->endDocument();

        file_put_contents($filePath, $xml->outputMemory());
    }
}
