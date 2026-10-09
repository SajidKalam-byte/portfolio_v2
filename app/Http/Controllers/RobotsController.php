<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $blockAll = config('portfolio.seo.block_non_production') && ! app()->isProduction();

        $lines = $blockAll
            ? ['# Non-production environment: indexing disabled.', 'User-agent: *', 'Disallow: /']
            : ['User-agent: *', 'Allow: /', '', 'Sitemap: '.route('sitemap')];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
