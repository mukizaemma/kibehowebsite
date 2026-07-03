<?php

namespace App\Services\Seo;

class RobotsBuilder
{
    /** @var list<string> */
    private const DISALLOW_PATHS = [
        '/content-management/',
        '/setting',
        '/account/',
        '/admin/',
        '/adminLogin',
        '/user/',
        '/login',
        '/Signin',
        '/getSignup',
        '/verify-email/',
        '/forgot-password',
        '/reset-password/',
        '/handover',
        '/livewire/',
        '/storage/',
        '/locale/',
    ];

    public function build(): string
    {
        $lines = [
            'User-agent: *',
        ];

        foreach (self::DISALLOW_PATHS as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return implode("\n", $lines)."\n";
    }
}
