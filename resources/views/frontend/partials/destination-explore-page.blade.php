@php
    $i18n = $translationPrefix ?? 'sanctuary';
    $page = $destinationPage ?? null;
    $heroCaption = $page->title ?? site_trans($i18n . '.title');
    $heroDescription = '';
    if ($pageHero && filled($pageHero->description)) {
        $heroDescription = $pageHero->description;
    } elseif ($page && filled(strip_tags((string) $page->description))) {
        $heroDescription = \Illuminate\Support\Str::words(strip_tags($page->description), 28, '...');
    } else {
        $heroDescription = site_trans($i18n . '.hero_lead');
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

    $activitiesEyebrow = site_trans($i18n . '.activities_eyebrow');
    $activitiesTitle = site_trans($i18n . '.activities_title');
    $ctaType = $ctaType ?? 'official';
@endphp

<div class="explore-sanctuary-hero rts__section page__hero__height page__hero__bg" style="background-image: url({{ $heroImage }});">
    <div class="explore-sanctuary-hero__overlay" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <div class="page__hero__content explore-sanctuary-hero__content">
                    <p class="explore-sanctuary-hero__eyebrow wow fadeInUp">{{ site_trans($i18n . '.eyebrow') }}</p>
                    <h1 class="wow fadeInUp" data-wow-delay="0.05s">{{ $heroCaption }}</h1>
                    <p class="wow fadeInUp font-sm mb-0" data-wow-delay="0.1s">{{ $heroDescription }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="explore-sanctuary-intro rts__section section__padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <h2 class="explore-sanctuary-section__title">{{ site_trans($i18n . '.about_title') }}</h2>
                <div class="explore-sanctuary-intro__prose content-richtext">
                    @if(filled($page->description ?? null))
                        {!! $page->description !!}
                    @else
                        <p class="text-muted mb-0">{{ site_trans($i18n . '.about_placeholder') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($destinationActivities) && $destinationActivities->isNotEmpty())
<section class="explore-sanctuary-events rts__section section__padding pt-0">
    <div class="container">
        <header class="explore-sanctuary-section__header text-center mb-4 mb-lg-5">
            <p class="explore-sanctuary-section__eyebrow">{{ $activitiesEyebrow }}</p>
            <h2 class="explore-sanctuary-section__title mb-0">{{ $activitiesTitle }}</h2>
        </header>

        <div class="row g-4">
            @foreach($destinationActivities as $activity)
                <div class="col-md-6 col-lg-4" wire:key="destination-activity-{{ $activity->id }}">
                    <article class="explore-sanctuary-event-card h-100">
                        @if($activity->image)
                            <div class="explore-sanctuary-event-card__media">
                                <img src="{{ asset('storage/' . $activity->image) }}" alt="{{ $activity->title }}" loading="lazy" decoding="async">
                            </div>
                        @endif
                        <div class="explore-sanctuary-event-card__body">
                            @if(!empty($activity->event_date))
                                <time class="explore-sanctuary-event-card__date" datetime="{{ $activity->event_date->format('Y-m-d') }}">
                                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                                    {{ $activity->event_date->format('M j, Y') }}
                                </time>
                            @endif
                            <h3 class="explore-sanctuary-event-card__title">{{ $activity->title }}</h3>
                            @if(filled($activity->description))
                                <p class="explore-sanctuary-event-card__text">{{ \Illuminate\Support\Str::limit(strip_tags($activity->description), 140) }}</p>
                            @endif
                            @if(filled($activity->external_url))
                                <a href="{{ $activity->external_url }}" class="explore-sanctuary-event-card__link" target="_blank" rel="noopener noreferrer">
                                    {{ site_trans($i18n . '.learn_more') }}
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

@if(isset($destinationGallery) && $destinationGallery->isNotEmpty())
<section class="explore-sanctuary-gallery rts__section section__padding">
    <div class="container">
        <header class="explore-sanctuary-section__header text-center mb-4 mb-lg-5">
            <p class="explore-sanctuary-section__eyebrow">{{ site_trans($i18n . '.gallery_eyebrow') }}</p>
            <h2 class="explore-sanctuary-section__title mb-2">{{ site_trans($i18n . '.gallery_title') }}</h2>
            <p class="explore-sanctuary-section__lead mx-auto">{{ site_trans($i18n . '.gallery_lead') }}</p>
        </header>

        <div class="row g-3 g-md-4">
            @foreach($destinationGallery->take(9) as $index => $image)
                <div class="col-6 col-md-4 col-lg-4" wire:key="destination-gallery-{{ $index }}">
                    <a href="{{ $image->url }}" class="explore-sanctuary-gallery__item d-block rounded-3 overflow-hidden" target="_blank" rel="noopener noreferrer" title="{{ $image->caption }}">
                        <img src="{{ $image->url }}" alt="{{ $image->caption ?: site_trans($i18n . '.gallery_alt') }}" loading="lazy" decoding="async">
                    </a>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-4 pt-2">
            <a wire:navigate href="{{ localized_route('gallery') }}" class="explore-sanctuary-btn explore-sanctuary-btn--outline">
                {{ site_trans($i18n . '.view_full_gallery') }}
            </a>
        </div>
    </div>
</section>
@endif

<section class="explore-sanctuary-cta rts__section section__padding pt-0 pb-5">
    <div class="container">
        <div class="explore-sanctuary-cta__card text-center">
            <div class="explore-sanctuary-cta__icon" aria-hidden="true">
                <i class="fa-solid {{ $ctaType === 'book' ? 'fa-route' : 'fa-church' }}"></i>
            </div>
            <h2 class="explore-sanctuary-cta__title">{{ site_trans($i18n . '.cta_title') }}</h2>
            <p class="explore-sanctuary-cta__lead">{{ site_trans($i18n . '.cta_lead') }}</p>
            @if($ctaType === 'book')
                @include('frontend.includes.reservation-link', [
                    'style' => 'fill',
                    'class' => 'explore-sanctuary-btn explore-sanctuary-btn--gold',
                    'label' => site_trans('nyaruguru.book_a_trip'),
                    'icon' => 'fa-solid fa-suitcase-rolling',
                ])
            @elseif(filled($officialWebsiteUrl ?? null))
                <a href="{{ $officialWebsiteUrl }}" target="_blank" rel="noopener noreferrer" class="explore-sanctuary-btn explore-sanctuary-btn--gold" data-no-spa-navigate>
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                    {{ site_trans('sanctuary.visit_official_site') }}
                </a>
            @endif
        </div>
    </div>
</section>
