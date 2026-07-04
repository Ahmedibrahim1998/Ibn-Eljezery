@php($locale = app()->getLocale())
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', localizedSetting('brand_name', config('app.name')))</title>

  {{-- Bootstrap CSS (RTL/LTR by locale) --}}
  @if ($locale === 'ar')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" />
  @else
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  @endif

  {{-- Google Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet" />

  {{-- Custom CSS --}}
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" />
  <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}" />
</head>
<body>
  {{-- Navbar --}}
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="#hero">
        <img src="{{ asset('assets/LOGO/logo.png') }}" alt="{{ localizedSetting('brand_name') }}" class="logo-img me-2" />
        <span>{{ localizedSetting('brand_name', config('app.name')) }}</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
        aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="#hero">{{ siteText('nav.home') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">{{ siteText('nav.about') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#teachers">{{ siteText('nav.teachers') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#programs">{{ siteText('nav.programs') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#courses">{{ siteText('nav.courses') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#memorization">{{ siteText('nav.memorization') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#testimonials">{{ siteText('nav.testimonials') }}</a></li>
          <li class="nav-item"><a class="nav-link" href="#faq">{{ siteText('nav.faq') }}</a></li>
          <li class="nav-item d-flex align-items-center">
            <a class="btn btn-sm btn-outline-light ms-lg-2 mt-2 mt-lg-0"
               href="{{ route('locale.switch', $locale === 'ar' ? 'en' : 'ar') }}">
              {{ $locale === 'ar' ? 'EN' : 'ع' }}
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link btn btn-outline-light px-3 py-1 ms-lg-2 mt-2 mt-lg-0" href="#contact">
              {{ siteText('nav.signup') }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  @yield('content')

  {{-- Footer --}}
  <footer class="py-3 bg-dark text-white-50 small">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
      <span>&copy; {{ date('Y') }} {{ localizedSetting('brand_name') }} - {{ siteText('footer.rights') }}</span>
      <span>{{ siteText('footer.design') }}</span>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
