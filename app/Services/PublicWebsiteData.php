<?php

namespace App\Services;

use App\Models\About;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\Category;
use App\Models\Eventpage;
use App\Models\Facility;
use App\Models\GikongoroDiocesePage;
use App\Models\KibehoPage;
use App\Models\NyaruguruPage;
use App\Models\MeetingRoom;
use App\Models\Gallery;
use App\Services\AggregatedGalleryService;
use App\Models\SanctuaryEvent;
use App\Models\NyaruguruActivity;
use App\Models\HotelContact;
use App\Models\HomeJourneyStep;
use App\Models\PageHero;
use App\Models\Program;
use App\Models\Promotion;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\Room;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StaffMember;
use App\Models\Slide;
use App\Models\TermsCondition;
use App\Models\TourActivity;
use App\Models\Trip;
use App\Models\WhyChooseUsItem;
use Illuminate\Http\Request;

class PublicWebsiteData
{
    public static function home(): array
    {
        $setting = Setting::first();
        $slides = Slide::orderBy('sort_order')->orderBy('created_at')->orderBy('id')->get();
        $about = About::first();
        $rooms = Room::with('amenities')
            ->where('status', 'Active')
            ->oldest()
            ->get();
        $gallery = app(AggregatedGalleryService::class)->homeFeaturedImages();
        $homeFacilities = Facility::where('status', 'Active')->oldest()->take(3)->get();
        $services = Service::where('status', 'Active')->with('images')->latest()->take(4)->get();
        $blogs = Blog::where('status', 'Published')->latest()->take(3)->get() ?? collect();
        $reviews = Review::approved()->latest()->take(4)->get();
        $reviewCount = Review::approved()->count();
        $whyChooseUsItems = WhyChooseUsItem::query()->orderBy('sort_order')->orderBy('id')->get();

        $kibehoPage = KibehoPage::current()->load('images');
        $kibehoImageUrl = null;
        if (filled($kibehoPage->cover_image)) {
            $kibehoImageUrl = asset('storage/' . $kibehoPage->cover_image);
        } elseif ($kibehoPage->images->isNotEmpty()) {
            $kibehoImageUrl = asset('storage/' . $kibehoPage->images->first()->image);
        }

        $nyaruguruPage = NyaruguruPage::current()->load('images');
        $nyaruguruImageUrl = null;
        if (filled($nyaruguruPage->cover_image)) {
            $nyaruguruImageUrl = asset('storage/' . $nyaruguruPage->cover_image);
        } elseif ($nyaruguruPage->images->isNotEmpty()) {
            $nyaruguruImageUrl = asset('storage/' . $nyaruguruPage->images->first()->image);
        }

        $meetingRooms = collect();
        $event = Eventpage::with(['meetingRooms.images'])->first();
        if ($event) {
            MeetingRoom::ensureDefaultsForEventpage($event);
            $meetingRooms = $event->fresh()->load(['meetingRooms' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')])->meetingRooms;
        }

        $restaurant = Restaurant::with(['images'])->first();
        $journeySteps = HomeJourneyStep::query()->active()->ordered()->get();
        $homeActivities = TourActivity::query()
            ->active()
            ->ordered()
            ->take(3)
            ->get();

        return [
            'setting' => $setting,
            'slides' => $slides,
            'about' => $about,
            'rooms' => $rooms,
            'gallery' => $gallery,
            'homeFacilities' => $homeFacilities,
            'services' => $services,
            'blogs' => $blogs,
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'whyChooseUsItems' => $whyChooseUsItems,
            'kibehoPage' => $kibehoPage,
            'kibehoImageUrl' => $kibehoImageUrl,
            'nyaruguruPage' => $nyaruguruPage,
            'nyaruguruImageUrl' => $nyaruguruImageUrl,
            'meetingRooms' => $meetingRooms ?? collect(),
            'restaurant' => $restaurant,
            'journeySteps' => $journeySteps,
            'homeActivities' => $homeActivities,
        ];
    }

    public static function about(): array
    {
        $facilities = Facility::where('status', 'Active')->oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $rooms = Room::where('status', 'Active')->oldest()->get();
        $allRooms = Room::where('status', 'Active')->oldest()->get();
        $pageHero = PageHero::getBySlug('about');

        return [
            'facilities' => $facilities,
            'setting' => $setting,
            'about' => $about,
            'rooms' => $rooms,
            'allRooms' => $allRooms,
            'pageHero' => $pageHero,
        ];
    }

    public static function rooms(Request $request): array
    {
        $rooms = Room::with(['amenities', 'images'])
            ->where('status', 'Active')
            ->where('room_type', 'room')
            ->oldest()
            ->get();
        $setting = Setting::first();
        $about = About::first();
        $facilities = Facility::where('status', 'Active')->oldest()->get();
        $pageHero = PageHero::getBySlug('rooms');

        return [
            'rooms' => $rooms,
            'setting' => $setting,
            'about' => $about,
            'facilities' => $facilities,
            'pageHero' => $pageHero,
        ];
    }

    public static function apartments(Request $request): array
    {
        $rooms = Room::with(['amenities', 'images'])
            ->where('status', 'Active')
            ->where('room_type', 'room')
            ->oldest()
            ->get();
        $apartments = Room::with(['amenities', 'images'])
            ->where('status', 'Active')
            ->where('room_type', 'apartment')
            ->oldest()
            ->get();
        $setting = Setting::first();
        $about = About::first();
        $facilities = Facility::where('status', 'Active')->oldest()->get();
        $pageHero = PageHero::getBySlug('rooms');

        return [
            'rooms' => $rooms,
            'apartments' => $apartments,
            'setting' => $setting,
            'about' => $about,
            'facilities' => $facilities,
            'pageHero' => $pageHero,
            'activeType' => 'apartment',
        ];
    }

    public static function room(string $slug): array
    {
        $room = Room::with(['amenities', 'images'])->where('slug', $slug)->firstOrFail();
        $amenities = $room->amenities;
        $images = $room->images;
        $allRooms = Room::with('images')
            ->where('id', '!=', $room->id)
            ->where('status', 'Active')
            ->oldest()
            ->take(3)
            ->get();
        $setting = Setting::first();
        $about = About::first();

        return [
            'room' => $room,
            'images' => $images,
            'amenities' => $amenities,
            'allRooms' => $allRooms,
            'setting' => $setting,
            'about' => $about,
        ];
    }

    /** Default apartment showcase page (/apartment). */
    public static function apartmentLanding(): array
    {
        $room = Room::with(['amenities', 'images'])
            ->where('status', 'Active')
            ->where('room_type', 'apartment')
            ->oldest()
            ->firstOrFail();

        return self::room($room->slug);
    }

    public static function facilities(): array
    {
        $facilities = Facility::with('images')->oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('facilities');

        return [
            'facilities' => $facilities,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    /** Same data as facilities listing; hero uses slug `our-services` when configured in Page Heroes. */
    public static function ourServices(): array
    {
        $data = self::facilities();
        $hero = PageHero::getBySlug('our-services');
        if ($hero) {
            $data['pageHero'] = $hero;
        }

        return $data;
    }

    public static function exploreKibeho(): array
    {
        $kibehoPage = KibehoPage::current()->load('images');
        $facilities = Facility::oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('explore-kibeho');

        $sanctuaryEvents = \App\Models\SanctuaryEvent::query()
            ->with('images')
            ->active()
            ->ordered()
            ->get();

        $pageImages = collect();
        if ($kibehoPage->cover_image) {
            $pageImages->push((object) [
                'url' => asset('storage/' . $kibehoPage->cover_image),
                'caption' => $kibehoPage->title,
            ]);
        }
        foreach ($kibehoPage->images as $img) {
            $pageImages->push((object) [
                'url' => asset('storage/' . $img->image),
                'caption' => $img->caption ?: $kibehoPage->title,
            ]);
        }

        $galleryImages = Gallery::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(category) = ?', ['kibeho sanctuary'])
                    ->orWhereRaw('LOWER(category) = ?', ['sanctuary']);
            })
            ->where('media_type', 'image')
            ->oldest()
            ->get()
            ->map(fn ($item) => (object) [
                'url' => asset('storage/' . $item->image),
                'caption' => $item->caption ?: $item->category,
            ]);

        $allGalleryImages = $pageImages->concat($galleryImages)->unique('url')->values();

        return [
            'kibehoPage' => $kibehoPage,
            'facility' => $kibehoPage,
            'facilities' => $facilities,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
            'sanctuaryEvents' => $sanctuaryEvents,
            'sanctuaryGallery' => $allGalleryImages,
            'officialWebsiteUrl' => filled(trim((string) $kibehoPage->official_website_url))
                ? trim((string) $kibehoPage->official_website_url)
                : null,
        ];
    }

    public static function sanctuaryActivity(string $slug): array
    {
        $activity = SanctuaryEvent::with('images')
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();
        $setting = Setting::first();
        $about = About::first();
        $related = SanctuaryEvent::query()
            ->active()
            ->ordered()
            ->where('id', '!=', $activity->id)
            ->take(3)
            ->get();

        return [
            'activity' => $activity,
            'images' => $activity->images,
            'relatedActivities' => $related,
            'setting' => $setting,
            'about' => $about,
            'backRoute' => 'explore-kibeho',
            'translationPrefix' => 'sanctuary',
        ];
    }

    public static function gikongoroDiocese(): array
    {
        $diocesePage = GikongoroDiocesePage::current();
        $facilities = Facility::oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('discover-gikongoro-diocese');

        $dioceseStats = \App\Models\GikongoroDioceseStat::query()
            ->active()
            ->ordered()
            ->get();

        return [
            'diocesePage' => $diocesePage,
            'facilities' => $facilities,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
            'dioceseStats' => $dioceseStats,
        ];
    }

    public static function discoverNyaruguru(): array
    {
        $nyaruguruPage = NyaruguruPage::current()->load('images');
        $facilities = Facility::oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('discover-nyaruguru');

        $nyaruguruActivities = \App\Models\NyaruguruActivity::query()
            ->with('images')
            ->active()
            ->ordered()
            ->get();

        $pageImages = collect();
        if ($nyaruguruPage->cover_image) {
            $pageImages->push((object) [
                'url' => asset('storage/' . $nyaruguruPage->cover_image),
                'caption' => $nyaruguruPage->title,
            ]);
        }
        foreach ($nyaruguruPage->images as $img) {
            $pageImages->push((object) [
                'url' => asset('storage/' . $img->image),
                'caption' => $img->caption ?: $nyaruguruPage->title,
            ]);
        }

        $galleryImages = Gallery::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(category) = ?', ['nyaruguru'])
                    ->orWhereRaw('LOWER(category) = ?', ['discover nyaruguru']);
            })
            ->where('media_type', 'image')
            ->oldest()
            ->get()
            ->map(fn ($item) => (object) [
                'url' => asset('storage/' . $item->image),
                'caption' => $item->caption ?: $item->category,
            ]);

        $allGalleryImages = $pageImages->concat($galleryImages)->unique('url')->values();

        return [
            'nyaruguruPage' => $nyaruguruPage,
            'facilities' => $facilities,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
            'nyaruguruActivities' => $nyaruguruActivities,
            'nyaruguruGallery' => $allGalleryImages,
        ];
    }

    public static function nyaruguruActivity(string $slug): array
    {
        $activity = NyaruguruActivity::with('images')
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();
        $setting = Setting::first();
        $about = About::first();
        $related = NyaruguruActivity::query()
            ->active()
            ->ordered()
            ->where('id', '!=', $activity->id)
            ->take(3)
            ->get();

        return [
            'activity' => $activity,
            'images' => $activity->images,
            'relatedActivities' => $related,
            'setting' => $setting,
            'about' => $about,
            'backRoute' => 'discover-nyaruguru',
            'translationPrefix' => 'nyaruguru',
        ];
    }

    public static function facility(string $slug): array
    {
        $facility = Facility::with('images')->where('slug', $slug)->firstOrFail();
        $images = $facility->images;
        $allFacilities = Facility::where('id', '!=', $facility->id)->oldest()->get();
        $facilities = Facility::oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $gallery = Gallery::oldest()->paginate(9);

        return [
            'facility' => $facility,
            'images' => $images,
            'allFacilities' => $allFacilities,
            'facilities' => $facilities,
            'setting' => $setting,
            'about' => $about,
            'gallery' => $gallery,
        ];
    }

    public static function guesthouse(): array
    {
        $room = Room::with('amenities')->where('category', 'Kinigi')->first();
        $amenities = $room?->amenities ?? collect();
        $images = $room?->images ?? collect();
        $allRooms = $room
            ? Room::where('id', '!=', $room->id)->oldest()->get()
            : collect();
        $about = About::first();
        $setting = Setting::first();

        return [
            'room' => $room,
            'amenities' => $amenities,
            'allRooms' => $allRooms,
            'images' => $images,
            'about' => $about,
            'setting' => $setting,
        ];
    }

    public static function restaurant(): array
    {
        $restaurant = Restaurant::with(['images', 'cuisines'])->first();
        if (! $restaurant) {
            Restaurant::create([
                'title' => 'Dining',
                'description' => 'Discover our restaurant and bar.',
            ]);
            $restaurant = Restaurant::with(['images', 'cuisines'])->first();
        }
        $images = $restaurant->images ?? collect();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('dining');

        $cuisines = $restaurant->cuisines ?? collect();

        return [
            'restaurant' => $restaurant,
            'images' => $images,
            'cuisines' => $cuisines,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function promotions(): array
    {
        $promotions = Promotion::oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('promotions');

        return [
            'promotions' => $promotions,
            'about' => $about,
            'setting' => $setting,
            'pageHero' => $pageHero,
        ];
    }

    public static function events(): array
    {
        $event = Eventpage::with(['meetingRooms.images'])->first();
        if (! $event) {
            $event = Eventpage::create([
                'title' => 'Meetings & Events',
                'description' => 'Host your meetings and events with us.',
                'details' => '',
            ]);
            $event->load(['meetingRooms.images']);
        }
        MeetingRoom::ensureDefaultsForEventpage($event);
        $event->load(['meetingRooms.images']);

        $meetingRooms = $event->meetingRooms ?? collect();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('meetings-events');
        $whyChooseUsItems = WhyChooseUsItem::query()->orderBy('sort_order')->orderBy('id')->get();

        return [
            'event' => $event,
            'meetingRooms' => $meetingRooms,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
            'whyChooseUsItems' => $whyChooseUsItems,
        ];
    }

    public static function meetingRoomShow(string $slug): array
    {
        $room = MeetingRoom::with(['images', 'eventpage'])
            ->where('slug', $slug)
            ->firstOrFail();

        $otherMeetingRooms = MeetingRoom::query()
            ->where('eventpage_id', $room->eventpage_id)
            ->where('id', '!=', $room->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('meetings-events');

        return [
            'room' => $room,
            'otherMeetingRooms' => $otherMeetingRooms,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function spaWellness(): array
    {
        $spaImages = Gallery::where('media_type', 'image')
            ->where('category', 'spa')
            ->latest()
            ->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('spa-wellness');

        return [
            'spaImages' => $spaImages,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function activities(): array
    {
        $activities = TourActivity::query()->active()->ordered()->with('images')->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('activities');

        return [
            'activities' => $activities,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function activity(string $slug): array
    {
        $activity = TourActivity::with('images')->where('slug', $slug)->where('status', 'Active')->firstOrFail();
        $images = $activity->images;
        $allActivities = TourActivity::query()
            ->active()
            ->ordered()
            ->where('id', '!=', $activity->id)
            ->take(3)
            ->get();
        $setting = Setting::first();
        $about = About::first();

        return [
            'activity' => $activity,
            'images' => $images,
            'allActivities' => $allActivities,
            'setting' => $setting,
            'about' => $about,
        ];
    }

    /**
     * Static data for the public gallery page (images/videos load in batches via Livewire).
     */
    public static function galleryPageStatic(): array
    {
        return [
            'setting' => Setting::first(),
            'about' => About::first(),
            'pageHero' => PageHero::getBySlug('gallery'),
        ];
    }

    public static function contact(): array
    {
        $setting = Setting::first();
        $about = About::first();
        $hotelContact = HotelContact::first();
        $pageHero = PageHero::getBySlug('contact');

        return [
            'setting' => $setting,
            'about' => $about,
            'hotelContact' => $hotelContact,
            'pageHero' => $pageHero,
        ];
    }

    public static function bookNow(): array
    {
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('book-now');

        return [
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function reviews(): array
    {
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('reviews');

        return [
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function review(int|string $id): array
    {
        $review = Review::approved()->findOrFail($id);
        $reviews = Review::approved()->where('id', '!=', $id)->latest()->take(5)->get();
        $setting = Setting::first();
        $about = About::first();

        return [
            'review' => $review,
            'reviews' => $reviews,
            'setting' => $setting,
            'about' => $about,
        ];
    }

    public static function terms(): array
    {
        $rooms = Room::where('status', 'Active')->oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $terms = TermsCondition::where('status', 'active')->first();
        $pageHero = PageHero::getBySlug('terms');

        return [
            'setting' => $setting,
            'about' => $about,
            'rooms' => $rooms,
            'terms' => $terms,
            'pageHero' => $pageHero,
        ];
    }

    public static function tours(): array
    {
        $tours = Trip::oldest()->get();
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('tours');

        return [
            'tours' => $tours,
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function tour(string $slug): array
    {
        $tour = Trip::with('images')->where('slug', $slug)->firstOrFail();
        $images = $tour->images ?? collect();
        $tours = Trip::where('id', '!=', $tour->id)->oldest()->get();
        $allTrips = Trip::all();
        $setting = Setting::first();
        $about = About::first();

        return [
            'tour' => $tour,
            'images' => $images,
            'tours' => $tours,
            'allTrips' => $allTrips,
            'setting' => $setting,
            'about' => $about,
        ];
    }

    public static function updates(): array
    {
        $blogs = Blog::where('status', 'Published')->latest()->get();
        $rooms = Room::with('images')
            ->where('status', 'Active')
            ->oldest()
            ->get();
        $latestBlogs = Blog::where('status', 'Published')->latest()->paginate(10);
        $setting = Setting::first();
        $about = About::first();
        $categories = Category::with('blogs')->oldest()->get();
        $pageHero = PageHero::getBySlug('updates');

        return [
            'blogs' => $blogs,
            'rooms' => $rooms,
            'latestBlogs' => $latestBlogs,
            'setting' => $setting,
            'categories' => $categories,
            'about' => $about,
            'pageHero' => $pageHero,
        ];
    }

    public static function ourTeam(): array
    {
        $setting = Setting::first();
        $about = About::first();
        $pageHero = PageHero::getBySlug('our-team');
        $staff = StaffMember::query()->oldest()->get();

        return [
            'setting' => $setting,
            'about' => $about,
            'pageHero' => $pageHero,
            'staff' => $staff,
        ];
    }

    public static function blogPost(string $slug): array
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        $latestBlogs = Blog::where('status', 'Published')->where('id', '!=', $blog->id)->latest()->paginate(10);
        $setting = Setting::first();
        $programs = Program::oldest()->get();
        $about = About::first();
        $comments = BlogComment::where('status', 'Published')->latest()->get();
        $commentsCount = $comments->count();
        $relatedBlogs = Blog::where('id', '!=', $blog->id)
            ->where('status', 'Published')
            ->take(5)
            ->get();

        return [
            'blog' => $blog,
            'latestBlogs' => $latestBlogs,
            'comments' => $comments,
            'commentsCount' => $commentsCount,
            'setting' => $setting,
            'programs' => $programs,
            'relatedBlogs' => $relatedBlogs,
            'about' => $about,
        ];
    }
}
