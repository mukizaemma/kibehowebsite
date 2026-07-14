{{--
  Compact booking CTA: message, phone, email, Book your stay + Explore Kibeho.
  External booking URL when channel is configured; otherwise offline /book-now wizard.
--}}
@php
    $c = \App\Support\HotelChannels::all();
    $bookUrl = hotel_book_now_url($setting ?? null);
    $bookExternal = hotel_book_now_is_external($setting ?? null);
    $bookingComUrl = $c['booking_com_url'] ?? null;
    $expediaUrl = $c['expedia_url'] ?? null;
    $hasPartners = booking_channel_enabled() && (filled($bookingComUrl) || filled($expediaUrl));
    $hotelName = $setting?->company ?? config('app.name', 'Kibeho Magnificat MV Hôtel');
    $email = $c['public_email'] ?? '';
    $mailto = filled($email) ? 'mailto:'.$email.'?subject='.rawurlencode('Stay enquiry — '.$hotelName) : '#';
    $publicPhone = hotel_public_phone($setting ?? null);
    $telHref = filled($publicPhone) ? 'tel:'.preg_replace('/\s+/', '', $publicPhone) : '#';
@endphp

<div class="hotel-cta-panel {{ $class ?? '' }}" id="enquiry-contacts">
    <p class="hotel-cta-panel__message">
        {{ $bookExternal
            ? site_trans('booking.cta_panel_lead_online')
            : site_trans('booking.cta_panel_lead_offline') }}
    </p>

    <ul class="hotel-cta-panel__contacts list-unstyled">
        @if(filled($publicPhone))
        <li>
            <a href="{{ $telHref }}" class="hotel-cta-panel__contact">
                <i class="fa-solid fa-phone" aria-hidden="true"></i>
                <span>{{ $publicPhone }}</span>
            </a>
        </li>
        @endif
        @if(filled($email))
        <li>
            <a href="{{ $mailto }}" class="hotel-cta-panel__contact" data-no-spa-navigate>
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <span>{{ $email }}</span>
            </a>
        </li>
        @endif
    </ul>

    <div class="hotel-cta-panel__actions">
        @if($bookExternal)
            <a href="{{ $bookUrl }}"
               class="theme-btn btn-style fill hotel-cta-panel__btn"
               target="_blank"
               rel="noopener noreferrer"
               data-no-spa-navigate>
                <span>{{ site_trans('buttons.book_your_stay') }}</span>
            </a>
        @else
            <a wire:navigate href="{{ $bookUrl }}" class="theme-btn btn-style fill hotel-cta-panel__btn">
                <span>{{ site_trans('buttons.book_your_stay') }}</span>
            </a>
        @endif

        <a wire:navigate href="{{ localized_route('explore-kibeho') }}" class="theme-btn btn-style border hotel-cta-panel__btn">
            <span>{{ site_trans('home.explore_kibeho') }}</span>
        </a>
    </div>

    @if($hasPartners)
        <div class="hotel-cta-panel__partners">
            <p class="hotel-cta-panel__partners-lead">{{ site_trans('booking.partners_lead') }}</p>
            <div class="d-flex flex-wrap gap-2">
                @if(filled($bookingComUrl))
                    <a href="{{ $bookingComUrl }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer" data-no-spa-navigate>
                        {{ site_trans('booking.partner_booking_com') }}
                    </a>
                @endif
                @if(filled($expediaUrl))
                    <a href="{{ $expediaUrl }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener noreferrer" data-no-spa-navigate>
                        {{ site_trans('booking.partner_expedia') }}
                    </a>
                @endif
            </div>
        </div>
    @endif
</div>
