<div class="public-livewire-page">

@php
    $heroImage = '';
    $heroCaption = 'Hotel Rooms';
    $heroDescription = 'Comfortable rooms for pilgrims and guests in the heart of Kibeho.';
    if ($pageHero && !empty($pageHero->background_image)) {
        $heroImage = asset('storage/' . $pageHero->background_image);
        $heroCaption = $pageHero->caption ?? $heroCaption;
        $heroDescription = $pageHero->description ?? $heroDescription;
    } elseif ($about && $about?->image2) {
        if (strpos($about?->image2, '/') !== false || strpos($about?->image2, 'abouts') === 0) {
            $heroImage = asset('storage/' . $about?->image2);
        } else {
            $heroImage = asset('storage/images/about/' . $about?->image2);
        }
    } else {
        $heroImage = asset('storage/images/about/default.jpg');
    }
@endphp

<!-- Page header -->
<div class="rts__section page__hero__height page__hero__bg" style="background-image: url({{ $heroImage }}); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-12">
                <div class="page__hero__content">
                    <h1 class="wow fadeInUp">{{ $heroCaption }}</h1>
                    <p class="wow fadeInUp font-sm">{{ $heroDescription }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rooms listing -->
<div class="rts__section section__padding pt-50 pb-120">
    <div class="container">
        <div class="row g-4">
            @forelse($rooms as $room)
                @include('frontend.includes.room-listing-card', ['room' => $room, 'colClass' => 'col-lg-6 col-md-6'])
            @empty
            <div class="col-12 text-center py-5 text-muted">
                <p class="mb-0">No rooms available at the moment.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
</div>
