<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Online reservation (channel manager — powers all "Book Now" buttons)
    |--------------------------------------------------------------------------
    */

    'reservation_url' => env('HOTEL_RESERVATION_URL', env('HOTEL_BOOKING_COM_URL', '')),

    /*
    |--------------------------------------------------------------------------
    | Booking.com (reviews & listing — optional if using another channel)
    |--------------------------------------------------------------------------
    */

    'booking_com_url' => env('HOTEL_BOOKING_COM_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | TripAdvisor — property on TripAdvisor (locationId for widgets)
    |--------------------------------------------------------------------------
    */

    'tripadvisor_location_id' => env('HOTEL_TRIPADVISOR_LOCATION_ID', ''),

    'tripadvisor_hotel_url' => env('HOTEL_TRIPADVISOR_HOTEL_URL', ''),

    'tripadvisor_write_review_url' => env('HOTEL_TRIPADVISOR_WRITE_REVIEW_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Google Maps / reviews (place link + optional embed)
    |--------------------------------------------------------------------------
    */

    'google_place_url' => env('HOTEL_GOOGLE_PLACE_URL', ''),

    /** Embed without API key (coordinates). */
    'google_maps_embed_url' => env(
        'HOTEL_GOOGLE_MAPS_EMBED_URL',
        'https://maps.google.com/maps?q=-2.5975,29.5500&z=16&output=embed'
    ),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp — digits only, country code, no + (e.g. 250794191115)
    |--------------------------------------------------------------------------
    */

    'whatsapp_e164' => env('HOTEL_WHATSAPP_E164', ''),

    /** Default first message (URL-encoded automatically when building wa.me links). */
    'whatsapp_default_message' => env(
        'HOTEL_WHATSAPP_MESSAGE',
        'Hello Kibeho Magnificat MV Hôtel, I would like to enquire about:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Public contact email (mailto + displayed)
    |--------------------------------------------------------------------------
    */

    'public_email' => env('HOTEL_PUBLIC_EMAIL', 'info@kibeho-magnificat.rw'),

];
