@php
    $contact = $contact ?? \App\Models\HotelContact::firstOrNew([]);
@endphp

<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-1">Public contact page</h5>
        <p class="text-muted small mb-0">Detailed contact details used on the Contact page, map panels, and footer (city, coordinates, WhatsApp).</p>
    </div>
    <form action="{{ route('content-management.contacts.update') }}" method="POST">
        @csrf
        <div class="card-body border-bottom-0 pb-4">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="hc_phone">Phone</label>
                    <input type="text" class="form-control" id="hc_phone" name="phone" value="{{ old('phone', $contact->phone ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_email">Email</label>
                    <input type="email" class="form-control" id="hc_email" name="email" value="{{ old('email', $contact->email ?? '') }}">
                </div>
                <div class="col-12">
                    <label class="form-label" for="hc_address">Address</label>
                    <input type="text" class="form-control" id="hc_address" name="address" value="{{ old('address', $contact->address ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="hc_city">City</label>
                    <input type="text" class="form-control" id="hc_city" name="city" value="{{ old('city', $contact->city ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="hc_country">Country</label>
                    <input type="text" class="form-control" id="hc_country" name="country" value="{{ old('country', $contact->country ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="hc_postal_code">Postal code</label>
                    <input type="text" class="form-control" id="hc_postal_code" name="postal_code" value="{{ old('postal_code', $contact->postal_code ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_website">Website</label>
                    <input type="url" class="form-control" id="hc_website" name="website" value="{{ old('website', $contact->website ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_whatsapp">WhatsApp</label>
                    <input type="text" class="form-control" id="hc_whatsapp" name="whatsapp" value="{{ old('whatsapp', $contact->whatsapp ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_facebook">Facebook</label>
                    <input type="url" class="form-control" id="hc_facebook" name="facebook" value="{{ old('facebook', $contact->facebook ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_twitter">Twitter / X</label>
                    <input type="url" class="form-control" id="hc_twitter" name="twitter" value="{{ old('twitter', $contact->twitter ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_instagram">Instagram</label>
                    <input type="url" class="form-control" id="hc_instagram" name="instagram" value="{{ old('instagram', $contact->instagram ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_linkedin">LinkedIn</label>
                    <input type="url" class="form-control" id="hc_linkedin" name="linkedin" value="{{ old('linkedin', $contact->linkedin ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_latitude">Latitude</label>
                    <input type="number" step="any" class="form-control" id="hc_latitude" name="latitude" value="{{ old('latitude', $contact->latitude ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="hc_longitude">Longitude</label>
                    <input type="number" step="any" class="form-control" id="hc_longitude" name="longitude" value="{{ old('longitude', $contact->longitude ?? '') }}">
                </div>
            </div>
        </div>
        <div class="card-footer bg-light border-top py-3 d-flex justify-content-end gap-2 flex-wrap">
            <button type="submit" class="btn btn-primary">Save public contact page</button>
        </div>
    </form>
</div>
