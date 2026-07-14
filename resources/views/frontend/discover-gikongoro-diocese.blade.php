<div class="public-livewire-page gikongoro-diocese-page">

@php
    $page = $diocesePage;
    $heroImage = $page->header_image
        ? asset('storage/' . $page->header_image)
        : ($pageHero && !empty($pageHero->background_image)
            ? asset('storage/' . $pageHero->background_image)
            : asset('storage/images/about/default.jpg'));
    $heroCaption = $page->title ?? site_trans('gikongoro.title');
    $mainImage = $page->profile_image
        ? asset('storage/' . $page->profile_image)
        : ($page->header_image ? asset('storage/' . $page->header_image) : null);
    $statsBg = $page->stats_background_image
        ? asset('storage/' . $page->stats_background_image)
        : $heroImage;
    $officialWebsiteUrl = filled(trim((string) ($page->official_website_url ?? '')))
        ? trim((string) $page->official_website_url)
        : null;
@endphp

<div class="gikongoro-hero rts__section page__hero__height page__hero__bg" style="background-image: url({{ $heroImage }});">
    <div class="gikongoro-hero__overlay" aria-hidden="true"></div>
    <div class="container position-relative">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 col-xl-8 text-center">
                <div class="page__hero__content gikongoro-hero__content">
                    <p class="gikongoro-hero__eyebrow wow fadeInUp">{{ site_trans('gikongoro.eyebrow') }}</p>
                    <h1 class="wow fadeInUp" data-wow-delay="0.05s">{{ $heroCaption }}</h1>
                    <p class="wow fadeInUp font-sm mb-0" data-wow-delay="0.1s">{{ site_trans('gikongoro.hero_lead') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="gikongoro-profile rts__section section__padding">
    <div class="container">
        <div class="row g-4 g-lg-5 align-items-center">
            <div class="col-lg-5 wow fadeInLeft">
                <div class="gikongoro-profile__media">
                    @if($mainImage)
                        <img src="{{ $mainImage }}" alt="{{ $page->title }}" class="gikongoro-profile__img" loading="eager" decoding="async" width="720" height="560">
                    @else
                        <div class="gikongoro-profile__img gikongoro-profile__img--placeholder" aria-hidden="true">
                            <i class="fa-solid fa-church"></i>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-7 wow fadeInRight">
                <div class="gikongoro-profile__content">
                    <h2 class="gikongoro-profile__title">{{ site_trans('gikongoro.about_title') }}</h2>
                    <div class="gikongoro-profile__prose content-richtext">
                        @if(filled($page->description))
                            {!! $page->description !!}
                        @else
                            <p class="text-muted mb-0">{{ site_trans('gikongoro.about_placeholder') }}</p>
                        @endif
                    </div>
                    @if($officialWebsiteUrl)
                        <a href="{{ $officialWebsiteUrl }}"
                           class="theme-btn btn-style fill gikongoro-profile__link"
                           target="_blank"
                           rel="noopener noreferrer"
                           data-no-spa-navigate>
                            <span>{{ site_trans('gikongoro.visit_website') }}</span>
                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

@if(isset($dioceseStats) && $dioceseStats->isNotEmpty())
<section class="gikongoro-stats rts__section" style="background-image: url({{ $statsBg }});">
    <div class="gikongoro-stats__overlay" aria-hidden="true"></div>
    <div class="container position-relative">
        <header class="gikongoro-stats__header text-center">
            <p class="gikongoro-stats__eyebrow">{{ site_trans('gikongoro.stats_eyebrow') }}</p>
            <h2 class="gikongoro-stats__title">{{ site_trans('gikongoro.stats_title') }}</h2>
        </header>
        <div class="row g-3 g-md-4 justify-content-center">
            @foreach($dioceseStats as $stat)
                <div class="col-6 col-md-4 col-lg-3" wire:key="diocese-stat-{{ $stat->id }}">
                    <article class="gikongoro-stat-card h-100 text-center">
                        @if(filled($stat->icon))
                            <span class="gikongoro-stat-card__icon" aria-hidden="true"><i class="{{ $stat->icon }}"></i></span>
                        @endif
                        <p class="gikongoro-stat-card__value">{{ $stat->value ?? '—' }}</p>
                        <p class="gikongoro-stat-card__label">{{ $stat->label }}</p>
                    </article>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

</div>
