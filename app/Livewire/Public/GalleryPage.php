<?php

namespace App\Livewire\Public;

use App\Services\AggregatedGalleryService;
use App\Services\PublicWebsiteData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.frontbase')]
class GalleryPage extends Component
{
    /** @var array<int, array{key:string,url:string,caption:string,source_label:string}> */
    public array $galleryImages = [];

    public bool $galleryHasMore = true;

    public int $galleryBatchSize = 12;

    public bool $loadingGallery = false;

    /** @var array<int, array{key:string,url:string,caption:string,source_label:string}> */
    protected array $allGalleryImages = [];

    public function mount(AggregatedGalleryService $galleryService): void
    {
        $this->allGalleryImages = $galleryService->allImages(true)
            ->map(fn (array $item) => [
                'key' => $item['key'],
                'url' => $item['url'],
                'caption' => $item['caption'],
                'source_label' => $item['source_label'],
            ])
            ->all();

        $this->galleryImages = [];
        $this->galleryHasMore = count($this->allGalleryImages) > 0;
        $this->loadMoreGalleryImages();
    }

    public function loadMoreGalleryImages(): void
    {
        if (! $this->galleryHasMore || $this->loadingGallery) {
            return;
        }

        $this->loadingGallery = true;

        try {
            $offset = count($this->galleryImages);
            $batch = array_slice($this->allGalleryImages, $offset, $this->galleryBatchSize);

            foreach ($batch as $image) {
                $this->galleryImages[] = $image;
            }

            $this->galleryHasMore = ($offset + count($batch)) < count($this->allGalleryImages);
        } finally {
            $this->loadingGallery = false;
        }
    }

    public function render()
    {
        return view('frontend.gallery', PublicWebsiteData::galleryPageStatic());
    }
}
