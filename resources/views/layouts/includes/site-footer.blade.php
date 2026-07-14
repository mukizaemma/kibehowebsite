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
        ['label' => site_trans('footer.amenity_security'), 'icon' => 'fa-solid fa-shield-halved'],
        ['label' => site_trans('footer.amenity_parking'), 'icon' => 'fa-solid fa-square-parking'],
        ['label' => site_trans('footer.amenity_wifi'), 'icon' => 'fa-solid fa-wifi'],
        ['label' => site_trans('footer.amenity_transport'), 'icon' => 'fa-solid fa-shuttle-van'],
        ['label' => site_trans('footer.amenity_meeting_rooms'), 'icon' => 'fa-solid fa-people-group'],
    ];

    $footerMainMenuLinks = [
        ['route' => 'home', 'label' => site_trans('nav.home')],
        ['route' => 'rooms', 'label' => site_trans('nav.stay')],
        ['route' => 'explore-kibeho', 'label' => site_trans('nav.explore_kibeho')],
        ['route' => 'meetings-events', 'label' => site_trans('nav.retreats')],
        ['route' => 'dining', 'label' => site_trans('footer.link_bar_restaurant')],
        ['route' => 'gallery', 'label' => site_trans('nav.gallery')],
        ['route' => 'contact', 'label' => site_trans('nav.contact')],
    ];
@endphp

<footer class="site-footer site-footer--kibeho" aria-label="Site footer">
    <div class="site-footer__accent" aria-hidden="true"></div>

    <div class="container site-footer__body">
        <div class="site-footer__layout">
            <aside class="site-footer__brand">
                <a wire:navigate href="{{ localized_route('home') }}" class="site-footer__logo-link">
                    <img class="site-footer__logo" src="{{ $footerLogoUrl }}" alt="{{ $setting?->company ?? 'Kibeho Magnificat Hotel' }}" width="180" height="80" loading="lazy" decoding="async">
                </a>
                <p class="site-footer__tagline">{{ site_trans('footer.tagline') }}</p>

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

            <nav class="site-footer__column site-footer__main-menu" aria-label="{{ site_trans('footer.quick_links') }}">
                <h2 class="site-footer__heading">{{ site_trans('footer.quick_links') }}</h2>
                <ul class="site-footer__link-list" role="list">
                    @foreach($footerMainMenuLinks as $item)
                        <li>
                            <a wire:navigate href="{{ localized_route($item['route']) }}">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <nav class="site-footer__column site-footer__discover" aria-label="{{ site_trans('footer.discover_region') }}">
                <h2 class="site-footer__heading">{{ site_trans('footer.discover_region') }}</h2>
                <ul class="site-footer__link-list" role="list">
                    <li>
                        <a wire:navigate href="{{ localized_route('discover-gikongoro-diocese') }}">
                            {{ site_trans('footer.discover_gikongoro_link') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ localized_route('discover-nyaruguru') }}">
                            {{ site_trans('footer.discover_nyaruguru_link') }}
                        </a>
                    </li>
                    <li>
                        <a wire:navigate href="{{ localized_route('explore-kibeho') }}">
                            {{ site_trans('footer.explore_sanctuary') }}
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="site-footer__services">
            <div class="site-footer__services-row">
                <h2 class="site-footer__heading site-footer__heading--services">{{ site_trans('footer.at_your_service') }}</h2>
                <ul class="site-footer__service-list" role="list">
                    @foreach($footerAmenities as $amenity)
                        <li class="site-footer__service-item">
                            <span class="site-footer__service-icon" aria-hidden="true">
                                <i class="{{ $amenity['icon'] }}"></i>
                            </span>
                            <span class="site-footer__service-label">{{ $amenity['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="site-footer__lang-wrap">
                    @include('frontend.includes.language-switcher', ['variant' => 'site-lang-switcher--footer'])
                </div>
            </div>
        </div>

        <div class="site-footer__contact-bar">
            <div class="site-footer__contact-list">
            @if($receptionPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $receptionPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.reception') }}</span>
                        <span class="site-footer__contact-value">{{ $receptionPhone }}</span>
                    </span>
                </a>
            @elseif(filled($mainPhone))
                <a href="tel:{{ preg_replace('/\s+/', '', $mainPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.phone') }}</span>
                        <span class="site-footer__contact-value">{{ $mainPhone }}</span>
                    </span>
                </a>
            @endif

            @if($managerPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $managerPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.manager') }}</span>
                        <span class="site-footer__contact-value">{{ $managerPhone }}</span>
                    </span>
                </a>
            @endif

            @if($restaurantPhone)
                <a href="tel:{{ preg_replace('/\s+/', '', $restaurantPhone) }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.restaurant') }}</span>
                        <span class="site-footer__contact-value">{{ $restaurantPhone }}</span>
                    </span>
                </a>
            @endif

            @if(filled($footerWhatsappDigits))
                <a href="https://wa.me/{{ $footerWhatsappDigits }}" target="_blank" rel="noopener noreferrer" class="site-footer__contact-item site-footer__contact-item--wa">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fab fa-whatsapp"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.whatsapp') }}</span>
                        <span class="site-footer__contact-value">{{ $footerWhatsappLabel }}</span>
                    </span>
                </a>
            @endif

            @if(filled($mainEmail))
                <a href="mailto:{{ $mainEmail }}" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.email') }}</span>
                        <span class="site-footer__contact-value site-footer__contact-break">{{ $mainEmail }}</span>
                    </span>
                </a>
            @endif

            @if(filled($ftAddr))
                <a href="{{ $ftMapUrl }}" target="_blank" rel="noopener noreferrer" class="site-footer__contact-item">
                    <span class="site-footer__contact-icon" aria-hidden="true"><i class="fa-solid fa-location-dot"></i></span>
                    <span class="site-footer__contact-text">
                        <span class="site-footer__contact-label">{{ site_trans('footer.location') }}</span>
                        <span class="site-footer__contact-value">{{ $ftAddr }}</span>
                    </span>
                </a>
            @endif
            </div>

            <div class="site-footer__book-wrap">
                @include('frontend.includes.reservation-link', [
                    'style' => 'fill',
                    'class' => 'site-footer__book-btn footer__book-cta',
                    'label' => site_trans('buttons.book_your_stay'),
                ])
            </div>
        </div>
    </div>

    <div class="site-footer__copyright">
        <div class="container">
            <p class="site-footer__copyright-text mb-0">
                &copy; {{ date('Y') }} {{ $setting?->company ?? 'Kibeho Magnificat MV Hôtel' }}. {{ site_trans('footer.all_rights') }}
                @if($setting?->footer_delivered_by_enabled && filled(trim((string) ($setting->footer_delivered_by_company ?? ''))))
                    {{ site_trans('footer.delivered_by') }}
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
