{{--
  General enquiry form — saves to messages; phone/email CTAs shown after successful submit.
--}}
@php
    $formId = 'hotel-enquiry-form-'.($formSuffix ?? 'default');
@endphp
<form action="{{ route('enquiry.submit') }}" method="POST" class="hotel-enquiry-form" id="{{ $formId }}" novalidate>
    @csrf
    <input type="hidden" name="enquiry_type" value="general">
    @if(filled($contextLabel ?? null))
        <input type="hidden" name="enquiry_context" value="{{ $contextLabel }}">
    @endif

    <div class="row g-3">
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
            <label class="form-label" for="{{ $formId }}-subject">{{ site_trans('booking.form_subject') }}</label>
            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="{{ $formId }}-subject" name="subject" value="{{ old('subject') }}" required>
            @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="{{ $formId }}-message">{{ site_trans('booking.form_message') }}</label>
            <textarea class="form-control @error('message') is-invalid @enderror" id="{{ $formId }}-message" name="message" rows="4" required>{{ old('message') }}</textarea>
            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <button type="submit" class="theme-btn btn-style border w-100">
                <span>{{ site_trans('booking.submit_enquiry') }}</span>
            </button>
        </div>
    </div>
</form>
