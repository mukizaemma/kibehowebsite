@props([
    'rooms',
    'setting' => null,
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'headingId' => 'booking-cta-heading',
    'idSuffix' => '',
    'showChildrenField' => false,
])

@php
    $setting = $setting ?? \App\Models\Setting::first();
    $eyebrow = $eyebrow ?? site_trans('booking.eyebrow');
    $title = $title ?? site_trans('booking.title');
    $lead = $lead ?? site_trans('booking.lead');
@endphp

<section class="home-cta rts__section section__padding" aria-labelledby="{{ $headingId }}">
    <div class="container">
        <div class="home-cta__intro text-center wow fadeInUp">
            @if(filled($eyebrow))
            <p class="home-cta__eyebrow">{{ $eyebrow }}</p>
            @endif
            <h2 id="{{ $headingId }}" class="home-cta__title section__title">{{ $title }}</h2>
            <p class="home-cta__lead font-sm">{{ $lead }}</p>
        </div>

        <div class="row g-4 g-xl-4 align-items-stretch">
            <div class="col-lg-6 wow fadeInLeft d-flex">
                @include('frontend.includes.cta-map-panel', ['setting' => $setting])
            </div>

            <div class="col-lg-6 wow fadeInRight d-flex">
                <div class="home-cta__panel home-cta__panel--form w-100">
                    @include('frontend.includes.hotel-booking-cta-panel')
                </div>
            </div>
        </div>
    </div>
</section>
