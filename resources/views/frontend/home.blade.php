<div class="livewire-home-page home-journey">
{{-- 1. Hero: pilgrimage hospitality, not conference venue --}}
@include('frontend.includes.slides')

{{-- 2. Why people come --}}
<section class="rts__section home-purposes section__padding" id="home-purposes" aria-labelledby="home-purposes-heading">
    <div class="container">
        <div class="home-section-intro text-center wow fadeInUp">
            <h2 id="home-purposes-heading" class="home-section-intro__title">{{ site_trans('home.purposes_title') }}</h2>
            <p class="home-section-intro__lead">{{ site_trans('home.purposes_lead') }}</p>
        </div>
        <div class="row g-4 justify-content-center">
            @foreach ([
                ['key' => 'pilgrimage', 'route' => 'explore-kibeho', 'icon' => 'fa-solid fa-place-of-worship'],
                ['key' => 'retreats', 'route' => 'meetings-events', 'icon' => 'fa-solid fa-hands-praying'],
                ['key' => 'relaxation', 'route' => 'rooms', 'icon' => 'fa-solid fa-mountain-sun'],
            ] as $i => $purpose)
            <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay=".{{ ($i + 1) * 1 }}s">
                <a wire:navigate href="{{ localized_route($purpose['route']) }}" class="home-purpose">
                    <span class="home-purpose__icon" aria-hidden="true"><i class="{{ $purpose['icon'] }}"></i></span>
                    <h3 class="home-purpose__title">{{ site_trans('home.purpose_' . $purpose['key'] . '_title') }}</h3>
                    <p class="home-purpose__text">{{ site_trans('home.purpose_' . $purpose['key'] . '_text') }}</p>
                    <span class="home-purpose__link">{{ site_trans('home.learn_more') }}</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 3. Why visit Nyaruguru — title, lead & image from Content Management → Nyaruguru page --}}
@php
    $nyaruguruTitle = filled($nyaruguruPage?->home_title)
        ? $nyaruguruPage->home_title
        : site_trans('home.nyaruguru_title');
    $nyaruguruExcerpt = filled($nyaruguruPage?->home_lead)
        ? $nyaruguruPage->home_lead
        : (filled($nyaruguruPage?->description)
            ? Str::words(strip_tags($nyaruguruPage->description), 55, '…')
            : site_trans('home.nyaruguru_text'));
    $nyaruguruMedia = $nyaruguruImageUrl
        ?? ($gallery->first()['url'] ?? asset('storage/images/about/default.jpg'));
    $kibehoMedia = $kibehoImageUrl
        ?? ($gallery->first()['url'] ?? asset('storage/images/about/default.jpg'));
@endphp
<section class="rts__section home-kibeho section__padding" id="home-nyaruguru" aria-labelledby="home-nyaruguru-heading">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6 wow fadeInLeft" data-wow-delay=".1s">
                <div class="home-kibeho__media">
                    <img src="{{ $nyaruguruMedia }}"
                         alt="{{ $nyaruguruTitle }}"
                         loading="lazy"
                         decoding="async"
                         width="840"
                         height="640"
                         class="home-kibeho__img">
                </div>
            </div>
            <div class="col-lg-6 wow fadeInRight" data-wow-delay=".2s">
                <div class="home-kibeho__content">
                    <h2 id="home-nyaruguru-heading" class="home-kibeho__title">{{ $nyaruguruTitle }}</h2>
                    <p class="home-kibeho__text">{{ $nyaruguruExcerpt }}</p>
                    <a wire:navigate href="{{ localized_route('discover-nyaruguru') }}" class="theme-btn btn-style fill home-kibeho__btn">
                        <span>{{ site_trans('home.nyaruguru_cta') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 4. Rest after pilgrimage — rooms --}}
<section class="rts__section section__padding home-rooms-section" id="home-stay" aria-labelledby="home-rooms-heading">
    <div class="container">
        <div class="home-section-intro text-center wow fadeInUp">
            <h2 id="home-rooms-heading" class="home-section-intro__title">{{ site_trans('home.rooms_title') }}</h2>
            <p class="home-section-intro__lead">{{ site_trans('home.rooms_lead') }}</p>
        </div>

        @if($rooms->count() > 0)
        <div class="row g-4 g-lg-4 justify-content-center wow fadeInUp" data-wow-delay=".1s">
            @foreach($rooms->take(4) as $room)
            <div class="col-12 col-md-6">
                <article class="home-room-card">
                    <a wire:navigate href="{{ localized_route('room', ['slug' => $room->slug]) }}" class="home-room-card__media">
                        <img src="{{ asset('storage/' . ($room->cover_image ?? 'rooms/default.jpg')) }}"
                            alt="{{ $room->title }}"
                            loading="lazy"
                            width="800"
                            height="480">
                    </a>
                    <div class="home-room-card__body">
                        <div class="home-room-card__head">
                            <a wire:navigate href="{{ localized_route('room', ['slug' => $room->slug]) }}" class="home-room-card__title">{{ $room->title }}</a>
                            <div class="home-room-card__price">
                                <span class="home-room-card__price-from">{{ site_trans('home.starts_from') }}</span>
                                <div class="home-room-card__price-line">
                                    <span class="home-room-card__price-amount">{{ hotel_price($room->price ?? 0, $setting) }}</span>
                                    <span class="home-room-card__price-unit">{{ site_trans('home.per_night') }}</span>
                                </div>
                            </div>
                        </div>
                        <p class="home-room-card__excerpt">
                            {!! Str::words(strip_tags($room->description ?? ''), 28, '…') !!}
                        </p>
                        <div class="home-room-card__actions">
                            <a wire:navigate href="{{ localized_route('room', ['slug' => $room->slug]) }}" class="theme-btn btn-style sm-btn border">
                                <span>{{ site_trans('buttons.view_details') }}</span>
                            </a>
                            @include('frontend.includes.reservation-link', ['style' => 'sm-btn fill'])
                        </div>
                    </div>
                </article>
            </div>
            @endforeach
        </div>

        <div class="row mt-45 mt-lg-50">
            <div class="col-12 text-center">
                <a wire:navigate href="{{ localized_route('rooms') }}" class="home-rooms-view-all theme-btn btn-style border">
                    <span>{{ site_trans('home.view_all_rooms') }}</span>
                </a>
            </div>
        </div>
        @else
        <p class="text-center text-muted mb-0">{{ site_trans('home.rooms_coming_soon') }}</p>
        @endif
    </div>
</section>

{{-- 5. Journey timeline — full-bleed photo + readable type (CMS: Pilgrimage journey) --}}
@php
    $journeyTitle = filled($setting?->home_journey_title)
        ? $setting->home_journey_title
        : site_trans('home.journey_title');
    $journeyLead = filled($setting?->home_journey_lead)
        ? $setting->home_journey_lead
        : site_trans('home.journey_lead');

    $journeyBg = filled($setting?->home_journey_image)
        ? asset('storage/' . $setting->home_journey_image)
        : ($kibehoMedia
            ?? (isset($gallery) && $gallery->count() > 1 ? $gallery->skip(1)->first()['url'] : null)
            ?? (isset($gallery) && $gallery->count() > 0 ? $gallery->first()['url'] : null)
            ?? (isset($slides) && $slides->isNotEmpty() && filled($slides->first()->image)
                ? asset('storage/' . $slides->first()->image)
                : null)
            ?? asset('storage/images/about/default.jpg'));

    $journeySteps = isset($journeySteps) ? $journeySteps : collect();
@endphp
@if($journeySteps->isNotEmpty())
<section class="home-journey-scene" id="home-journey" aria-labelledby="home-journey-heading" style="--journey-bg: url('{{ $journeyBg }}')">
    <div class="home-journey-scene__media" aria-hidden="true"></div>
    <div class="home-journey-scene__veil" aria-hidden="true"></div>
    <div class="home-journey-scene__inner container">
        <div class="home-journey-scene__intro text-center wow fadeInUp">
            <h2 id="home-journey-heading" class="home-journey-scene__title">{{ $journeyTitle }}</h2>
            <p class="home-journey-scene__lead">{{ $journeyLead }}</p>
        </div>
        <ol class="home-timeline wow fadeInUp" data-wow-delay=".2s">
            @foreach ($journeySteps as $i => $step)
            <li class="home-timeline__step">
                <span class="home-timeline__index" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <span class="home-timeline__icon" aria-hidden="true"><i class="{{ $step->icon }}"></i></span>
                <span class="home-timeline__label">{{ $step->label }}</span>
            </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

{{-- 6. A place to gather — Meetings & Dining, then purpose cards --}}
@php
    $gatherTypes = [
        ['key' => 'church_retreats', 'icon' => 'fa-solid fa-church'],
        ['key' => 'choir_camps', 'icon' => 'fa-solid fa-music'],
        ['key' => 'youth', 'icon' => 'fa-solid fa-people-group'],
        ['key' => 'marriage', 'icon' => 'fa-solid fa-heart'],
        ['key' => 'priest', 'icon' => 'fa-solid fa-hands-praying'],
        ['key' => 'ngo', 'icon' => 'fa-solid fa-handshake'],
        ['key' => 'leadership', 'icon' => 'fa-solid fa-compass'],
    ];

    $gatherGallery = isset($gallery) ? $gallery->values() : collect();
    $gatherFallbacks = collect([
        $kibehoMedia ?? null,
        $gatherGallery->get(0)['url'] ?? null,
        $gatherGallery->get(1)['url'] ?? null,
        (isset($slides) && $slides->isNotEmpty() && filled($slides->first()->image)
            ? asset('storage/' . $slides->first()->image)
            : null),
        asset('storage/images/about/default.jpg'),
    ])->filter()->values();

    $gatherMeetingsRoom = ($meetingRooms ?? collect())
        ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
        ->values()
        ->first();

    $gatherMeetingsImage = $gatherFallbacks->first();
    if ($gatherMeetingsRoom) {
        if (filled($gatherMeetingsRoom->image)) {
            $gatherMeetingsImage = asset('storage/images/meeting-rooms/covers/' . $gatherMeetingsRoom->image);
        } elseif ($gatherMeetingsRoom->images->first()) {
            $gatherMeetingsImage = asset('storage/' . $gatherMeetingsRoom->images->first()->image);
        }
    }

    $gatherDiningImage = $gatherFallbacks->get(1) ?? $gatherFallbacks->first();
    if (isset($restaurant) && $restaurant) {
        if (filled($restaurant->image)) {
            $gatherDiningImage = asset('storage/images/restaurant/' . $restaurant->image);
        } elseif ($restaurant->images->isNotEmpty() && filled($restaurant->images->first()->image)) {
            $gatherDiningImage = asset('storage/images/restaurant/' . $restaurant->images->first()->image);
        }
    }
@endphp
<section class="home-gather-scene" id="home-gather" aria-labelledby="home-gather-heading">
    <div class="home-gather-scene__inner container">
        <header class="home-gather-scene__header text-center wow fadeInUp">
            <h2 id="home-gather-heading" class="home-gather-scene__title">{{ site_trans('home.gather_title') }}</h2>
            <p class="home-gather-scene__lead">{{ site_trans('home.gather_lead') }}</p>
        </header>

        <div class="row g-3 g-lg-4 home-gather-scene__destinations">
            <div class="col-md-6 wow fadeInUp">
                <article class="home-gather-dest">
                    <div class="home-gather-dest__media">
                        <img src="{{ $gatherMeetingsImage }}" alt="{{ site_trans('home.gather_meetings_title') }}" loading="lazy" width="800" height="520">
                    </div>
                    <div class="home-gather-dest__body">
                        <h3 class="home-gather-dest__title">{{ site_trans('home.gather_meetings_title') }}</h3>
                        <p class="home-gather-dest__text">{{ site_trans('home.gather_meetings_lead') }}</p>
                        <a wire:navigate href="{{ localized_route('meetings-events') }}" class="home-gather-dest__link">
                            {{ site_trans('home.gather_view_more') }}
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            </div>
            <div class="col-md-6 wow fadeInUp" data-wow-delay=".08s">
                <article class="home-gather-dest">
                    <div class="home-gather-dest__media">
                        <img src="{{ $gatherDiningImage }}" alt="{{ site_trans('home.gather_dining_title') }}" loading="lazy" width="800" height="520">
                    </div>
                    <div class="home-gather-dest__body">
                        <h3 class="home-gather-dest__title">{{ site_trans('home.gather_dining_title') }}</h3>
                        <p class="home-gather-dest__text">{{ site_trans('home.gather_dining_lead') }}</p>
                        <a wire:navigate href="{{ localized_route('dining') }}" class="home-gather-dest__link">
                            {{ site_trans('home.gather_view_more') }}
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            </div>
        </div>

        <div class="row g-3 g-md-4 justify-content-center home-gather-scene__purposes">
            @foreach ($gatherTypes as $i => $type)
            <div class="col-6 col-md-4 col-xl-3 wow fadeInUp" data-wow-delay=".0{{ min($i, 5) }}s">
                <a wire:navigate href="{{ localized_route('meetings-events') }}" class="home-gather-purpose">
                    <span class="home-gather-purpose__icon" aria-hidden="true"><i class="{{ $type['icon'] }}"></i></span>
                    <span class="home-gather-purpose__label">{{ site_trans('home.gather_tag_' . $type['key']) }}</span>
                    <span class="home-gather-purpose__arrow" aria-hidden="true">→</span>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- 7. Things to experience — 3 featured tour activities --}}
<section class="rts__section home-experiences section__padding" id="home-experiences" aria-labelledby="home-experiences-heading">
    <div class="container">
        <div class="home-section-intro text-center wow fadeInUp">
            <h2 id="home-experiences-heading" class="home-section-intro__title">{{ site_trans('home.experiences_title') }}</h2>
            <p class="home-section-intro__lead">{{ site_trans('home.experiences_lead') }}</p>
        </div>

        @if(isset($homeActivities) && $homeActivities->isNotEmpty())
        <div class="row g-4 justify-content-center">
            @foreach ($homeActivities as $i => $activity)
            @php
                $activityImage = filled($activity->cover_image)
                    ? asset('storage/' . $activity->cover_image)
                    : asset('storage/images/about/default.jpg');
                $activityExcerpt = filled($activity->description)
                    ? Str::words(strip_tags($activity->description), 28, '…')
                    : '';
            @endphp
            <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay=".0{{ $i }}s">
                <article class="home-activity-card h-100">
                    <a wire:navigate href="{{ localized_route('activity', ['slug' => $activity->slug]) }}" class="home-activity-card__media">
                        <img src="{{ $activityImage }}" alt="{{ $activity->title }}" loading="lazy" width="640" height="420">
                    </a>
                    <div class="home-activity-card__body">
                        <h3 class="home-activity-card__title">
                            <a wire:navigate href="{{ localized_route('activity', ['slug' => $activity->slug]) }}">{{ $activity->title }}</a>
                        </h3>
                        @if($activityExcerpt)
                        <p class="home-activity-card__text">{{ $activityExcerpt }}</p>
                        @endif
                        <a wire:navigate href="{{ localized_route('activity', ['slug' => $activity->slug]) }}" class="home-activity-card__link">
                            {{ site_trans('home.view_details') }}
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4 mt-lg-5 wow fadeInUp">
            <a wire:navigate href="{{ localized_route('activities') }}" class="theme-btn btn-style border">
                <span>{{ site_trans('home.view_all_activities') }}</span>
            </a>
        </div>
        @else
        <p class="text-center text-muted mb-0">{{ site_trans('home.activities_coming_soon') }}</p>
        @endif
    </div>
</section>

<x-booking-cta
    :rooms="$rooms"
    heading-id="home-cta-heading"
    :show-children-field="true"
    :eyebrow="''"
    :title="site_trans('home.close_booking_title')"
    :lead="site_trans('home.close_booking_lead')"
/>
</div>
