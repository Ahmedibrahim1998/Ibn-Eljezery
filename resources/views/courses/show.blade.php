@extends('layouts.app')

@section('title', $course->localized('title'))

@section('content')
  <section class="section-padding listing-page">
    <div class="container">

      {{-- Flash messages --}}
      @if (session('booking_success'))
        <div class="alert alert-success">
          {{ session('booking_success') }}
          @if (session('booking_join_url'))
            <div class="mt-2">
              <strong>{{ siteText('booking.zoom_link') }}:</strong>
              <a href="{{ session('booking_join_url') }}" target="_blank" class="alert-link">{{ session('booking_join_url') }}</a>
            </div>
          @endif
        </div>
      @endif
      @if (session('booking_error'))
        <div class="alert alert-warning">{{ session('booking_error') }}</div>
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
        {{-- Course info --}}
        <div class="col-lg-5">
          <div class="bg-white rounded-4 shadow-sm p-4 h-100">
            <h2 class="section-title h5 mb-3">{{ siteText('course.details') }}</h2>
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
            @if ($course->type->value === 'online')
              <div class="alert alert-info small mt-3 mb-0">{{ siteText('booking.online_note') }}</div>
            @endif
          </div>
        </div>

        {{-- Sessions + booking --}}
        <div class="col-lg-7" id="sessions">
          <h2 class="section-title h5 mb-3">{{ siteText('course.sessions_title') }}</h2>

          @forelse ($course->sessions as $session)
            <div class="session-card p-3 mb-3">
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
                <div class="text-end">
                  @if ($session->seatsLeft() === null)
                    <span class="badge bg-secondary">{{ siteText('course.unlimited_seats') }}</span>
                  @elseif ($session->isFull())
                    <span class="badge bg-danger">{{ siteText('course.full') }}</span>
                  @else
                    <span class="badge bg-success">{{ $session->seatsLeft() }} {{ siteText('course.seats_left') }}</span>
                  @endif
                </div>
              </div>

              @if (! $session->isFull())
                <div class="mt-3">
                  <button class="btn btn-sm btn-main" type="button" data-bs-toggle="collapse"
                    data-bs-target="#book{{ $session->id }}" aria-expanded="false">
                    {{ siteText('course.book_now') }}
                  </button>
                  <div class="collapse mt-3" id="book{{ $session->id }}">
                    <form action="{{ route('bookings.store') }}" method="POST" class="small border-top pt-3">
                      @csrf
                      <input type="hidden" name="course_session_id" value="{{ $session->id }}" />
                      <div class="row g-2">
                        <div class="col-md-6">
                          <label class="form-label">{{ siteText('booking.name') }}</label>
                          <input type="text" name="name" class="form-control form-control-sm" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">{{ siteText('booking.phone') }}</label>
                          <input type="tel" name="phone" class="form-control form-control-sm" required />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">{{ siteText('booking.email') }}</label>
                          <input type="email" name="email" class="form-control form-control-sm" />
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">{{ siteText('booking.notes') }}</label>
                          <input type="text" name="notes" class="form-control form-control-sm" />
                        </div>
                      </div>
                      <button type="submit" class="btn btn-main btn-sm mt-3">{{ siteText('booking.submit') }}</button>
                    </form>
                  </div>
                </div>
              @endif
            </div>
          @empty
            <div class="alert alert-light border">{{ siteText('course.no_sessions') }}</div>
          @endforelse

          <div class="mt-4">
            <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-sm">{{ siteText('buttons.back_home') }}</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
