<?php

namespace App\Support;

use App\Models\HotelContact;
use Illuminate\Http\Request;

class SyncHotelContact
{
    public static function fromRequest(Request $request): void
    {
        $contact = HotelContact::firstOrNew([]);

        $contact->fill($request->only([
            'phone',
            'email',
            'address',
            'city',
            'country',
            'postal_code',
            'website',
            'facebook',
            'twitter',
            'instagram',
            'linkedin',
            'whatsapp',
            'latitude',
            'longitude',
        ]));

        $contact->save();
    }
}
