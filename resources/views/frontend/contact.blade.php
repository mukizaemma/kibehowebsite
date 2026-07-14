<div class="public-livewire-page">

@include('frontend.includes.page-hero-banner', [
    'defaultCaption' => 'Contact Us',
    'defaultDescription' => "We'd love to help you get a comfortable stay",
    'showHeroContacts' => true,
])

@php
    $setting = $setting ?? \App\Models\Setting::first();
@endphp

<!-- Contact Section (phone, email, social — see site header) -->
<div class="rts__section section__padding">
    <div class="container">
        <div class="row g-4 g-xl-4 align-items-stretch">
            <div class="col-lg-7 col-xl-7">
                <div class="home-cta__panel home-cta__panel--form site-form-panel h-100" style="padding: 2rem; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                    <h3 class="mb-3 section__title section__title--compact">{{ site_trans('home.close_booking_title') }}</h3>
                    @include('frontend.includes.hotel-booking-cta-panel')
                </div>
            </div>

            <div class="col-lg-5 col-xl-5 mt-4 mt-lg-0 d-flex align-items-stretch">
                <div class="home-cta__panel home-cta__panel--map w-100 h-100">
                    <div class="home-cta__map-head">
                        <span class="home-cta__map-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                        <div class="home-cta__map-head-text">
                            <span class="home-cta__map-label">Visit us</span>
                            <span id="contact-map-heading" class="home-cta__map-title">{{ $setting?->company ?? config('app.name') }}</span>
                            @if(filled($setting?->address))
                                <span class="home-cta__map-address">{{ $setting->address }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="home-cta__map-frame contact-page-map-frame">
                        @if(!empty($setting?->google_map_embed))
                            {!! $setting->google_map_embed !!}
                        @else
                            @php
                                $hc = \App\Models\HotelContact::first();
                                $latitude = $hc?->latitude ?? '-1.9441';
                                $longitude = $hc?->longitude ?? '30.0619';
                            @endphp
                            <iframe
                                title="Hotel location on Google Maps"
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3987.497311415315!2d{{ $longitude }}!3d{{ $latitude }}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z{{ $latitude }},{{ $longitude }}!5e0!3m2!1sen!2srw!4v1234567890"
                                width="100%"
                                height="100%"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                allowfullscreen=""></iframe>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Contact End -->
</div>
