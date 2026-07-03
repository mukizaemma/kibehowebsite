@php
    $setting = $setting ?? \App\Models\Setting::first();
@endphp
<div class="home-cta__panel home-cta__panel--map w-100">
    <div class="home-cta__map-head">
        <span class="home-cta__map-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
        <div class="home-cta__map-head-text">
            <span class="home-cta__map-label">Visit us</span>
            <span class="home-cta__map-title">{{ $setting?->company ?? config('app.name') }}</span>
            @if(filled($setting?->address))
                <span class="home-cta__map-address">{{ $setting->address }}</span>
            @endif
        </div>
    </div>
    <div class="home-cta__map-frame">
        @if(!empty($setting?->google_map_embed))
            {!! $setting?->google_map_embed !!}
        @else
            @php
                $hotelContact = \App\Models\HotelContact::first();
                $latitude = $hotelContact?->latitude ?? '-1.9441';
                $longitude = $hotelContact?->longitude ?? '30.0619';
            @endphp
            <iframe
                title="Hotel location on Google Maps"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3987.497311415315!2d{{ $longitude }}!3d{{ $latitude }}!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2z{{ $latitude }},{{ $longitude }}!5e0!3m2!1sen!2srw!4v1234567890"
                width="100%"
                height="100%"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen=""
            ></iframe>
        @endif
    </div>
</div>
