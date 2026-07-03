{{--
  External reservation link — uses Settings → Online reservation URL.
  Props: $class, $label (default "Book Now"), $style (fill|border|sm-btn), $icon (optional FA class)
--}}
@php
    $reservationUrl = hotel_reservation_url($setting ?? null);
    $btnClass = trim('theme-btn btn-style ' . ($style ?? 'sm-btn fill') . ' ' . ($class ?? ''));
    $btnLabel = $label ?? 'Book Now';
@endphp
@if(filled($reservationUrl))
    <a href="{{ $reservationUrl }}"
       class="{{ $btnClass }}"
       target="_blank"
       rel="noopener noreferrer"
       data-no-spa-navigate>
        @if(!empty($icon))
            <i class="{{ $icon }} me-2" aria-hidden="true"></i>
        @endif
        <span>{{ $btnLabel }}</span>
    </a>
@else
    <a wire:navigate href="{{ route('connect') }}" class="{{ $btnClass }}">
        @if(!empty($icon))
            <i class="{{ $icon }} me-2" aria-hidden="true"></i>
        @endif
        <span>{{ $btnLabel }}</span>
    </a>
@endif
