<div class="livewire-home-page">
<!-- Animated Slideshow Section -->
@include('frontend.includes.slides')
<!-- Slideshow End -->

<!-- About Us Section -->
@php
    $homeAboutImage = asset('storage/images/about/default.jpg');
    if ($about) {
        foreach (['image1', 'storyImage', 'image2'] as $aboutImageField) {
            if (filled($about->{$aboutImageField})) {
                $path = $about->{$aboutImageField};
                if (str_contains($path, '/') || str_starts_with($path, 'abouts')) {
                    $homeAboutImage = asset('storage/' . $path);
                } else {
                    $homeAboutImage = asset('storage/images/about/' . $path);
                }
                break;
            }
        }
    }
    $homeAboutTitle = filled($about?->title) ? $about->title : ($setting?->company ?? 'About our hotel');
    $homeAboutExcerpt = filled($about?->founderDescription)
        ? Str::words(strip_tags($about->founderDescription), 48, '…')
        : 'A peaceful place where faith, hospitality, and comfort welcome every guest in the heart of Kibeho.';
@endphp
<section class="rts__section home-about-split section__padding" id="home-about" aria-labelledby="home-about-heading">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6 wow fadeInLeft" data-wow-delay=".15s">
                <div class="home-about-split__media">
                    <img src="{{ $homeAboutImage }}"
                         alt="{{ $homeAboutTitle }}"
                         loading="lazy"
                         decoding="async"
                         width="720"
                         height="540"
                         class="home-about-split__img">
                </div>
            </div>
            <div class="col-lg-6 wow fadeInRight" data-wow-delay=".25s">
                <div class="home-about-split__content">
                    <h2 id="home-about-heading" class="home-about-split__title">
                        {{ $homeAboutTitle }}@if(filled($about?->subTitle)) <span class="home-about-split__title-accent">{{ $about->subTitle }}</span>@endif
                    </h2>
                    <p class="home-about-split__text">{{ $homeAboutExcerpt }}</p>
                    <a wire:navigate href="{{ localized_route('about') }}" class="theme-btn btn-style fill home-about-split__btn">
                        <span>{{ site_trans('buttons.read_more') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- About Us End -->

<!-- Hotel Rooms Section -->
<div class="rts__section section__padding home-rooms-section" style="background: #f5f6f8;">
    <div class="container">
        <div class="row justify-content-center text-center mb-50 mb-lg-60">
            <div class="col-lg-8 wow fadeInUp">
                <div class="section__topbar">
                    <h2 class="section__title">{{ site_trans('home.rooms_title') }}</h2>
                    <p class="font-sm mb-0">{{ site_trans('home.rooms_lead') }}</p>
                </div>
            </div>
        </div>

        @if($rooms->count() > 0)
        <div class="row g-4 g-lg-4 justify-content-center wow fadeInUp" data-wow-delay=".1s">
            @foreach($rooms->take(4) as $room)
            <div class="col-12 col-md-6">
                <article class="home-room-card">
                    <a wire:navigate href="{{ localized_localized_route('room', ['slug' => $room->slug]) }}" class="home-room-card__media">
                        <img src="{{ asset('storage/' . ($room->cover_image ?? 'rooms/default.jpg')) }}"
                            alt="{{ $room->title }}"
                            loading="lazy"
                            width="800"
                            height="480">
                    </a>
                    <div class="home-room-card__body">
                        <div class="home-room-card__head">
                            <a wire:navigate href="{{ localized_localized_route('room', ['slug' => $room->slug]) }}" class="home-room-card__title">{{ $room->title }}</a>
                            <div class="home-room-card__price">
                                <span class="home-room-card__price-from">{{ site_trans('home.starts_from') }}</span>
                                <div class="home-room-card__price-line">
                                    <span class="home-room-card__price-amount">{{ hotel_price($room->price ?? 0, $setting) }}</span>
                                    <span class="home-room-card__price-unit">{{ site_trans('home.per_night') }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="home-room-card__excerpt">
                            {!! Str::words(strip_tags($room->description ?? ''), 28, '…') !!}
                        </p>
            <div class="home-room-card__actions">
                <a wire:navigate href="{{ localized_localized_route('room', ['slug' => $room->slug]) }}" class="theme-btn btn-style sm-btn border">
                    <span>{{ site_trans('buttons.view_details') }}</span>
                </a>
                @include('frontend.includes.reservation-link', ['style' => 'sm-btn fill'])
            </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>

        <div class="row mt-45 mt-lg-50">
            <div class="col-12 text-center">
                <a wire:navigate href="{{ localized_route('rooms') }}" class="home-rooms-view-all theme-btn btn-style border">
                    <span>{{ site_trans('home.view_all_rooms') }}</span>
                </a>
            </div>
        </div>
        @else
        <p class="text-center text-muted mb-0">{{ site_trans('home.rooms_coming_soon') }}</p>
        @endif
    </div>
</div>
<!-- Hotel Rooms End -->

<!-- Our Services Section (facilities) — matches Our Hotel Rooms layout -->
<div class="rts__section section__padding home-services-section" style="background: #f5f6f8;">
    <div class="container">
        <div class="row justify-content-center text-center mb-50 mb-lg-60">
            <div class="col-lg-8 wow fadeInUp">
                <div class="section__topbar">
                    <h2 class="section__title">{{ site_trans('home.services_title') }}</h2>
                    <p class="font-sm mb-0">{{ site_trans('home.services_lead') }}</p>
                </div>
            </div>
        </div>

        @if($homeFacilities->count() > 0)
        <div class="row g-4 g-lg-4 justify-content-center wow fadeInUp" data-wow-delay=".1s">
            @foreach($homeFacilities as $facility)
            <div class="col-12 col-md-6">
                <article class="home-room-card">
                    <a wire:navigate href="{{ localized_localized_route('facility', ['slug' => $facility->slug]) }}" class="home-room-card__media">
                        <img src="{{ asset('storage/' . ($facility->cover_image ?? 'facilities/default.jpg')) }}"
                            alt="{{ $facility->title }}"
                            loading="lazy"
                            width="800"
                            height="480">
                    </a>
                    <div class="home-room-card__body">
                        <div class="home-room-card__head home-room-card__head--title-only">
                            <a wire:navigate href="{{ localized_localized_route('facility', ['slug' => $facility->slug]) }}" class="home-room-card__title">{{ $facility->title }}</a>
                        </div>
                        <p class="home-room-card__excerpt">
                            {!! Str::words(strip_tags($facility->description ?? ''), 28, '…') !!}
                        </p>
                        <div class="home-room-card__actions">
                            <a wire:navigate href="{{ localized_localized_route('facility', ['slug' => $facility->slug]) }}" class="theme-btn btn-style sm-btn border">
                                <span>{{ site_trans('buttons.view_details') }}</span>
                            </a>
                            <a wire:navigate href="{{ localized_localized_route('contact') }}" class="theme-btn btn-style sm-btn fill">
                                <span>{{ site_trans('buttons.contact_us') }}</span>
                            </a>
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>

        <div class="row mt-45 mt-lg-50">
            <div class="col-12 text-center">
                <a wire:navigate href="{{ localized_localized_route('our-services') }}" class="home-rooms-view-all theme-btn btn-style border">
                    <span>{{ site_trans('home.view_all_services') }}</span>
                </a>
            </div>
        </div>
        @else
        <p class="text-center text-muted mb-0">{{ site_trans('home.services_coming_soon') }}</p>
        @endif
    </div>
</div>
<!-- Our Services End -->

@include('layouts.includes.why-choose-us')

<!-- Updates/Blogs Section -->
@if($blogs && $blogs->count() > 0)
<div class="rts__section section__padding" style="background: #f9f9f9;">
    <div class="container">
        <div class="row position-relative justify-content-center text-center mb-60">
            <div class="col-lg-6 wow fadeInUp">
                <div class="section__topbar">
                    <h2 class="section__title">{{ site_trans('home.updates_title') }}</h2>
                    <p class="font-sm">{{ site_trans('home.updates_lead') }}</p>
                </div>
            </div>
        </div>
        <div class="row g-30">
            @foreach($blogs as $blog)
            <div class="col-xl-4 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".{{ $loop->index * 2 }}s">
                <div class="blog__card" style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                    <div style="height: 250px; overflow: hidden;">
                        <a wire:navigate href="{{ localized_localized_route('update', ['slug' => $blog->slug]) }}">
                            <img src="{{ asset('storage/images/blogs/' . ($blog->image ?? 'default.jpg')) }}" 
                                 alt="{{ $blog->title }}" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        </a>
                    </div>
                    <div style="padding: 25px;">
                        <a wire:navigate href="{{ localized_localized_route('update', ['slug' => $blog->slug]) }}" class="h5" style="display: block; margin-bottom: 15px; color: #222;">
                            {{ $blog->title }}
                        </a>
                        <p class="font-sm" style="color: #666; margin-bottom: 15px;">
                            {!! Str::words(strip_tags($blog->body ?? ''), 25, '...') !!}
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span class="font-sm" style="color: #999;">
                                {{ $blog->created_at->format('M d, Y') }}
                            </span>
                            <a wire:navigate href="{{ localized_localized_route('update', ['slug' => $blog->slug]) }}" class="theme-btn btn-style sm-btn border">
                                <span>{{ site_trans('buttons.read_more') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="row mt-40">
            <div class="col-12 text-center">
                <a wire:navigate href="{{ localized_route('updates') }}" class="theme-btn btn-style fill">
                    <span>{{ site_trans('home.view_all_updates') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endif
<!-- Updates End -->

@if(isset($gallery) && $gallery->count() > 0)
<section class="rts__section section__padding home-gallery-preview" aria-labelledby="home-gallery-heading">
    <div class="container">
        <div class="row justify-content-center text-center mb-45 mb-lg-50">
            <div class="col-lg-8 wow fadeInUp">
                <div class="section__topbar">
                    <p class="home-gallery-preview__eyebrow">{{ site_trans('home.gallery_eyebrow') }}</p>
                    <h2 id="home-gallery-heading" class="section__title">{{ site_trans('home.gallery_title') }}</h2>
                    <p class="font-sm mb-0">{{ site_trans('home.gallery_lead') }}</p>
                </div>
            </div>
        </div>
        <div class="row g-3 g-lg-4 justify-content-center wow fadeInUp" data-wow-delay=".1s">
            @foreach($gallery->take(3) as $image)
            <div class="col-md-4">
                <a wire:navigate href="{{ localized_localized_route('gallery') }}" class="home-gallery-preview__item d-block rounded-3 overflow-hidden">
                    <img src="{{ $image['url'] }}"
                         alt="{{ $image['caption'] ?: site_trans('home.gallery_image_alt') }}"
                         loading="lazy"
                         decoding="async"
                         width="640"
                         height="480">
                    @if(!empty($image['caption']))
                        <span class="home-gallery-preview__caption">{{ $image['caption'] }}</span>
                    @endif
                </a>
            </div>
            @endforeach
        </div>
        <div class="row mt-40">
            <div class="col-12 text-center">
                <a wire:navigate href="{{ localized_localized_route('gallery') }}" class="theme-btn btn-style border">
                    <span>{{ site_trans('home.view_full_gallery') }}</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

<x-booking-cta :rooms="$rooms" heading-id="home-cta-heading" :show-children-field="true" />
<!-- Call to Action End -->
</div>
