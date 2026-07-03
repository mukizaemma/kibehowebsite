<div class="public-livewire-page explore-sanctuary-page explore-nyaruguru-page">
@include('frontend.partials.destination-explore-page', [
    'destinationPage' => $nyaruguruPage,
    'translationPrefix' => 'nyaruguru',
    'destinationActivities' => $nyaruguruActivities,
    'destinationGallery' => $nyaruguruGallery,
    'ctaType' => 'book',
    'pageHero' => $pageHero,
    'about' => $about,
])
</div>
