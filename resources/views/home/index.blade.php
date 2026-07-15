@extends('layouts.app')

@php($locale = app()->getLocale())

@section('content')
  {{-- ===================== Hero ===================== --}}
  <section id="hero" class="hero d-flex align-items-center">
    <div class="overlay"></div>
    <div class="container position-relative">
      <div class="row align-items-center">
        <div class="col-lg-7 text-center text-lg-start text-white">
          <h1 class="display-5 fw-bold mb-3">{{ localizedSetting('hero_title') }}</h1>
          <p class="lead mb-4">{{ localizedSetting('hero_lead') }}</p>
          <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
            <a href="#programs" class="btn btn-main btn-lg px-4">{{ siteText('hero.cta_programs') }}</a>
            <a href="#contact" class="btn btn-outline-light btn-lg px-4">{{ siteText('hero.cta_contact') }}</a>
          </div>
          <div class="hero-stats mt-4 row g-3">
            @for ($i = 1; $i <= 3; $i++)
              <div class="col-6 col-md-4">
                <div class="stat-box">
                  <div class="stat-number">{{ setting("stat_{$i}_number") }}</div>
                  <div class="stat-label">{{ localizedSetting("stat_{$i}_label") }}</div>
                </div>
              </div>
            @endfor
          </div>
        </div>
        <div class="col-lg-5 d-none d-lg-block">
          <div class="hero-card shadow-lg">
            <h5 class="mb-3 text-center">{{ siteText('hero.form_title') }}</h5>
            <p class="small text-muted text-center mb-4">{{ siteText('hero.form_subtitle') }}</p>
            @include('home.partials.lead-form', ['source' => 'hero'])
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ===================== About ===================== --}}
  <section id="about" class="section-padding bg-light">
    <div class="container">
      <div class="row align-items-center g-4">
        <div class="col-lg-6">
          <div class="about-content">
            <h2 class="section-title mb-3">{{ siteText('sections.about_title') }}</h2>
            <p class="mb-3">{{ localizedSetting('about_p1') }}</p>
            <p class="mb-3">{{ localizedSetting('about_p2') }}</p>
            <ul class="list-unstyled about-list mt-3">
              @foreach (preg_split('/\r\n|\r|\n/', (string) localizedSetting('about_list')) as $item)
                @if (trim($item) !== '')<li>{{ $item }}</li>@endif
              @endforeach
            </ul>
          </div>
        </div>
        <div class="col-lg-5 offset-lg-1">
          <div class="about-box shadow-sm p-4 rounded-4 bg-white h-100">
            <h5 class="mb-3">{{ siteText('sections.features_title') }}</h5>
            <div class="row g-3">
              @foreach ($features as $feature)
                <div class="col-6">
                  <div class="feature-card">
                    <span class="feature-icon">{{ $feature->icon }}</span>
                    <h6>{{ $feature->localized('title') }}</h6>
                    <p class="small text-muted mb-0">{{ $feature->localized('description') }}</p>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ===================== Courses ===================== --}}
  <section id="courses" class="section-padding">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title mb-3">{{ siteText('sections.courses_title') }}</h2>
        <p class="text-muted">{{ siteText('sections.courses_lead') }}</p>
      </div>
      <div class="row g-4 justify-content-center">
        @foreach (['offline' => '🏛️', 'online' => '💻'] as $t => $icon)
          <div class="col-md-6 col-lg-5">
            <a href="{{ route('courses.type', $t) }}" class="text-decoration-none text-reset">
              <div class="pricing-card p-5 h-100 shadow-sm text-center">
                <div style="font-size:3rem;line-height:1">{{ $icon }}</div>
                <h3 class="h4 mt-3 mb-2">{{ siteText('course.' . $t . '_title') }}</h3>
                <p class="text-muted mb-3">{{ siteText('course.' . $t . '_desc') }}</p>
                <span class="btn btn-main px-4">{{ siteText('course.browse') }}</span>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>

  {{-- ===================== Programs ===================== --}}
  <section id="programs" class="section-padding bg-light">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title mb-3">{{ siteText('sections.programs_title') }}</h2>
        <p class="text-muted">{{ siteText('sections.programs_lead') }}</p>
      </div>
      <div class="row g-4">
        @foreach ($programs as $program)
          <div class="col-md-6 col-lg-4">
            @include('home.partials.program-card', ['item' => $program])
          </div>
        @endforeach
      </div>
      @if ($programsTotal > $programs->count())
        <div class="text-center mt-5">
          <a href="{{ route('programs.index') }}" class="btn btn-outline-main px-4">
            {{ siteText('buttons.view_all') }} ({{ $programsTotal }})
          </a>
        </div>
      @endif
    </div>
  </section>

  {{-- ===================== Teachers ===================== --}}
  <section id="teachers" class="section-padding">
    <div class="container">
      <div class="text-center mb-5 teachers-section">
        <h2 class="section-title mb-3">{{ siteText('sections.teachers_title') }}</h2>
        <p class="text-muted">{{ siteText('sections.teachers_lead') }}</p>
      </div>
      <div class="row g-4 teachers-section">
        @foreach ($teachers as $teacher)
          <div class="col-md-6 col-lg-3">
            @include('home.partials.teacher-card', ['item' => $teacher])
          </div>
        @endforeach
      </div>
      @if ($teachersTotal > $teachers->count())
        <div class="text-center mt-5">
          <a href="{{ route('teachers.index') }}" class="btn btn-outline-main px-4">
            {{ siteText('buttons.view_all') }} ({{ $teachersTotal }})
          </a>
        </div>
      @endif
    </div>
  </section>

  {{-- ===================== Memorization ===================== --}}
  <section id="memorization" class="section-padding">
    <div class="container">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <h2 class="mb-3">{{ siteText('sections.memorization_title') }}</h2>
          <p class="mb-3">{{ localizedSetting('memorization_p1') }}</p>
          <ol class="small mb-3 memorization-steps">
            @foreach (preg_split('/\r\n|\r|\n/', (string) localizedSetting('memorization_steps')) as $step)
              @if (trim($step) !== '')<li>{{ $step }}</li>@endif
            @endforeach
          </ol>
          <p class="mb-0">{{ localizedSetting('memorization_p2') }}</p>
        </div>
        <div class="col-lg-6">
          <div class="tracking-box shadow-sm p-4 rounded-4 bg-light h-100">
            <h5 class="mb-3">{{ siteText('sections.weekly_plan_title') }}</h5>
            <div class="table-responsive">
              <table class="table table-sm align-middle mb-0">
                <thead>
                  <tr>
                    <th>{{ siteText('sections.weekly_plan_day') }}</th>
                    <th>{{ siteText('sections.weekly_plan_new') }}</th>
                    <th>{{ siteText('sections.weekly_plan_review') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($weeklyPlan as $row)
                    <tr>
                      <td>{{ $row->localized('day') }}</td>
                      <td>{{ $row->localized('new_memorization') }}</td>
                      <td>{{ $row->localized('review') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- ===================== Testimonials ===================== --}}
  <section id="testimonials" class="section-padding bg-light">
    <div class="container">
      <div class="text-center mb-5 testimonials-section">
        <h2 class="section-title mb-3">{{ siteText('sections.testimonials_title') }}</h2>
        <p class="text-muted">{{ siteText('sections.testimonials_lead') }}</p>
      </div>
      <div class="row g-4 testimonials-section">
        @foreach ($testimonials as $testimonial)
          <div class="col-md-6 col-lg-4">
            @include('home.partials.testimonial-card', ['item' => $testimonial])
          </div>
        @endforeach
      </div>
      @if ($testimonialsTotal > $testimonials->count())
        <div class="text-center mt-5">
          <a href="{{ route('testimonials.index') }}" class="btn btn-outline-main px-4">
            {{ siteText('buttons.view_all') }} ({{ $testimonialsTotal }})
          </a>
        </div>
      @endif
    </div>
  </section>

  {{-- ===================== FAQ ===================== --}}
  <section id="faq" class="section-padding">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title mb-3">{{ siteText('sections.faq_title') }}</h2>
        <p class="text-muted">{{ siteText('sections.faq_lead') }}</p>
      </div>
      <div class="row g-4">
        <div class="col-lg-8 mx-auto">
          <div class="accordion" id="faqAccordion">
            @foreach ($faqs as $i => $faq)
              <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading{{ $i }}">
                  <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $i }}"
                    aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $i }}">
                    {{ $faq->localized('question') }}
                  </button>
                </h2>
                <div id="faqCollapse{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                  aria-labelledby="faqHeading{{ $i }}" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">{{ $faq->localized('answer') }}</div>
                </div>
              </div>
            @endforeach
          </div>
          @if ($faqsTotal > $faqs->count())
            <div class="text-center mt-4">
              <a href="{{ route('faqs.index') }}" class="btn btn-outline-main px-4">
                {{ siteText('buttons.view_all') }} ({{ $faqsTotal }})
              </a>
            </div>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- ===================== Contact ===================== --}}
  <section id="contact" class="section-padding bg-main-dark text-white">
    <div class="container">
      <div class="row g-4 align-items-center stagger">
        <div class="col-lg-6">
          <h2 class="section-title text-white mb-3">{{ siteText('sections.contact_title') }}</h2>
          <p class="mb-3">{{ siteText('sections.contact_lead') }}</p>
          <ul class="list-unstyled small mb-4 contact-info">
            <li>
              <strong>{{ siteText('contact.phone_label') }}</strong>
              <a href="{{ waUrl(setting('whatsapp_phone')) }}" target="_blank" rel="noopener" class="text-white text-decoration-underline">{{ setting('whatsapp_phone') }}</a>
            </li>
            <li>
              <strong>{{ siteText('contact.email_label') }}</strong>
              <a href="mailto:{{ setting('contact_email') }}" class="text-white text-decoration-underline">{{ setting('contact_email') }}</a>
            </li>
            <li><strong>{{ siteText('contact.location_label') }}</strong> {{ localizedSetting('contact_location') }}</li>
          </ul>
          <a href="{{ waUrl(setting('whatsapp_phone')) }}" target="_blank" rel="noopener" class="btn btn-whatsapp mb-4" aria-label="{{ siteText('contact.whatsapp') }}">
            <span class="whatsapp-icon" aria-hidden="true">
              <svg viewBox="0 0 32 32" width="20" height="20" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M19.11 17.37c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.15-.42-2.19-1.35-.81-.72-1.36-1.6-1.52-1.88-.16-.27-.02-.42.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.83-2.01-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.3 0 1.36.99 2.67 1.13 2.85.14.18 1.95 2.98 4.73 4.18.66.28 1.17.45 1.57.57.66.21 1.26.18 1.74.11.53-.08 1.6-.65 1.83-1.27.23-.62.23-1.15.16-1.27-.07-.12-.25-.2-.52-.34z"/>
                <path d="M16.03 3.2c-7.07 0-12.83 5.7-12.83 12.71 0 2.24.59 4.42 1.71 6.34L3 29l6.95-1.82c1.86 1 3.96 1.53 6.08 1.53h.01c7.07 0 12.83-5.7 12.83-12.71S23.1 3.2 16.03 3.2zm0 23.28h-.01c-1.9 0-3.77-.51-5.4-1.49l-.39-.23-4.12 1.08 1.1-4.01-.25-.4a10.54 10.54 0 0 1-1.62-5.56c0-5.8 4.78-10.52 10.69-10.52 5.91 0 10.69 4.72 10.69 10.52S21.94 26.48 16.03 26.48z"/>
              </svg>
            </span>
            <span>{{ siteText('contact.whatsapp') }}</span>
          </a>
        </div>
        <div class="col-lg-6">
          <div class="contact-card bg-white text-dark p-4 rounded-4 shadow-sm">
            <h5 class="mb-1">{{ siteText('review.title') }}</h5>
            <p class="small text-muted mb-3">{{ siteText('review.subtitle') }}</p>
            @include('home.partials.review-form')
          </div>
        </div>
      </div>
    </div>
  </section>

  @include('home.partials.package-modal')
@endsection
