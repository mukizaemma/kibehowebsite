{{--
  Room / facility / activity reservation form — saves to bookings table and sends Resend notifications.
  Props: $roomId, $facilityId, $tourActivityId, $formSuffix, $rooms (collection)
--}}
@php
    $formId = 'hotel-booking-form-'.($formSuffix ?? 'default');
    $rooms = $rooms ?? \App\Models\Room::where('status', 'Active')->orderBy('title')->get();
    $isFacility = filled($facilityId ?? null);
    $isActivity = filled($tourActivityId ?? null);
@endphp
<form action="{{ route('booking.submit') }}" method="POST" class="hotel-booking-form" id="{{ $formId }}" novalidate>
    @csrf

    @if($isFacility)
        <input type="hidden" name="facility_id" value="{{ $facilityId }}">
    @elseif($isActivity)
        <input type="hidden" name="tour_activity_id" value="{{ $tourActivityId }}">
    @endif

    <div class="row g-3">
        @if(! $isFacility && ! $isActivity)
            <div class="col-12">
                <label class="form-label" for="{{ $formId }}-room">{{ site_trans('booking.form_room') }}</label>
                <select class="form-select @error('room_id') is-invalid @enderror" id="{{ $formId }}-room" name="room_id" required>
                    <option value="">{{ site_trans('booking.form_room_placeholder') }}</option>
                    @foreach($rooms as $roomOption)
                        <option value="{{ $roomOption->id }}" @selected((int) old('room_id', $roomId ?? 0) === (int) $roomOption->id)>{{ $roomOption->title }}</option>
                    @endforeach
                </select>
                @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-checkin">{{ site_trans('booking.form_checkin') }}</label>
                <input type="date" class="form-control @error('checkin') is-invalid @enderror" id="{{ $formId }}-checkin" name="checkin" value="{{ old('checkin') }}" required min="{{ date('Y-m-d') }}">
                @error('checkin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-checkout">{{ site_trans('booking.form_checkout') }}</label>
                <input type="date" class="form-control @error('checkout') is-invalid @enderror" id="{{ $formId }}-checkout" name="checkout" value="{{ old('checkout') }}" required>
                @error('checkout')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="{{ $formId }}-adults">{{ site_trans('booking.form_adults') }}</label>
                <input type="number" class="form-control @error('adults') is-invalid @enderror" id="{{ $formId }}-adults" name="adults" value="{{ old('adults', 1) }}" min="1" required>
                @error('adults')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="{{ $formId }}-children">{{ site_trans('booking.form_children') }}</label>
                <input type="number" class="form-control @error('children') is-invalid @enderror" id="{{ $formId }}-children" name="children" value="{{ old('children', 0) }}" min="0">
                @error('children')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label" for="{{ $formId }}-rooms">{{ site_trans('booking.form_rooms') }}</label>
                <input type="number" class="form-control @error('rooms') is-invalid @enderror" id="{{ $formId }}-rooms" name="rooms" value="{{ old('rooms', 1) }}" min="1">
                @error('rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @elseif($isFacility)
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-date">{{ site_trans('booking.form_reservation_date') }}</label>
                <input type="date" class="form-control @error('reservation_date') is-invalid @enderror" id="{{ $formId }}-date" name="reservation_date" value="{{ old('reservation_date') }}" required min="{{ date('Y-m-d') }}">
                @error('reservation_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-guests">{{ site_trans('booking.form_guests') }}</label>
                <input type="number" class="form-control @error('guests') is-invalid @enderror" id="{{ $formId }}-guests" name="guests" value="{{ old('guests', 1) }}" min="1" required>
                @error('guests')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @else
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-checkin">{{ site_trans('booking.form_activity_date') }}</label>
                <input type="date" class="form-control @error('checkin') is-invalid @enderror" id="{{ $formId }}-checkin" name="checkin" value="{{ old('checkin') }}" required min="{{ date('Y-m-d') }}">
                @error('checkin')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-checkout">{{ site_trans('booking.form_activity_end') }}</label>
                <input type="date" class="form-control @error('checkout') is-invalid @enderror" id="{{ $formId }}-checkout" name="checkout" value="{{ old('checkout') }}" required>
                @error('checkout')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-adults">{{ site_trans('booking.form_adults') }}</label>
                <input type="number" class="form-control @error('adults') is-invalid @enderror" id="{{ $formId }}-adults" name="adults" value="{{ old('adults', 1) }}" min="1" required>
                @error('adults')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="{{ $formId }}-children">{{ site_trans('booking.form_children') }}</label>
                <input type="number" class="form-control @error('children') is-invalid @enderror" id="{{ $formId }}-children" name="children" value="{{ old('children', 0) }}" min="0">
                @error('children')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @endif

        <div class="col-md-6">
            <label class="form-label" for="{{ $formId }}-name">{{ site_trans('booking.form_name') }}</label>
            <input type="text" class="form-control @error('names') is-invalid @enderror" id="{{ $formId }}-name" name="names" value="{{ old('names') }}" required autocomplete="name">
            @error('names')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="{{ $formId }}-phone">{{ site_trans('booking.form_phone') }}</label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="{{ $formId }}-phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel">
            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="{{ $formId }}-email">{{ site_trans('booking.form_email') }}</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="{{ $formId }}-email" name="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="{{ $formId }}-message">{{ site_trans('booking.form_message_optional') }}</label>
            <textarea class="form-control @error('message') is-invalid @enderror" id="{{ $formId }}-message" name="message" rows="3">{{ old('message') }}</textarea>
            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <button type="submit" class="theme-btn btn-style border w-100">
                <span>{{ site_trans('booking.submit_reservation') }}</span>
            </button>
        </div>
    </div>
</form>
