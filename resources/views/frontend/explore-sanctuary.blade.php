<div class="public-livewire-page explore-sanctuary-page">

@php
    $page = $kibehoPage ?? $facility ?? null;
    $heroCaption = $page->title ?? site_trans('sanctuary.title');
    $heroDescription = '';
    if ($pageHero && filled($pageHero->description)) {
        $heroDescription = $pageHero->description;
    } elseif ($page && filled(strip_tags((string) $page->description))) {
        $heroDescription = \Illuminate\Support\Str::words(strip_tags($page->description), 28, '...');
    } else {
        $heroDescription = site_trans('sanctuary.hero_lead');
    }

    $heroImage = '';
    if ($pageHero && !empty($pageHero->background_image)) {
        $heroImage = asset('storage/' . $pageHero->background_image);
        $heroCaption = $pageHero->caption ?: $heroCaption;
    } elseif ($page && $page->cover_image) {
        $heroImage = asset('storage/' . $page->cover_image);
    } elseif ($about && $about->image2) {
        $heroImage = (strpos($about->image2, '/') !== false || strpos($about->image2, 'abouts') === 0)
            ? asset('storage/' . $about->image2)
            : asset('storage/images/about/' . $about->image2);
    } else {
        $heroImage = asset('storage/images/about/default.jpg');
    }
@endphp

<div class="explore-sanctuary-hero rts__section page__hero__height page__hero__bg" style="background-image: url({{ $heroImage }});">
    <div class="explore-sanctuary-hero__overlay" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <div class="page__hero__content explore-sanctuary-hero__content">
                    <p class="explore-sanctuary-hero__eyebrow wow fadeInUp">{{ site_trans('sanctuary.eyebrow') }}</p>
                    <h1 class="wow fadeInUp" data-wow-delay="0.05s">{{ $heroCaption }}</h1>
                    <p class="wow fadeInUp font-sm mb-0" data-wow-delay="0.1s">{{ $heroDescription }}</p>
                    @if(filled($officialWebsiteUrl ?? null))
                        <div class="wow fadeInUp explore-sanctuary-hero__cta" data-wow-delay="0.15s">
                            <a href="{{ $officialWebsiteUrl }}" target="_blank" rel="noopener noreferrer" class="explore-sanctuary-btn explore-sanctuary-btn--gold">
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                {{ site_trans('sanctuary.visit_official_site') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<section class="explore-sanctuary-intro rts__section section__padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <h2 class="explore-sanctuary-section__title">{{ site_trans('sanctuary.about_title') }}</h2>
                <div class="explore-sanctuary-intro__prose content-richtext">
                    @if(filled($page->description ?? null))
                        {!! $page->description !!}
                    @else
                        <p class="text-muted mb-0">{{ site_trans('sanctuary.about_placeholder') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($sanctuaryEvents) && $sanctuaryEvents->isNotEmpty())
<section class="explore-sanctuary-events rts__section section__padding pt-0">
    <div class="container">
        <header class="explore-sanctuary-section__header text-center mb-4 mb-lg-5">
            <p class="explore-sanctuary-section__eyebrow">{{ site_trans('sanctuary.events_eyebrow') }}</p>
            <h2 class="explore-sanctuary-section__title mb-0">{{ site_trans('sanctuary.events_title') }}</h2>
        </header>

        <div class="row g-4">
            @foreach($sanctuaryEvents as $event)
                <div class="col-md-6 col-lg-4" wire:key="sanctuary-event-{{ $event->id }}">
                    <article class="explore-sanctuary-event-card h-100">
                        @if($event->image)
                            <div class="explore-sanctuary-event-card__media">
                                <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" loading="lazy" decoding="async">
                            </div>
                        @endif
                        <div class="explore-sanctuary-event-card__body">
                            @if($event->event_date)
                                <time class="explore-sanctuary-event-card__date" datetime="{{ $event->event_date->format('Y-m-d') }}">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    {{ $event->event_date->format('M j, Y') }}
                                </time>
                            @endif
                            <h3 class="explore-sanctuary-event-card__title">{{ $event->title }}</h3>
                            @if(filled($event->description))
                                <p class="explore-sanctuary-event-card__text">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 140) }}</p>
                            @endif
                            @if(filled($event->external_url))
                                <a href="{{ $event->external_url }}" class="explore-sanctuary-event-card__link" target="_blank" rel="noopener noreferrer">
                                    {{ site_trans('sanctuary.learn_more') }}
                                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            @endif
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(isset($sanctuaryGallery) && $sanctuaryGallery->isNotEmpty())
<section class="explore-sanctuary-gallery rts__section section__padding">
    <div class="container">
        <header class="explore-sanctuary-section__header text-center mb-4 mb-lg-5">
            <p class="explore-sanctuary-section__eyebrow">{{ site_trans('sanctuary.gallery_eyebrow') }}</p>
            <h2 class="explore-sanctuary-section__title mb-2">{{ site_trans('sanctuary.gallery_title') }}</h2>
            <p class="explore-sanctuary-section__lead mx-auto">{{ site_trans('sanctuary.gallery_lead') }}</p>
        </header>

        <div class="row g-3 g-md-4">
            @foreach($sanctuaryGallery->take(9) as $index => $image)
                <div class="col-6 col-md-4 col-lg-4" wire:key="sanctuary-gallery-{{ $index }}">
                    <a href="{{ $image->url }}" class="explore-sanctuary-gallery__item d-block rounded-3 overflow-hidden" target="_blank" rel="noopener noreferrer" title="{{ $image->caption }}">
                        <img src="{{ $image->url }}" alt="{{ $image->caption ?: site_trans('sanctuary.gallery_alt') }}" loading="lazy" decoding="async">
                    </a>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4 pt-2">
            <a wire:navigate href="{{ localized_route('gallery') }}" class="explore-sanctuary-btn explore-sanctuary-btn--outline">
                {{ site_trans('sanctuary.view_full_gallery') }}
            </a>
        </div>
    </div>
</section>
@endif

@if(filled($officialWebsiteUrl ?? null))
<section class="explore-sanctuary-cta rts__section section__padding pt-0 pb-5">
    <div class="container">
        <div class="explore-sanctuary-cta__card text-center">
            <div class="explore-sanctuary-cta__icon" aria-hidden="true">
                <i class="fa-solid fa-church"></i>
            </div>
            <h2 class="explore-sanctuary-cta__title">{{ site_trans('sanctuary.cta_title') }}</h2>
            <p class="explore-sanctuary-cta__lead">{{ site_trans('sanctuary.cta_lead') }}</p>
            <a href="{{ $officialWebsiteUrl }}" target="_blank" rel="noopener noreferrer" class="explore-sanctuary-btn explore-sanctuary-btn--gold">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                {{ site_trans('sanctuary.visit_official_site') }}
            </a>
        </div>
    </div>
</section>
@endif

</div>
