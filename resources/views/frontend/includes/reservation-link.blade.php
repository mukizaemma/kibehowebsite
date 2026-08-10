{{--
  Book your stay — external channel when enabled & configured, otherwise /book-now wizard.
  Props: $class, $label, $style (fill|border|sm-btn), $icon, $room (Room model) or $roomId
--}}
@php
    $roomId = isset($roomId) ? (int) $roomId : (isset($room) ? (int) ($room->id ?? 0) : 0);
    $bookUrl = hotel_book_now_url($setting ?? null, $roomId > 0 ? $roomId : null);
    $bookExternal = hotel_book_now_is_external($setting ?? null);
    $btnClass = trim('theme-btn btn-style ' . ($style ?? 'sm-btn fill') . ' ' . ($class ?? ''));
    $btnLabel = $label ?? site_trans('buttons.book_your_stay');
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
