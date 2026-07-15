<div class="public-livewire-page destination-activity-page">
@php
    $i18n = $translationPrefix ?? 'sanctuary';
    $heroImage = filled($activity->image)
        ? asset('storage/' . $activity->image)
        : (($about && $about->image2)
            ? (str_contains($about->image2, '/') || str_starts_with($about->image2, 'abouts')
                ? asset('storage/' . $about->image2)
                : asset('storage/images/about/' . $about->image2))
            : asset('storage/images/about/default.jpg'));
@endphp

<div class="rts__section page__hero__height page__hero__bg" style="background-image: url({{ $heroImage }});">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <div class="page__hero__content">
                    <p class="explore-sanctuary-hero__eyebrow wow fadeInUp">{{ site_trans($i18n . '.activities_eyebrow') }}</p>
                    <h1 class="wow fadeInUp" data-wow-delay="0.05s">{{ $activity->title }}</h1>
                    @if(!empty($activity->event_date))
                        <p class="wow fadeInUp font-sm mb-0" data-wow-delay="0.1s">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            {{ $activity->event_date->format('F j, Y') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<section class="rts__section section__padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="content-richtext destination-activity-page__prose">
                    @if(filled($activity->description))
                        @if(str_contains($activity->description, '<'))
                            {!! $activity->description !!}
                        @else
                            {!! nl2br(e($activity->description)) !!}
                        @endif
                    @endif
                </div>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a wire:navigate href="{{ localized_route($backRoute) }}" class="theme-btn btn-style border">
                        <span>{{ site_trans($i18n . '.back_to_list') }}</span>
                    </a>
                    @if(filled($activity->external_url))
                        <a href="{{ $activity->external_url }}"
                           class="theme-btn btn-style fill"
                           target="_blank"
                           rel="noopener noreferrer"
                           data-no-spa-navigate>
                            <span>{{ site_trans($i18n . '.learn_more') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($images) && $images->isNotEmpty())
<section class="rts__section section__padding pt-0">
    <div class="container">
        <header class="text-center mb-4 mb-lg-5">
            <h2 class="explore-sanctuary-section__title mb-0">{{ site_trans($i18n . '.activity_gallery_title') }}</h2>
        </header>
        <div class="row g-3 g-md-4">
            @foreach($images as $img)
                <div class="col-6 col-md-4">
                    <a href="{{ asset('storage/' . $img->image) }}" class="explore-sanctuary-gallery__item d-block overflow-hidden" target="_blank" rel="noopener noreferrer" title="{{ $img->caption ?: $activity->title }}">
                        <img src="{{ asset('storage/' . $img->image) }}" alt="{{ $img->caption ?: $activity->title }}" loading="lazy" decoding="async">
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if(isset($relatedActivities) && $relatedActivities->isNotEmpty())
<section class="rts__section section__padding pt-0">
    <div class="container">
        <header class="text-center mb-4">
            <h2 class="explore-sanctuary-section__title mb-0">{{ site_trans($i18n . '.related_activities') }}</h2>
        </header>
        <div class="row g-4">
            @foreach($relatedActivities as $related)
                <div class="col-md-4">
                    <article class="explore-sanctuary-event-card h-100">
                        @if($related->image)
                            <a wire:navigate href="{{ localized_route($backRoute === 'explore-kibeho' ? 'explore-kibeho.activity' : 'discover-nyaruguru.activity', ['slug' => $related->slug]) }}" class="explore-sanctuary-event-card__media">
                                <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->title }}" loading="lazy">
                            </a>
                        @endif
                        <div class="explore-sanctuary-event-card__body">
                            <h3 class="explore-sanctuary-event-card__title">{{ $related->title }}</h3>
                            <a wire:navigate
                               href="{{ localized_route($backRoute === 'explore-kibeho' ? 'explore-kibeho.activity' : 'discover-nyaruguru.activity', ['slug' => $related->slug]) }}"
                               class="explore-sanctuary-event-card__link">
                                {{ site_trans($i18n . '.view_more') }}
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
</div>
