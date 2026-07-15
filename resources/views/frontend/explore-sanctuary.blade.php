<div class="public-livewire-page explore-sanctuary-page">
@include('frontend.partials.destination-explore-page', [
    'destinationPage' => $kibehoPage,
    'translationPrefix' => 'sanctuary',
    'destinationActivities' => $sanctuaryEvents,
    'destinationGallery' => $sanctuaryGallery,
    'activityDetailRoute' => 'explore-kibeho.activity',
    'ctaType' => 'official',
    'officialWebsiteUrl' => $officialWebsiteUrl,
    'pageHero' => $pageHero,
    'about' => $about,
])
</div>
