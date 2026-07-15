@extends('layouts.app')

@section('title', $category->localized('title'))

@section('content')
  <section class="section-padding listing-page">
    <div class="container">

      <div class="detail-hero mb-4">
        <span class="detail-badge mb-2 d-inline-block">
          {{ $category->type->value === 'online' ? siteText('course.online') : siteText('course.offline') }}
        </span>
        <h1 class="h3 fw-bold mb-2">{{ $category->localized('title') }}</h1>
        @if ($category->localized('description'))
          <p class="mb-0" style="opacity:.9;">{{ $category->localized('description') }}</p>
        @endif
      </div>

      <h2 class="section-title h5 mb-3">{{ siteText('course.groups_title') }}</h2>

      @if ($groups->isEmpty())
        <p class="text-muted">{{ siteText('course.no_groups') }}</p>
      @else
        <div class="row g-4">
          @foreach ($groups as $group)
            <div class="col-md-6">
              <div class="pricing-card p-4 h-100 shadow-sm">
                @if ($group->daysLeftLabel())
                  <span class="badge bg-danger mb-2 d-inline-flex align-items-center gap-1">
                    <span aria-hidden="true">⏳</span> {{ siteText('course.hurry') }} — {{ $group->daysLeftLabel() }}
                  </span>
                @endif
                <h5 class="mb-1 d-flex align-items-center gap-1">
                  <span aria-hidden="true">👤</span>
                  {{ $group->teacher?->localized('name') ?? $group->localized('title') }}
                </h5>
                @if ($group->durationLabel())
                  <div class="text-muted small mb-2">🗓️ {{ siteText('course.total_duration') }}: {{ $group->durationLabel() }}</div>
                @endif
                @if ($group->localized('description'))
                  <p class="text-muted small mb-3">{{ $group->localized('description') }}</p>
                @endif
                <ul class="small mb-3">
                  @foreach ($group->localizedItems() as $line)
                    <li>{{ $line }}</li>
                  @endforeach
                </ul>
                <a href="{{ route('courses.show', $group) }}" class="btn btn-main btn-sm">{{ siteText('course.group_details') }}</a>
              </div>
            </div>
          @endforeach
        </div>
      @endif

      <div class="text-center mt-5">
        <a href="{{ route('courses.type', $category->type->value) }}" class="btn btn-outline-secondary btn-sm">{{ siteText('course.back_courses') }}</a>
      </div>
    </div>
  </section>
@endsection
