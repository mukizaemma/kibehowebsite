@if(translations_enabled())
@php
    $switcherClass = trim('site-lang-switcher ' . ($variant ?? ''));
@endphp
<div class="{{ $switcherClass }} d-flex align-items-center gap-1" aria-label="{{ site_trans('common.language') }}">
    <a href="{{ locale_switch_url('en') }}"
       class="site-lang-switcher__btn {{ site_locale() === 'en' ? 'is-active' : '' }}"
       hreflang="en"
       lang="en">EN</a>
    <span class="site-lang-switcher__sep" aria-hidden="true">|</span>
    <a href="{{ locale_switch_url('fr') }}"
       class="site-lang-switcher__btn {{ site_locale() === 'fr' ? 'is-active' : '' }}"
       hreflang="fr"
       lang="fr">FR</a>
</div>
@endif
