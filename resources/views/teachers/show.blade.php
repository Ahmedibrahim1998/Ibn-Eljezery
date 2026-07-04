@extends('layouts.app')

@section('title', $teacher->localized('name'))

@section('content')
  <section class="section-padding listing-page">
    <div class="container">

      {{-- Hero header --}}
      <div class="detail-hero mb-4">
        <div class="detail-avatar">
          @if ($teacher->photo)
            <img src="{{ asset('storage/'.$teacher->photo) }}" alt="{{ $teacher->localized('name') }}" />
          @else
            <span>{{ mb_substr((string) $teacher->localized('name'), 0, 1) }}</span>
          @endif
        </div>
        <h1 class="h3 fw-bold mb-1">{{ $teacher->localized('name') }}</h1>
        @if ($teacher->localized('certification'))
          <p class="mb-2 opacity-75">{{ $teacher->localized('certification') }}</p>
        @endif
        @if ($teacher->localized('badge'))
          <span class="detail-badge">{{ $teacher->localized('badge') }}</span>
        @endif
      </div>

      <div class="row g-4">
        {{-- About the teacher --}}
        @if ($teacher->localized('description'))
          <div class="col-12">
            <div class="bg-white rounded-4 shadow-sm p-4">
              <h2 class="section-title h5 mb-3">{{ siteText('teacher.profile') }}</h2>
              <p class="mb-0 text-muted" style="line-height:2;">{{ $teacher->localized('description') }}</p>
            </div>
          </div>
        @endif

        {{-- Teacher's courses --}}
        <div class="col-12">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="section-title h4 mb-0">{{ siteText('teacher.courses_title') }}</h2>
            <span class="badge bg-main-subtle text-main">{{ $teacher->courses->count() }}</span>
          </div>

          @if ($teacher->courses->isNotEmpty())
            <div class="row g-4">
              @foreach ($teacher->courses as $course)
                <div class="col-md-6 col-lg-4">
                  @include('home.partials.course-card', ['item' => $course])
                </div>
              @endforeach
            </div>
          @else
            <div class="alert alert-light border text-center">{{ siteText('teacher.no_courses') }}</div>
          @endif
        </div>
      </div>

      <div class="mt-4 text-center">
        <a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary btn-sm">{{ siteText('buttons.back_home') }}</a>
      </div>
    </div>
  </section>
@endsection
