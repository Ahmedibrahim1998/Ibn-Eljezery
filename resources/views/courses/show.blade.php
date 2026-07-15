@extends('layouts.app')

@section('title', $course->localized('title'))

@section('content')
  <section class="section-padding listing-page">
    <div class="container">

      {{-- Flash messages --}}
      @if (session('booking_success'))
        <div class="alert alert-success">{{ session('booking_success') }}</div>
      @endif
      @if (session('booking_error'))
        <div class="alert alert-warning">{{ session('booking_error') }}</div>
      @endif
      @if ($errors->any())
        <div class="alert alert-danger">
          @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
      @endif

      {{-- Hero header --}}
      <div class="detail-hero mb-4">
        <span class="detail-badge mb-3 d-inline-block">
          {{ $course->type->value === 'online' ? siteText('course.online') : siteText('course.offline') }}
        </span>
        <h1 class="h3 fw-bold mb-2">{{ $course->localized('title') }}</h1>
        @if ($course->teacher)
          <a href="{{ route('teachers.show', $course->teacher) }}" class="detail-meta text-white text-decoration-none">
            <span aria-hidden="true">👤</span> {{ $course->teacher->localized('name') }}
          </a>
        @endif
      </div>

      <div class="row g-4">
        {{-- Course info + schedule --}}
        <div class="col-lg-7">
          <div class="bg-white rounded-4 shadow-sm p-4 mb-4">
            <h2 class="section-title h5 mb-3">{{ siteText('course.details') }}</h2>
            @if ($course->durationLabel())
              <div class="detail-badge d-inline-flex align-items-center gap-2 mb-3">
                <span aria-hidden="true">🗓️</span>
                <span>{{ siteText('course.total_duration') }}: <strong>{{ $course->durationLabel() }}</strong></span>
              </div>
            @endif
            @if ($course->localized('description'))
              <p class="text-muted" style="line-height:2;">{{ $course->localized('description') }}</p>
            @endif
            @if (count($course->localizedItems()))
              <ul class="detail-list mt-3">
                @foreach ($course->localizedItems() as $line)
                  <li>{{ $line }}</li>
                @endforeach
              </ul>
            @endif
          </div>

          {{-- Schedule (read-only) --}}
          @if ($course->sessions->isNotEmpty())
            <div class="bg-white rounded-4 shadow-sm p-4">
              <h2 class="section-title h5 mb-3">{{ siteText('course.schedule_title') }}</h2>
              @foreach ($course->sessions as $session)
                <div class="session-card p-3 mb-2">
                  <div class="d-flex align-items-center gap-3">
                    <div class="session-date">
                      <div class="day">{{ $session->starts_at->format('d') }}</div>
                      <div class="mon">{{ $session->starts_at->translatedFormat('M') }}</div>
                    </div>
                    <div class="flex-grow-1">
                      <div class="fw-bold">{{ $session->starts_at->translatedFormat('l — g:i A') }}</div>
                      <div class="small text-muted">
                        <span>🕒 {{ $session->duration_minutes }} {{ siteText('course.minutes') }}</span>
                        @if (! $session->isOnline() && $session->localized('location'))
                          <span class="ms-2">📍 {{ $session->localized('location') }}</span>
                        @elseif ($session->isOnline())
                          <span class="ms-2">💻 {{ siteText('course.online') }}</span>
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @endif
        </div>

        {{-- Enrollment form --}}
        <div class="col-lg-5" id="enroll">
          <div class="bg-white rounded-4 shadow-sm p-4">
            <h2 class="section-title h5 mb-3">{{ siteText('course.enroll_title') }}</h2>
            @if ($course->enrollmentOpen())
              @if ($course->daysLeftLabel())
                <div class="alert alert-warning small">⏳ {{ siteText('course.hurry') }} — {{ $course->daysLeftLabel() }}</div>
              @endif
              @if ($course->type->value === 'online')
                <div class="alert alert-info small">{{ siteText('booking.online_note') }}</div>
              @endif
              <form action="{{ route('bookings.store') }}" method="POST" class="small ajax-form" data-reset="1">
                @csrf
                <input type="hidden" name="course_id" value="{{ $course->id }}" />
                <div class="form-message alert d-none small mb-3" role="alert"></div>
                <div class="mb-2">
                  <label class="form-label">{{ siteText('booking.name') }}</label>
                  <input type="text" name="name" class="form-control" value="{{ old('name') }}" required />
                </div>
                <div class="mb-2">
                  <label class="form-label">{{ siteText('booking.phone') }}</label>
                  <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" required />
                </div>
                <div class="mb-2">
                  <label class="form-label">{{ siteText('booking.email') }}</label>
                  <input type="email" name="email" class="form-control" value="{{ old('email') }}" />
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ siteText('booking.notes') }}</label>
                  <textarea name="notes" rows="2" class="form-control">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-main w-100">
                  <span class="btn-label">{{ siteText('course.enroll_now') }}</span>
                  <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
              </form>
            @else
              <div class="alert alert-secondary small mb-0">{{ siteText('course.enroll_closed') }}</div>
            @endif
          </div>

          <div class="mt-3 text-center">
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-sm">{{ siteText('buttons.back_home') }}</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
