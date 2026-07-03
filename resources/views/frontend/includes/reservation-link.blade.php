{{--
  Book Now — external channel when enabled & configured, otherwise contact page.
  Props: $class, $label (default "Book Now"), $style (fill|border|sm-btn), $icon (optional FA class)
--}}
@php
    $bookUrl = hotel_book_now_url($setting ?? null);
    $bookExternal = hotel_book_now_is_external($setting ?? null);
    $btnClass = trim('theme-btn btn-style ' . ($style ?? 'sm-btn fill') . ' ' . ($class ?? ''));
    $btnLabel = $label ?? site_trans('buttons.book_now');
@endphp
@if($bookExternal)
    <a href="{{ $bookUrl }}"
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
    <a wire:navigate href="{{ $bookUrl }}" class="{{ $btnClass }}">
        @if(!empty($icon))
            <i class="{{ $icon }} me-2" aria-hidden="true"></i>
        @endif
        <span>{{ $btnLabel }}</span>
    </a>
@endif
