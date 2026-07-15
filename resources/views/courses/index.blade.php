@extends('layouts.app')

@section('title', siteText('sections.courses_title'))

@section('content')
  <section class="section-padding listing-page">
    <div class="container">
      <div class="text-center mb-5">
        <h1 class="section-title mb-3">{{ siteText('sections.courses_title') }}</h1>
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
                <span class="badge bg-main-subtle text-main mb-3">{{ $counts[$t] ?? 0 }} {{ siteText('course.courses_count') }}</span>
                <div><span class="btn btn-main px-4">{{ siteText('course.browse') }}</span></div>
              </div>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@endsection
