<?php

namespace App\Services\Seo;

use App\Models\Blog;
use App\Models\Facility;
use App\Models\MeetingRoom;
use App\Models\Review;
use App\Models\Room;
use App\Models\TourActivity;
use App\Models\Trip;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class SitemapBuilder
{
    /** @var array<string, array{priority: string, changefreq: string}> */
    private const STATIC_PATHS = [
        '/' => ['priority' => '1.0', 'changefreq' => 'weekly'],
        '/about-us' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/our-services' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/our-rooms' => ['priority' => '0.9', 'changefreq' => 'weekly'],
        '/dining' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/our-team' => ['priority' => '0.6', 'changefreq' => 'monthly'],
        '/our-updates' => ['priority' => '0.7', 'changefreq' => 'weekly'],
        '/tours' => ['priority' => '0.8', 'changefreq' => 'weekly'],
        '/gallery' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/contact' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/promotions' => ['priority' => '0.7', 'changefreq' => 'weekly'],
        '/apartment' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/guesthouse' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/facilities' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/explore-kibeho' => ['priority' => '0.8', 'changefreq' => 'monthly'],
        '/discover-gikongoro-diocese' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/discover-nyaruguru' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/activities' => ['priority' => '0.8', 'changefreq' => 'weekly'],
        '/meetings-events' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/spa-wellness' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        '/terms-and-conditions' => ['priority' => '0.4', 'changefreq' => 'yearly'],
        '/reviews' => ['priority' => '0.6', 'changefreq' => 'weekly'],
        '/book-now' => ['priority' => '0.7', 'changefreq' => 'monthly'],
    ];

    /** @var list<array{loc: string, lastmod: ?string, changefreq: string, priority: string}> */
    private array $entries = [];

    public function build(): string
    {
        $this->entries = [];
        URL::forceRootUrl(config('app.url'));

        foreach (self::STATIC_PATHS as $path => $meta) {
            $this->addLocalizedPath($path, null, $meta['changefreq'], $meta['priority']);
        }

        if (Schema::hasTable('rooms')) {
            Room::query()
                ->where('status', 'Active')
                ->whereNotNull('slug')
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->each(fn ($room) => $this->addLocalizedPath(
                    '/our-rooms/'.$room->slug,
                    $room->updated_at,
                    'weekly',
                    '0.7'
                ));
        }

        if (Schema::hasTable('blogs')) {
            Blog::query()
                ->where('status', 'Published')
                ->whereNotNull('slug')
                ->orderByDesc('updated_at')
                ->get(['slug', 'updated_at'])
                ->each(fn ($post) => $this->addLocalizedPath(
                    '/our-updates/'.$post->slug,
                    $post->updated_at,
                    'monthly',
                    '0.6'
                ));
        }

        if (Schema::hasTable('trips')) {
            Trip::query()
                ->whereNotNull('slug')
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->each(fn ($trip) => $this->addLocalizedPath(
                    '/tour/'.$trip->slug,
                    $trip->updated_at,
                    'monthly',
                    '0.6'
                ));
        }

        if (Schema::hasTable('tour_activities')) {
            TourActivity::query()
                ->where('status', 'Active')
                ->whereNotNull('slug')
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->each(fn ($activity) => $this->addLocalizedPath(
                    '/activities/'.$activity->slug,
                    $activity->updated_at,
                    'monthly',
                    '0.6'
                ));
        }

        if (Schema::hasTable('facilities')) {
            Facility::query()
                ->where('status', 'Active')
                ->whereNotNull('slug')
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->each(fn ($facility) => $this->addLocalizedPath(
                    '/facilities/'.$facility->slug,
                    $facility->updated_at,
                    'monthly',
                    '0.6'
                ));
        }

        if (Schema::hasTable('meeting_rooms')) {
            MeetingRoom::query()
                ->whereNotNull('slug')
                ->orderBy('id')
                ->get(['slug', 'updated_at'])
                ->each(fn ($room) => $this->addLocalizedPath(
                    '/meetings-events/'.$room->slug,
                    $room->updated_at,
                    'monthly',
                    '0.5'
                ));
        }

        if (Schema::hasTable('reviews')) {
            Review::query()
                ->approved()
                ->orderByDesc('updated_at')
                ->get(['id', 'updated_at'])
                ->each(fn ($review) => $this->addLocalizedPath(
                    '/reviews/'.$review->id,
                    $review->updated_at,
                    'yearly',
                    '0.4'
                ));
        }

        return $this->toXml();
    }

    private function addLocalizedPath(
        string $path,
        mixed $lastmod,
        string $changefreq,
        string $priority
    ): void {
        $paths = [$path];

        if (translations_enabled()) {
            $paths[] = $path === '/' ? '/fr' : '/fr'.$path;
        }

        foreach ($paths as $localizedPath) {
            $this->entries[] = [
                'loc' => url($localizedPath),
                'lastmod' => $this->formatLastmod($lastmod),
                'changefreq' => $changefreq,
                'priority' => $priority,
            ];
        }
    }

    private function formatLastmod(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toAtomString();
        }

        return null;
    }

    private function toXml(): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($this->entries as $entry) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.htmlspecialchars($entry['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>';
            if ($entry['lastmod']) {
                $lines[] = '    <lastmod>'.$entry['lastmod'].'</lastmod>';
            }
            $lines[] = '    <changefreq>'.$entry['changefreq'].'</changefreq>';
            $lines[] = '    <priority>'.$entry['priority'].'</priority>';
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }
}
