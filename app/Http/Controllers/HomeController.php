<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Post;
use App\Models\Room;
use App\Models\Trip;
use App\Models\User;
use App\Models\Role;
use App\Models\About;
use App\Models\Slide;
use App\Models\Review;
use App\Models\Message;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Facility;
use App\Models\Service;
use App\Models\Eventpage;
use App\Models\Promotion;
use App\Models\Roomimage;
use App\Models\Tourimage;
use App\Models\Tripimage;
use App\Models\Restaurant;
use App\Models\Subscriber;
use App\Models\BlogComment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Models\Facilityimage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Gallery;
use App\Models\PageHero;
use App\Models\TourActivity;
use App\Mail\BlogCommentGuestMail;
use App\Mail\BlogCommentsNotofications;
use App\Mail\ContactEnquiryAdminMail;
use App\Mail\ContactEnquiryGuestMail;
use App\Mail\NewSubscriberNotification;
use App\Mail\ReviewSubmittedAdminMail;
use App\Mail\ReviewSubmittedGuestMail;
use App\Mail\SubscriberThankYouMail;
use App\Services\PublicWebsiteData;
use App\Services\BookingSubmissionService;
use App\Services\SiteNotificationMail;
use Ramsey\Uuid\Uuid;


class HomeController extends Controller
{
    public function index(Request $request)
    {
        return view('frontend.home', PublicWebsiteData::home());
    }

    public function about()
    {
        return view('frontend.about', PublicWebsiteData::about());
    }

    public function rooms(Request $request)
    {
        return view('frontend.rooms', PublicWebsiteData::rooms($request));
    }

    public function room($slug)
    {
        return view('frontend.room', PublicWebsiteData::room($slug));
    }

    public function apartment()
    {
        return view('frontend.apartment', PublicWebsiteData::apartmentLanding());
    }

    public function facilities()
    {
        return view('frontend.facilities', PublicWebsiteData::facilities());
    }

    public function facility($slug)
    {
        return view('frontend.facility', PublicWebsiteData::facility($slug));
    }

    public function apartments(Request $request)
    {
        return view('frontend.rooms', PublicWebsiteData::apartments($request));
    }

    public function guesthouse()
    {
        return view('frontend.guesthouse', PublicWebsiteData::guesthouse());
    }

    public function restaurant()
    {
        return view('frontend.restaurant', PublicWebsiteData::restaurant());
    }

    public function promotions()
    {
        return view('frontend.promotions', PublicWebsiteData::promotions());
    }

    public function events()
    {
        return view('frontend.events', PublicWebsiteData::events());
    }

    public function spaWellness()
    {
        return view('frontend.spa-wellness', PublicWebsiteData::spaWellness());
    }

    public function activities()
    {
        return view('frontend.activities', PublicWebsiteData::activities());
    }

    public function activity($slug)
    {
        return view('frontend.activity', PublicWebsiteData::activity($slug));
    }

    public function gallery()
    {
        return \Livewire\Livewire::mount(\App\Livewire\Public\GalleryPage::class);
    }

    public function contact()
    {
        return view('frontend.contact', PublicWebsiteData::contact());
    }

    public function reviews()
    {
        return view('frontend.reviews', PublicWebsiteData::reviews());
    }

    public function review($id)
    {
        return view('frontend.review', PublicWebsiteData::review($id));
    }

    public function storeReview(Request $request){
        $request->validate([
            'names' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'testimony' => 'required|string|min:10',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $review = new Review();
        $review->names = $request->names;
        $review->email = $request->email;
        $review->testimony = $request->testimony;
        $review->rating = $request->rating;
        $review->website = $request->website;
        $review->status = 'pending';
        $review->save();

        $adminOk = SiteNotificationMail::sendToTeam(new ReviewSubmittedAdminMail($review));
        $guestOk = SiteNotificationMail::sendToGuest($review->email, new ReviewSubmittedGuestMail($review));

        return $this->redirectBackWithContactEmailSwal(
            redirect()->back(),
            $adminOk,
            $guestOk,
            true,
            'Thank you',
            'Your review was submitted. It will be published after admin approval.'
        );
    }

    public function terms()
    {
        return view('frontend.terms', PublicWebsiteData::terms());
    }

    public function storeBooking(Request $request, BookingSubmissionService $bookingSubmission)
    {
        try {
            $result = $bookingSubmission->submit($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Booking submission failed', ['message' => $e->getMessage(), 'exception' => $e]);

            return redirect()->back()->with('error', 'Something went wrong. Please try again later.')->withInput();
        }

        return $this->redirectBackWithContactEmailSwal(
            redirect()->back()->withFragment('enquiry-contacts'),
            $result['admin_sent'],
            $result['guest_sent'],
            true,
            'Reservation received',
            'Your reservation request was saved (reference #'.$result['booking']->id.'). We will get back to you soon.'
        )->with('show_enquiry_contacts', true);
    }

    /** @deprecated Use storeBooking() — kept for backward compatibility */
    public function bookNow(Request $request, BookingSubmissionService $bookingSubmission)
    {
        return $this->storeBooking($request, $bookingSubmission);
    }

    public function tours()
    {
        return view('frontend.tours', PublicWebsiteData::tours());
    }

    public function tour($slug)
    {
        return view('frontend.tour', PublicWebsiteData::tour($slug));
    }

    public function connect()
    {
        return view('frontend.contact', PublicWebsiteData::bookNow());
    }

    public function adminLogin()
    {
        return view('auth.login');
    }

    
    public function signinNow() {
        $setting = Setting::first();
        $about = About::first();
        return view('auth.login', [
            'setting' => $setting, 
            'about' => $about, 
        ]);
    }



    public function update($slug)
    {
        Blog::where('slug', $slug)->firstOrFail()->increment('views');

        return view('frontend.blog', PublicWebsiteData::blogPost($slug));
    }

    public function updates()
    {
        return view('frontend.blogs', PublicWebsiteData::updates());
    }

  
    public function signin(){
        $cart = session('cart', []);
        return view('web.login',[
            'cart'=>$cart,
        ]);
    }

    public function logouts()
    {
        Auth::logout();
        return redirect()->route('home');
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $guestRole = Role::where('slug', 'guest')->firstOrFail();

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_id' => Uuid::uuid4(),
            'role_id' => $guestRole->id,
            'status' => 'Active',
        ]);

        return redirect()->back()->with('success', 'User Created');
    }



    public function subscribe(Request $request) {
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('subscribers', 'email'),
            ],
        ]);

        $email = $request->input('email');

        $subscribed = Subscriber::create([
            'email' => $email,
        ]);


        if ($subscribed) {
            $adminOk = SiteNotificationMail::sendToTeam(new NewSubscriberNotification($subscribed));
            $guestOk = SiteNotificationMail::sendToGuest($email, new SubscriberThankYouMail($email));

            return $this->redirectBackWithContactEmailSwal(
                redirect()->back(),
                $adminOk,
                $guestOk,
                true,
                'Subscribed',
                'Thank you for joining our mailing list.'
            );
        }

        return redirect()->back()->with('error', 'Something Went Wrong. Try again later!');
    }
   

    public function sendMessage(Request $request)
    {
        $enquiryType = $request->input('enquiry_type', 'general');
        $rules = [
            'enquiry_type' => 'required|in:general,room',
            'names' => 'required|string|max:255',
            'phone' => 'required|string|max:60',
            'email' => 'required|email|max:255',
        ];

        if ($enquiryType === 'general') {
            $rules['subject'] = 'required|string|max:255';
            $rules['message'] = 'required|string|max:5000';
        } else {
            $rules['room_id'] = 'required|exists:rooms,id';
            $rules['checkin_date'] = 'required|date|after_or_equal:today';
            $rules['checkout_date'] = 'required|date|after:checkin_date';
            $rules['adults'] = 'required|integer|min:1';
            $rules['children'] = 'nullable|integer|min:0';
            $rules['rooms'] = 'nullable|integer|min:1';
            $rules['extra_beds'] = 'nullable|integer|min:0';
            $rules['message'] = 'nullable|string|max:5000';
        }

        $validated = $request->validate($rules);

        $email = trim((string) $validated['email']);

        $payload = [
            'enquiry_type' => $validated['enquiry_type'],
            'names' => $validated['names'],
            'phone' => $validated['phone'],
            'email' => $email,
            'message' => $validated['message'] ?? null,
            'room_id' => null,
            'checkin_date' => null,
            'checkout_date' => null,
            'adults' => null,
            'children' => null,
            'subject' => null,
        ];

        if ($validated['enquiry_type'] === 'general') {
            $payload['subject'] = $validated['subject'];
            $payload['message'] = $validated['message'];
            if ($request->filled('enquiry_context')) {
                $payload['message'] = trim($payload['message']."\n\n[Context:".trim((string) $request->input('enquiry_context')).']');
            }
        } else {
            $room = Room::find($validated['room_id']);
            $payload['room_id'] = (int) $validated['room_id'];
            $payload['checkin_date'] = $validated['checkin_date'];
            $payload['checkout_date'] = $validated['checkout_date'];
            $payload['adults'] = (int) $validated['adults'];
            $payload['children'] = isset($validated['children']) ? (int) $validated['children'] : 0;
            $payload['subject'] = $room ? 'Room enquiry: '.$room->title : 'Room enquiry';
            $payload['message'] = $validated['message'] ?? null;

            // Keep these extra fields consistent with the room booking form.
            // We store them inside the message text to avoid changing the message schema.
            $requestedRooms = (int) ($validated['rooms'] ?? 1);
            $extraBeds = (int) ($validated['extra_beds'] ?? 0);
            $extraLines = [];
            if ($requestedRooms > 1) {
                $extraLines[] = 'Number of Rooms: ' . $requestedRooms;
            }
            if ($extraBeds > 0) {
                $extraLines[] = 'Extra beds requested: ' . $extraBeds;
            }
            if (!empty($extraLines)) {
                $payload['message'] = trim(($payload['message'] ?? '') . "\n" . implode("\n", $extraLines));
            }
        }

        $message = Message::create($payload);
        $message->load('room');

        $adminSent = SiteNotificationMail::sendToTeam(new ContactEnquiryAdminMail($message));
        $guestSent = SiteNotificationMail::sendToGuest($message->email, new ContactEnquiryGuestMail($message));

        return $this->redirectBackWithContactEmailSwal(
            redirect()->back()->withFragment('enquiry-contacts'),
            $adminSent,
            $guestSent,
            true,
            'Message received',
            'Thank you for reaching out — we will get back to you soon.'
        )->with('show_enquiry_contacts', true);
    }

    public function submitProposal(Request $request)
    {
        $validated = $request->validate([
            'proposal_source' => 'required|in:meetings,dining',
            'names' => 'required|string|max:255',
            'phone' => 'required|string|max:60',
            'email' => 'required|email|max:255',
            'preferred_date' => 'required|date|after_or_equal:today',
            'number_of_days' => 'required|integer|min:1|max:365',
            'event_type' => 'nullable|string|max:64',
            'party_size' => 'nullable|integer|min:1',
            'meeting_room' => 'nullable|string|max:255',
            'additional_requests' => 'nullable|string|max:5000',
        ]);

        $label = $validated['proposal_source'] === 'dining' ? 'Dining' : 'Meetings & events';
        $subject = 'Proposal request — '.$label;

        $lines = [
            'Preferred date: '.$validated['preferred_date'],
            'Number of days: '.(int) $validated['number_of_days'],
        ];
        if (filled($validated['event_type'] ?? null)) {
            $lines[] = 'Event type: '.$validated['event_type'];
        }
        if (array_key_exists('party_size', $validated) && $validated['party_size'] !== null) {
            $lines[] = 'Party size: '.$validated['party_size'];
        }
        if (filled($validated['meeting_room'] ?? null)) {
            $lines[] = 'Meeting room: '.$validated['meeting_room'];
        }
        $extra = trim((string) ($validated['additional_requests'] ?? ''));
        if ($extra !== '') {
            $lines[] = 'Additional requests:';
            $lines[] = $extra;
        }
        $lines[] = 'Source: '.$label;

        $message = Message::create([
            'enquiry_type' => 'proposal',
            'names' => $validated['names'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'subject' => $subject,
            'message' => implode("\n", $lines),
        ]);

        $adminSent = SiteNotificationMail::sendToTeam(new ContactEnquiryAdminMail($message));
        $guestSent = SiteNotificationMail::sendToGuest($message->email, new ContactEnquiryGuestMail($message));

        return $this->redirectBackWithContactEmailSwal(
            redirect()->back(),
            $adminSent,
            $guestSent,
            true,
            'Request received',
            'Your proposal request was saved. We will follow up shortly.'
        );
    }

    public function testimony(Request $request)
    {
        $validated = $request->validate([
            'names' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'testimony' => 'required|string|min:10',
        ]);

        $review = Review::create([
            'names' => $validated['names'],
            'email' => $validated['email'],
            'testimony' => $validated['testimony'],
            'rating' => 5,
            'status' => 'pending',
        ]);

        $adminOk = SiteNotificationMail::sendToTeam(new ReviewSubmittedAdminMail($review));
        $guestOk = SiteNotificationMail::sendToGuest($review->email, new ReviewSubmittedGuestMail($review));

        return $this->redirectBackWithContactEmailSwal(
            redirect()->back(),
            $adminOk,
            $guestOk,
            true,
            'Thank you',
            'Your testimony was submitted. It will be published after admin approval.'
        );
    }

    public function sendComment(Request $request)
    {
        $validated = $request->validate([
            'blog_id' => 'required|exists:blogs,id',
            'names' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'comment' => 'required|string|min:2|max:5000',
        ]);

        $user = auth()->user();

        $comment = BlogComment::create([
            'blog_id' => $validated['blog_id'],
            'names' => $validated['names'],
            'email' => $validated['email'],
            'comment' => $validated['comment'],
            'added_by' => $user?->id,
        ]);

        $comment->load('blog');

        $adminOk = SiteNotificationMail::sendToTeam(new BlogCommentsNotofications($comment));
        $guestOk = SiteNotificationMail::sendToGuest($comment->email, new BlogCommentGuestMail($comment));

        return $this->redirectBackWithContactEmailSwal(
            redirect()->back(),
            $adminOk,
            $guestOk,
            true,
            'Comment received',
            'Thank you — your comment was saved.'
        );
    }

    /**
     * Flash SweetAlert payload: enquiry is always saved; describe whether notification emails succeeded.
     *
     * @param  bool  $guestAttempted  True when a guest confirmation email was attempted (address present).
     */
    private function redirectBackWithContactEmailSwal(
        RedirectResponse $redirect,
        bool $adminSent,
        bool $guestSent,
        bool $guestAttempted,
        string $title,
        string $savedLine
    ): RedirectResponse {
        $html = '<p>'.e($savedLine).'</p>';

        if (! $guestAttempted) {
            if ($adminSent) {
                $html .= '<p>Our team was notified by email.</p>';
                $icon = 'success';
            } else {
                $html .= '<p>We could not send email to our team. Your message was saved; we will follow up when possible.</p>';
                $icon = 'warning';
            }

            return $redirect->with('swal', [
                'icon' => $icon,
                'title' => $title,
                'html' => $html,
            ]);
        }

        if ($adminSent && $guestSent) {
            $html .= '<p>Email notifications were sent to our team and to your address.</p>';
            $icon = 'success';
        } elseif (! $adminSent && ! $guestSent) {
            $html .= '<p>We could not send email notifications. Your message was saved; we will follow up using your contact details.</p>';
            $icon = 'warning';
        } else {
            $html .= '<p>'
                .($adminSent ? 'Our team was notified by email.' : 'Could not notify our team by email.')
                .' '
                .($guestSent ? 'A confirmation was sent to your email.' : 'Could not send a confirmation to your email.')
                .'</p>';
            $icon = 'warning';
        }

        return $redirect->with('swal', [
            'icon' => $icon,
            'title' => $title,
            'html' => $html,
        ]);
    }

}
