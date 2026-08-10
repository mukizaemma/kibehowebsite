<div class="public-livewire-page">

@include('frontend.includes.page-hero-banner', [
    'defaultCaption' => $event->title ?? site_trans('meetings.page_title'),
    'defaultDescription' => $pageHero?->description ?? site_trans('meetings.page_lead'),
])

@php
    $meetingRooms = $meetingRooms ?? collect();
@endphp

<section class="page-feature page-feature--meetings rts__section section__padding">
    <div class="container meetings-events-intro">
        <header class="page-feature__header mb-4 mb-lg-5 text-center text-lg-start">
            <p class="page-feature__eyebrow">{{ site_trans('meetings.eyebrow') }}</p>
            <h2 class="page-feature__title section__title">{{ $event->title ?? site_trans('meetings.page_title') }}</h2>
            <p class="page-feature__subtitle font-sm mx-auto ms-lg-0">
                {{ site_trans('meetings.page_subtitle') }}
            </p>
        </header>

        <aside class="hotel-capacity-note mb-4 mb-lg-5" aria-label="{{ site_trans('hotel.capacity_title') }}">
            <h3 class="hotel-capacity-note__title h6 mb-2">{{ site_trans('hotel.capacity_title') }}</h3>
            <p class="hotel-capacity-note__rooms mb-2">{{ site_trans('hotel.capacity_rooms') }}</p>
            <p class="hotel-capacity-note__meetings mb-0">{{ site_trans('hotel.capacity_meetings') }}</p>
        </aside>

        <div class="page-feature__prose content-richtext meetings-events-intro__description mb-4 mb-lg-5 w-100">
            @if(filled($event->description))
                {!! $event->description !!}
            @else
                <p class="text-muted mb-0">{{ site_trans('meetings.empty_description') }}</p>
            @endif
        </div>

        @if(filled($event->details ?? null))
            <div class="page-feature__details mt-5 pt-4 border-top w-100">
                <h2 class="h5 mb-3">{{ site_trans('meetings.details_title') }}</h2>
                <div class="page-feature__prose content-richtext mb-0">
                    {!! $event->details !!}
                </div>
            </div>
        @endif
    </div>
</section>

@if($meetingRooms->isNotEmpty())
<section class="meetings-events-rooms rts__section section__padding pt-0">
    <div class="container">
        <h2 class="h3 mb-2 mb-lg-3">{{ site_trans('meetings.rooms_title') }}</h2>
        <p class="text-muted mb-4 mb-lg-5">{{ site_trans('meetings.rooms_lead') }}</p>
        @include('frontend.includes.meeting-rooms-grid', ['meetingRooms' => $meetingRooms])
    </div>
</section>
@endif

<section class="meetings-events-proposal rts__section section__padding pt-0 pb-5">
    <div class="container">
        @if(isset($whyChooseUsItems) && $whyChooseUsItems->isNotEmpty())
            <div class="row align-items-stretch g-4 g-lg-5 meetings-events-wcu-row">
                <div class="col-12 meetings-events-wcu-col meetings-events-wcu-col--why">
                    @include('layouts.includes.why-choose-us', ['whyChooseUsLayout' => 'meetings'])
                </div>
                <div class="col-12 meetings-events-wcu-col meetings-events-wcu-col--form">
                    @include('frontend.includes.event-inquiry-sidebar', [
                        'formPrefix' => 'meetings',
                        'proposalSource' => 'meetings',
                        'cardTitle' => site_trans('meetings.proposal_title'),
                        'cardLead' => site_trans('meetings.proposal_lead'),
                        'iconClass' => 'fa-solid fa-building-columns',
                    ])
                </div>
            </div>
        @else
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    @include('frontend.includes.event-inquiry-sidebar', [
                        'formPrefix' => 'meetings',
                        'proposalSource' => 'meetings',
                        'cardTitle' => site_trans('meetings.proposal_title'),
                        'cardLead' => site_trans('meetings.proposal_lead'),
                        'iconClass' => 'fa-solid fa-building-columns',
                    ])
                </div>
            </div>
        @endif
    </div>
</section>
</div>
