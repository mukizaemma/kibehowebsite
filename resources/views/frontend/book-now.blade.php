<div class="public-livewire-page book-now-page">
    @php
        $setting = $setting ?? \App\Models\Setting::first();
    @endphp

    <section class="book-wizard section__padding">
        <div class="container">
            <div class="book-wizard__intro text-center mb-40 mb-lg-50">
                <h1 class="book-wizard__page-title">{{ site_trans('booking.wizard_title') }}</h1>
                <p class="book-wizard__page-lead">{{ site_trans('booking.wizard_lead') }}</p>
            </div>

            @if($submitted)
                <div class="book-wizard__success text-center">
                    <div class="book-wizard__success-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></div>
                    <h2 class="book-wizard__success-title">{{ site_trans('booking.wizard_success_title') }}</h2>
                    <p class="book-wizard__success-lead">{{ site_trans('booking.wizard_success_lead') }}</p>
                    @if($submittedBookingId)
                        <p class="book-wizard__success-ref">{{ site_trans('booking.wizard_success_ref', ['id' => $submittedBookingId]) }}</p>
                    @endif
                    @if($guestEmailSent)
                        <p class="book-wizard__success-email">A confirmation email was sent to <strong>{{ $email }}</strong>.</p>
                    @else
                        <p class="book-wizard__success-email">We saved your request. If you do not receive an email shortly, contact us and quote your reference.</p>
                    @endif
                    <div class="book-wizard__success-actions">
                        <a wire:navigate href="{{ localized_route('home') }}" class="theme-btn btn-style fill">
                            <span>{{ site_trans('nav.home') }}</span>
                        </a>
                        <a wire:navigate href="{{ localized_route('explore-kibeho') }}" class="theme-btn btn-style border">
                            <span>{{ site_trans('home.explore_kibeho') }}</span>
                        </a>
                    </div>
                </div>
            @else
                <ol class="book-wizard__steps list-unstyled">
                    @foreach ([1 => 'stay', 2 => 'guest', 3 => 'confirm'] as $num => $key)
                    <li class="book-wizard__step {{ $step === $num ? 'is-current' : '' }} {{ $step > $num ? 'is-done' : '' }}">
                        <span class="book-wizard__step-num">{{ $num }}</span>
                        <span class="book-wizard__step-label">{{ site_trans('booking.wizard_step_'.$key) }}</span>
                    </li>
                    @endforeach
                </ol>

                <div class="row g-4 g-xl-5 align-items-start">
                    <div class="col-lg-7">
                        <div class="book-wizard__card">
                            @if ($errors->any())
                                <div class="book-wizard__errors" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($step === 1)
                                <h2 class="book-wizard__card-title">{{ site_trans('booking.wizard_stay_title') }}</h2>
                                <p class="book-wizard__card-lead">{{ site_trans('booking.wizard_stay_lead') }}</p>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <fieldset class="book-wizard__party">
                                            <legend class="form-label mb-2">{{ site_trans('booking.form_party_type') }}</legend>
                                            <div class="book-wizard__party-options">
                                                <label class="book-wizard__radio">
                                                    <input type="radio" wire:model.live="party_type" value="individual">
                                                    <span>
                                                        <strong>{{ site_trans('booking.party_individual') }}</strong>
                                                        <small>{{ site_trans('booking.party_individual_help') }}</small>
                                                    </span>
                                                </label>
                                                <label class="book-wizard__radio">
                                                    <input type="radio" wire:model.live="party_type" value="group">
                                                    <span>
                                                        <strong>{{ site_trans('booking.party_group') }}</strong>
                                                        <small>{{ site_trans('booking.party_group_help') }}</small>
                                                    </span>
                                                </label>
                                            </div>
                                        </fieldset>
                                        @error('party_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>

                                    @if($party_type === 'group')
                                        <div class="col-12">
                                            <p class="book-wizard__group-note mb-0">{{ site_trans('booking.group_availability_note') }}</p>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <label class="form-label" for="wizard-room">{{ site_trans('booking.form_room') }}</label>
                                        <select wire:model.live="room_id" id="wizard-room" class="form-select @error('room_id') is-invalid @enderror">
                                            <option value="">{{ site_trans('booking.form_room_placeholder') }}</option>
                                            @foreach($availableRooms as $room)
                                                <option value="{{ $room->id }}">
                                                    {{ $room->title }} — {{ hotel_price($room->price ?? 0, $setting) }}
                                                    @if((int) ($room->number_of_rooms ?? 1) > 1)
                                                        ({{ site_trans('booking.form_inventory', ['count' => (int) $room->number_of_rooms]) }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('room_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="wizard-checkin">{{ site_trans('booking.form_checkin') }}</label>
                                        <input wire:model.live="checkin" type="date" id="wizard-checkin" class="form-control @error('checkin') is-invalid @enderror" min="{{ now()->toDateString() }}">
                                        @error('checkin') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="wizard-checkout">{{ site_trans('booking.form_checkout') }}</label>
                                        <input wire:model.live="checkout" type="date" id="wizard-checkout" class="form-control @error('checkout') is-invalid @enderror" min="{{ $checkin ?: now()->toDateString() }}">
                                        @error('checkout') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="wizard-adults">{{ site_trans('booking.form_adults') }}</label>
                                        <input wire:model.live="adults" type="number" min="1" max="{{ $party_type === 'group' ? 120 : 20 }}" id="wizard-adults" class="form-control @error('adults') is-invalid @enderror">
                                        @error('adults') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="wizard-children">{{ site_trans('booking.form_children') }}</label>
                                        <input wire:model.live="children" type="number" min="0" max="{{ $party_type === 'group' ? 60 : 20 }}" id="wizard-children" class="form-control @error('children') is-invalid @enderror">
                                        @error('children') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="wizard-rooms">{{ site_trans('booking.form_rooms') }}</label>
                                        <input wire:model.live="room_count" type="number" min="1" max="{{ $roomInventory }}" id="wizard-rooms" class="form-control @error('room_count') is-invalid @enderror">
                                        @error('room_count') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        @if($selectedRoom)
                                            <small class="text-muted">{{ site_trans('booking.form_rooms_hint', ['max' => $roomInventory]) }}</small>
                                        @endif
                                    </div>
                                </div>
                            @elseif($step === 2)
                                <h2 class="book-wizard__card-title">{{ site_trans('booking.wizard_guest_title') }}</h2>
                                <p class="book-wizard__card-lead">
                                    {{ $party_type === 'group' ? site_trans('booking.wizard_guest_lead_group') : site_trans('booking.wizard_guest_lead') }}
                                </p>
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label" for="wizard-names">{{ $party_type === 'group' ? site_trans('booking.form_contact_name') : site_trans('booking.form_name') }}</label>
                                        <input wire:model="names" type="text" id="wizard-names" class="form-control @error('names') is-invalid @enderror" autocomplete="name">
                                        @error('names') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="wizard-phone">{{ site_trans('booking.form_phone') }} *</label>
                                        <input wire:model="phone" type="tel" id="wizard-phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+250 7XX XXX XXX" autocomplete="tel">
                                        @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="wizard-email">{{ site_trans('booking.form_email') }}</label>
                                        <input wire:model="email" type="email" id="wizard-email" class="form-control @error('email') is-invalid @enderror" autocomplete="email">
                                        @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="wizard-country">{{ site_trans('booking.form_country') }}</label>
                                        <input wire:model="country" type="text" id="wizard-country" class="form-control @error('country') is-invalid @enderror" autocomplete="country-name" placeholder="{{ site_trans('booking.form_country_placeholder') }}">
                                        @error('country') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="wizard-reason">{{ site_trans('booking.form_stay_reason') }}</label>
                                        <select wire:model="stay_reason" id="wizard-reason" class="form-select @error('stay_reason') is-invalid @enderror">
                                            <option value="">{{ site_trans('booking.form_stay_reason_placeholder') }}</option>
                                            @foreach($stayReasons as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        @error('stay_reason') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label" for="wizard-message">{{ site_trans('booking.form_message_optional') }}</label>
                                        <textarea wire:model="message" id="wizard-message" rows="4" class="form-control @error('message') is-invalid @enderror" placeholder="{{ $party_type === 'group' ? site_trans('booking.form_message_group_placeholder') : '' }}"></textarea>
                                        @error('message') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @else
                                <h2 class="book-wizard__card-title">{{ site_trans('booking.wizard_confirm_title') }}</h2>
                                <p class="book-wizard__card-lead">{{ site_trans('booking.wizard_confirm_lead') }}</p>

                                <dl class="book-wizard__review">
                                    <div><dt>{{ site_trans('booking.form_party_type') }}</dt><dd>{{ $party_type === 'group' ? site_trans('booking.party_group') : site_trans('booking.party_individual') }}</dd></div>
                                    <div><dt>{{ site_trans('booking.form_room') }}</dt><dd>{{ $selectedRoom?->title ?? '—' }}</dd></div>
                                    <div><dt>{{ site_trans('booking.wizard_dates') }}</dt><dd>{{ $checkin }} → {{ $checkout }}</dd></div>
                                    <div><dt>{{ site_trans('booking.wizard_guests') }}</dt><dd>{{ $adults }} {{ site_trans('booking.form_adults') }}, {{ $children }} {{ site_trans('booking.form_children') }} · {{ $room_count }} {{ site_trans('booking.wizard_rooms_short') }}</dd></div>
                                    <div><dt>{{ site_trans('booking.form_name') }}</dt><dd>{{ $names }}</dd></div>
                                    <div><dt>{{ site_trans('booking.form_country') }}</dt><dd>{{ $country }}</dd></div>
                                    <div><dt>{{ site_trans('booking.form_stay_reason') }}</dt><dd>{{ \App\Support\BookingStayReasons::label($stay_reason) }}</dd></div>
                                    <div><dt>{{ site_trans('booking.form_phone') }}</dt><dd>{{ $phone }}</dd></div>
                                    <div><dt>{{ site_trans('booking.form_email') }}</dt><dd>{{ $email }}</dd></div>
                                    <div><dt>{{ site_trans('booking.wizard_total') }}</dt><dd class="book-wizard__total">{{ hotel_price($estimatedTotal, $setting) }}</dd></div>
                                </dl>

                                <fieldset class="book-wizard__confirm-via">
                                    <legend>{{ site_trans('booking.wizard_confirm_via_title') }}</legend>
                                    <label class="book-wizard__radio">
                                        <input type="radio" wire:model="confirm_via" value="email">
                                        <span>
                                            <strong>{{ site_trans('booking.wizard_confirm_email') }}</strong>
                                            <small>{{ site_trans('booking.wizard_confirm_email_help') }}</small>
                                        </span>
                                    </label>
                                    @if($hasWhatsapp)
                                    <label class="book-wizard__radio">
                                        <input type="radio" wire:model="confirm_via" value="whatsapp">
                                        <span>
                                            <strong>{{ site_trans('booking.wizard_confirm_whatsapp') }}</strong>
                                            <small>{{ site_trans('booking.wizard_confirm_whatsapp_help') }}</small>
                                        </span>
                                    </label>
                                    @endif
                                    @error('confirm_via') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </fieldset>

                                <label class="book-wizard__terms">
                                    <input type="checkbox" wire:model="agree_terms">
                                    <span>{{ site_trans('booking.wizard_terms') }}</span>
                                </label>
                                @error('agree_terms') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @endif

                            <div class="book-wizard__nav">
                                @if($step > 1)
                                    <button type="button" class="book-wizard__btn book-wizard__btn--ghost" wire:click="previousStep">
                                        {{ site_trans('booking.wizard_back') }}
                                    </button>
                                @else
                                    <span></span>
                                @endif

                                @if($step < 3)
                                    <button type="button" class="book-wizard__btn book-wizard__btn--primary" wire:click="nextStep" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="nextStep">{{ site_trans('booking.wizard_continue') }}</span>
                                        <span wire:loading wire:target="nextStep">{{ site_trans('booking.wizard_submitting') }}</span>
                                    </button>
                                @else
                                    <button type="button" class="book-wizard__btn book-wizard__btn--primary" wire:click="confirmBooking" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="confirmBooking">{{ site_trans('booking.wizard_confirm_button') }}</span>
                                        <span wire:loading wire:target="confirmBooking">{{ site_trans('booking.wizard_submitting') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <aside class="col-lg-5">
                        <div class="book-wizard__summary">
                            <h3 class="book-wizard__summary-title">{{ site_trans('booking.wizard_summary_title') }}</h3>
                            <p class="book-wizard__summary-dates">
                                {{ $checkin ?: '—' }} → {{ $checkout ?: '—' }}
                                @if($nights > 0) · {{ site_trans('booking.wizard_nights', ['count' => $nights]) }} @endif
                            </p>

                            @if($selectedRoom)
                                <div class="book-wizard__summary-room">
                                    <strong>{{ $selectedRoom->title }}</strong>
                                    <span>{{ $party_type === 'group' ? site_trans('booking.party_group') : site_trans('booking.party_individual') }} · {{ $room_count }} {{ site_trans('booking.wizard_rooms_short') }}</span>
                                    <span>{{ $adults }} {{ site_trans('booking.form_adults') }}, {{ $children }} {{ site_trans('booking.form_children') }}</span>
                                    <span>{{ hotel_price($estimatedTotal, $setting) }}</span>
                                </div>
                            @else
                                <p class="book-wizard__summary-empty">{{ site_trans('booking.wizard_summary_empty') }}</p>
                            @endif

                            <div class="book-wizard__summary-total">
                                <span>{{ site_trans('booking.wizard_total') }}</span>
                                <strong>{{ hotel_price($estimatedTotal, $setting) }}</strong>
                            </div>

                            <div class="book-wizard__summary-help">
                                @if(filled($publicPhone))
                                    <a href="tel:{{ preg_replace('/\s+/', '', $publicPhone) }}"><i class="fa-solid fa-phone" aria-hidden="true"></i> {{ $publicPhone }}</a>
                                @endif
                                @if(filled($publicEmail))
                                    <a href="mailto:{{ $publicEmail }}" data-no-spa-navigate><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $publicEmail }}</a>
                                @endif
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </section>
</div>

@script
<script>
    $wire.on('open-url', (event) => {
        const url = event.url || (event[0] && event[0].url);
        if (url) {
            window.open(url, '_blank', 'noopener,noreferrer');
        }
    });
</script>
@endscript
