@props([
    'rooms',
    'setting' => null,
    'eyebrow' => 'Book your stay',
    'title' => 'Reserve with Booking.com',
    'lead' => 'All room reservations go through Booking.com for secure payment and instant availability. For questions, WhatsApp or email us.',
    'headingId' => 'booking-cta-heading',
    'idSuffix' => '',
    'showChildrenField' => false,
])

@php
    $setting = $setting ?? \App\Models\Setting::first();
@endphp

<section class="home-cta rts__section section__padding" aria-labelledby="{{ $headingId }}">
    <div class="container">
        <div class="home-cta__intro text-center wow fadeInUp">
            <p class="home-cta__eyebrow">{{ $eyebrow }}</p>
            <h2 id="{{ $headingId }}" class="home-cta__title section__title">{{ $title }}</h2>
            <p class="home-cta__lead font-sm">{{ $lead }}</p>
        </div>

        <div class="row g-4 g-xl-4 align-items-stretch">
            <div class="col-lg-6 wow fadeInLeft d-flex">
                @include('frontend.includes.cta-map-panel', ['setting' => $setting])
            </div>

            <div class="col-lg-6 wow fadeInRight d-flex">
                <div class="home-cta__panel home-cta__panel--form w-100">
                    <div class="home-cta__form-badge">
                        <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                        <span>Book &amp; enquire</span>
                    </div>
                    @include('frontend.includes.hotel-booking-channels', ['contextLabel' => ' (home CTA)'])
                </div>
            </div>
        </div>
    </div>
</section>
