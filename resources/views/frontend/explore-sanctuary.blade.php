<div class="public-livewire-page explore-sanctuary-page">
@include('frontend.partials.destination-explore-page', [
    'destinationPage' => $kibehoPage,
    'translationPrefix' => 'sanctuary',
    'destinationActivities' => $sanctuaryEvents,
    'destinationGallery' => $sanctuaryGallery,
    'ctaType' => 'official',
    'officialWebsiteUrl' => $officialWebsiteUrl,
    'pageHero' => $pageHero,
    'about' => $about,
])
</div>
