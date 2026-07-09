{{--
  Book Now + enquiry panel.
  Book Now is always visible. Phone & email buttons appear only after a successful enquiry form submit.
--}}
@php
    $formSuffix = $formSuffix ?? md5((string) ($contextLabel ?? 'default'));
    $showContactButtons = session('show_enquiry_contacts', false);
    $c = \App\Support\HotelChannels::all();
    $bookUrl = hotel_book_now_url($setting ?? null);
    $bookExternal = hotel_book_now_is_external($setting ?? null);
    $bookingComUrl = $c['booking_com_url'] ?? null;
    $expediaUrl = $c['expedia_url'] ?? null;
    $hasPartners = booking_channel_enabled() && (filled($bookingComUrl) || filled($expediaUrl));
    $hotelName = $setting?->company ?? config('app.name', 'Kibeho Magnificat MV Hôtel');
    $email = $c['public_email'] ?? '';
    $mailto = filled($email) ? 'mailto:'.$email.'?subject='.rawurlencode('Enquiry — '.$hotelName) : '#';
    $publicPhone = hotel_public_phone($setting ?? null);
    $telHref = filled($publicPhone) ? 'tel:'.preg_replace('/\s+/', '', $publicPhone) : '#';
@endphp

<div class="hotel-channel-actions {{ $class ?? '' }}" id="enquiry-contacts">
    <div class="hotel-channel-actions__book mb-4">
        <h3 class="hotel-channel-actions__section-title h6 mb-2">{{ site_trans('buttons.book_now') }}</h3>
        <p class="hotel-channel-actions__lead small text-muted mb-3">
            {{ $bookExternal ? site_trans('booking.book_now_lead') : site_trans('booking.book_now_lead_contact') }}
        </p>
        @if($bookExternal)
            <a href="{{ $bookUrl }}" class="hotel-channel-actions__btn hotel-channel-actions__btn--booking theme-btn btn-style fill text-center w-100" target="_blank" rel="noopener noreferrer" data-no-spa-navigate>
                <i class="fa-solid fa-calendar-check me-2" aria-hidden="true"></i>
                <span>{{ site_trans('buttons.book_now') }}</span>
            </a>
        @else
            <a wire:navigate href="{{ $bookUrl }}" class="hotel-channel-actions__btn hotel-channel-actions__btn--booking theme-btn btn-style fill text-center w-100">
                <i class="fa-solid fa-calendar-check me-2" aria-hidden="true"></i>
                <span>{{ site_trans('buttons.book_now') }}</span>
            </a>
        @endif

        @if($hasPartners)
            <div class="hotel-channel-actions__partners mt-3">
                <p class="hotel-channel-actions__partners-lead small text-muted mb-2">{{ site_trans('booking.partners_lead') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    @if(filled($bookingComUrl))
                        <a href="{{ $bookingComUrl }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2" target="_blank" rel="noopener noreferrer" data-no-spa-navigate>
                            <i class="fa-solid fa-bed" aria-hidden="true"></i>
                            <span>{{ site_trans('booking.partner_booking_com') }}</span>
                        </a>
                    @endif
                    @if(filled($expediaUrl))
                        <a href="{{ $expediaUrl }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2" target="_blank" rel="noopener noreferrer" data-no-spa-navigate>
                            <i class="fa-solid fa-plane-departure" aria-hidden="true"></i>
                            <span>{{ site_trans('booking.partner_expedia') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="hotel-channel-actions__enquire border-top pt-4">
        @if($showContactButtons)
            <div class="hotel-channel-actions__success">
                <h3 class="hotel-channel-actions__section-title h6 mb-2">{{ site_trans('booking.enquiry_received_title') }}</h3>
                <p class="small text-muted mb-3">{{ site_trans('booking.enquiry_received_lead') }}</p>
                <div class="row g-2">
                    <div class="col-sm-6">
                        <a href="{{ $telHref }}" class="btn btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" @if(!filled($publicPhone)) aria-disabled="true" tabindex="-1" @endif>
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <span>{{ site_trans('booking.call_us') }}</span>
                        </a>
                    </div>
                    <div class="col-sm-6">
                        <a href="{{ $mailto }}" class="btn btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-2" data-no-spa-navigate @if(!filled($email)) aria-disabled="true" tabindex="-1" @endif>
                            <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                            <span>{{ site_trans('booking.email_us') }}</span>
                        </a>
                    </div>
                </div>
                @if(filled($publicPhone))
                    <p class="small text-muted text-center mt-2 mb-0">{{ $publicPhone }}</p>
                @endif
            </div>
        @else
            <h3 class="hotel-channel-actions__section-title h6 mb-2">{{ site_trans('booking.reserve_title') }}</h3>
            <p class="hotel-channel-actions__lead small text-muted mb-3">{{ site_trans('booking.reserve_lead') }}</p>
            @include('frontend.includes.hotel-room-booking-form', [
                'formSuffix' => $formSuffix,
                'roomId' => $roomId ?? null,
                'facilityId' => $facilityId ?? null,
                'tourActivityId' => $tourActivityId ?? null,
            ])

            <div class="hotel-channel-actions__enquire-general border-top pt-4 mt-4">
                <h3 class="hotel-channel-actions__section-title h6 mb-2">{{ site_trans('booking.enquire_title') }}</h3>
                <p class="hotel-channel-actions__lead small text-muted mb-3">{{ site_trans('booking.enquire_lead') }}</p>
                @include('frontend.includes.hotel-enquiry-form', [
                    'formSuffix' => $formSuffix.'-enquiry',
                    'contextLabel' => $contextLabel ?? null,
                ])
            </div>
        @endif
    </div>
</div>

@if($showContactButtons)
<script>
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('enquiry-contacts');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>
@endif
