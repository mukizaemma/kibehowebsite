<?php

namespace App\Http\Controllers;

use App\Services\Seo\RobotsBuilder;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    public function robots(RobotsBuilder $builder): Response
    {
        return response($builder->build(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function sitemap(SitemapBuilder $builder): Response
    {
        $xml = Cache::remember('seo.sitemap.xml', 3600, fn () => $builder->build());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
