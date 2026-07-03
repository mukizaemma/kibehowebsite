@php
    $brandLogoFallback = asset('assets/images/brand/kibeho-magnificat-logo.png');
    $footerLogoUrl = filled(trim((string) ($setting?->donate ?? '')))
        ? asset('storage/images') . $setting->donate
        : $brandLogoFallback;

    $ftHotel = \App\Models\HotelContact::first();
    $receptionPhone  = $setting?->reception_phone ?? null;
    $managerPhone    = $setting?->manager_phone ?? null;
    $restaurantPhone = $setting?->restaurant_phone ?? null;
    $mainPhone = $ftHotel?->phone ?? $setting?->phone ?? '';
    $mainEmail = $ftHotel?->email ?? $setting?->email ?? '';

    $ftAddr = '';
    if ($ftHotel) {
        $ftAddr = trim(implode(' ', array_filter([
            $ftHotel->address,
            $ftHotel->city,
            $ftHotel->country,
            $ftHotel->postal_code,
        ])));
    }
    if ($ftAddr === '') {
        $ftAddr = $setting?->address ?? '';
    }

    $ftMapUrl = $ftAddr !== ''
        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($ftAddr)
        : 'https://maps.app.goo.gl/HHpJhzWDsh4JCiVCA';

    $socialLinks = [];
    $socialRaw = [
        [$ftHotel?->facebook ?? $setting?->facebook, 'fab fa-facebook-f', 'Facebook'],
        [$ftHotel?->twitter ?? $setting?->twitter, 'fab fa-twitter', 'Twitter'],
        [$ftHotel?->instagram ?? $setting?->instagram, 'fab fa-instagram', 'Instagram'],
        [$ftHotel?->linkedin ?? $setting?->linkedin, 'fab fa-linkedin-in', 'LinkedIn'],
        [$setting?->youtube, 'fab fa-youtube', 'YouTube'],
    ];
    foreach ($socialRaw as $row) {
        if (filled(trim((string) ($row[0] ?? '')))) {
            $socialLinks[] = ['url' => trim((string) $row[0]), 'icon' => $row[1], 'label' => $row[2]];
        }
    }

    $footerWhatsappDigits = '';
    if (filled(trim((string) ($setting->whatsapp_e164 ?? '')))) {
        $footerWhatsappDigits = preg_replace('/\D+/', '', (string) $setting->whatsapp_e164);
    } elseif (filled(trim((string) ($setting->reception_phone ?? '')))) {
        $footerWhatsappDigits = preg_replace('/\D+/', '', (string) $setting->reception_phone);
    } elseif ($ftHotel && filled($ftHotel->whatsapp)) {
        $footerWhatsappDigits = preg_replace('/\D+/', '', $ftHotel->whatsapp);
    }

    $footerWhatsappLabel = filled(trim((string) ($setting->reception_phone ?? '')))
        ? trim((string) $setting->reception_phone)
        : (($ftHotel && filled($ftHotel->whatsapp))
            ? $ftHotel->whatsapp
            : trim((string) ($setting->whatsapp_e164 ?? '')));

    $footerStars = isset($setting?->star_rating) ? (int) $setting->star_rating : 0;

    $footerAmenities = [
        ['label' => '24/7 Security', 'icon' => 'fa-solid fa-shield-halved'],
        ['label' => 'Free Parking', 'icon' => 'fa-solid fa-square-parking'],
        ['label' => 'Free Wi-Fi', 'icon' => 'fa-solid fa-wifi'],
        ['label' => 'Transport', 'icon' => 'fa-solid fa-shuttle-van'],
        ['label' => 'Meeting Rooms', 'icon' => 'fa-solid fa-people-group'],
    ];

    $footerExploreLinks = [
        ['label' => 'Home', 'route' => 'home', 'icon' => 'fa-solid fa-house'],
        ['label' => 'About', 'route' => 'about', 'icon' => 'fa-solid fa-book-open'],
        ['label' => 'Rooms', 'route' => 'rooms', 'icon' => 'fa-solid fa-bed'],
        ['label' => 'Dining', 'route' => 'dining', 'icon' => 'fa-solid fa-utensils'],
        ['label' => 'Events', 'route' => 'meetings-events', 'icon' => 'fa-solid fa-calendar-days'],
        ['label' => 'Gallery', 'route' => 'gallery', 'icon' => 'fa-solid fa-images'],
        ['label' => 'Contact', 'route' => 'contact', 'icon' => 'fa-solid fa-envelope'],
    ];
@endphp

<footer class="site-footer site-footer--kibeho" aria-label="Site footer">
    <div class="site-footer__accent" aria-hidden="true"></div>

    <div class="container site-footer__body">
        <div class="site-footer__layout">
            <aside class="site-footer__brand">
                <a wire:navigate href="{{ route('home') }}" class="site-footer__logo-link">
                    <img class="site-footer__logo" src="{{ $footerLogoUrl }}" alt="{{ $setting?->company ?? 'Kibeho Magnificat Hotel' }}" width="180" height="80" loading="lazy" decoding="async">
                </a>
                <p class="site-footer__tagline">Faith &middot; Hospitality &middot; Peace</p>

                @if($footerStars >= 1 && $footerStars <= 5)
                    <div class="site-footer__stars" role="img" aria-label="{{ $footerStars }} out of 5 stars">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="site-footer__star {{ $i <= $footerStars ? 'is-on' : 'is-off' }}" aria-hidden="true">
                                <i class="fa-solid fa-star"></i>
                            </span>
                        @endfor
                    </div>
                @endif

                @if(filled($setting?->quote))
                    <p class="site-footer__mission">{{ $setting->quote }}</p>
                @endif

                @if(count($socialLinks) > 0)
                    <div class="site-footer__social" role="list">
                        @foreach($socialLinks as $social)
                            <a href="{{ $social['url'] }}"
                               class="site-footer__social-link"
                               aria-label="{{ $social['label'] }}"
                               target="_blank"
                               rel="noopener noreferrer">
                                <i class="{{ $social['icon'] }}" aria-hidden="true"></i>
                            </a>
                        @endforeach
                    </div>
                @endif
            </aside>

            <div class="site-footer__panels">
                <div class="site-footer__link-panels">
                    <div class="site-footer__panel site-footer__panel--explore">
                        <h2 class="site-footer__panel-title">Explore the hotel</h2>
                        <ul class="site-footer__nav-grid" role="list">
                            @foreach($footerExploreLinks as $link)
                                <li>
                                    <a wire:navigate href="{{ route($link['route']) }}" class="site-footer__nav-link">
                                        <span class="site-footer__nav-icon" aria-hidden="true">
                                            <i class="{{ $link['icon'] }}"></i>
                                        </span>
                                        <span class="site-footer__nav-label">{{ $link['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if(isset($facilities) && $facilities->isNotEmpty())
                        <div class="site-footer__panel site-footer__panel--facilities">
                            <h2 class="site-footer__panel-title">Our facilities</h2>
                            <ul class="site-footer__facility-tags" role="list">
                                @foreach($facilities as $facility)
                                    <li>
                                        <a wire:navigate href="{{ route('facility', ['slug' => $facility->slug]) }}" class="site-footer__facility-tag">
                                            {{ $facility->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="site-footer__amenities-block">
                    <h2 class="site-footer__panel-title">At your service</h2>
                    <ul class="site-footer__amenity-grid" role="list">
                        @foreach($footerAmenities as $amenity)
                            <li class="site-footer__amenity">
                                <span class="site-footer__amenity-icon" aria-hidden="true">
                                    <i class="{{ $amenity['icon'] }}"></i>
                                </span>
                                <span class="site-footer__amenity-label">{{ $amenity['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="site-footer__contact-bar">
            @if($receptionPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $receptionPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">Reception</span>
                        <span>{{ $receptionPhone }}</span>
                    </span>
                </a>
            @elseif(filled($mainPhone))
                <a href="tel:{{ preg_replace('/\s+/', '', $mainPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text"><span>{{ $mainPhone }}</span></span>
                </a>
            @endif

            @if($managerPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $managerPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">Manager</span>
                        <span>{{ $managerPhone }}</span>
                    </span>
                </a>
            @endif

            @if($restaurantPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $restaurantPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">Restaurant</span>
                        <span>{{ $restaurantPhone }}</span>
                    </span>
                </a>
            @endif

            @if(filled($footerWhatsappDigits))
                <a href="https://wa.me/{{ $footerWhatsappDigits }}" target="_blank" rel="noopener noreferrer" class="site-footer__contact-item site-footer__contact-item--wa">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
                    <span class="site-footer__contact-text"><span>{{ $footerWhatsappLabel }}</span></span>
                </a>
            @endif

            @if(filled($mainEmail))
                <a href="mailto:{{ $mainEmail }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                    <span class="site-footer__contact-text"><span class="site-footer__contact-break">{{ $mainEmail }}</span></span>
                </a>
            @endif

            @if(filled($ftAddr))
                <a href="{{ $ftMapUrl }}" target="_blank" rel="noopener noreferrer" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <span class="site-footer__contact-text"><span>{{ $ftAddr }}</span></span>
                </a>
            @endif

            <div class="site-footer__book-wrap">
                @include('frontend.includes.reservation-link', [
                    'style' => 'fill',
                    'class' => 'site-footer__book-btn footer__book-cta',
                    'label' => 'Book your stay',
                ])
            </div>
        </div>
    </div>

    <div class="site-footer__copyright">
        <div class="container">
            <p class="site-footer__copyright-text mb-0">
                &copy; {{ date('Y') }} {{ $setting?->company ?? 'Kibeho Magnificat MV Hôtel' }}. All rights reserved.
                @if($setting?->footer_delivered_by_enabled && filled(trim((string) ($setting->footer_delivered_by_company ?? ''))))
                    Delivered by
                    @php
                        $creditUrl = trim((string) ($setting->footer_delivered_by_url ?? ''));
                        $creditName = trim((string) $setting->footer_delivered_by_company);
                    @endphp
                    @if($creditUrl !== '' && filter_var($creditUrl, FILTER_VALIDATE_URL))
                        <a href="{{ $creditUrl }}" target="_blank" rel="noopener noreferrer">{{ $creditName }}</a>.
                    @else
                        {{ $creditName }}.
                    @endif
                @endif
            </p>
        </div>
    </div>
</footer>
