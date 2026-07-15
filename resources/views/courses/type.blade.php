@extends('layouts.app')

@section('title', siteText('course.' . $type->value . '_title'))

@section('content')
  <section class="section-padding listing-page">
    <div class="container">
      <div class="text-center mb-5">
        <span class="badge {{ $type->value === 'online' ? 'bg-main' : 'bg-main-subtle text-main' }}">
          {{ $type->value === 'online' ? siteText('course.online') : siteText('course.offline') }}
        </span>
        <h1 class="section-title mt-2 mb-3">{{ siteText('course.' . $type->value . '_title') }}</h1>
      </div>

      @if ($categories->isEmpty())
        <p class="text-center text-muted">{{ siteText('course.no_courses') }}</p>
      @else
        <div class="row g-4">
          @foreach ($categories as $cat)
            <div class="col-md-6 col-lg-4">
              <a href="{{ route('courses.category', $cat) }}" class="text-decoration-none text-reset">
                <div class="pricing-card p-4 h-100 shadow-sm">
                  <h5 class="mb-2">{{ $cat->localized('title') }}</h5>
                  @if ($cat->localized('description'))
                    <p class="text-muted small mb-3">{{ $cat->localized('description') }}</p>
                  @endif
                  <span class="badge bg-main-subtle text-main">{{ $cat->activeGroupsCount() }} {{ siteText('course.groups_count') }}</span>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      @endif

      <div class="text-center mt-5">
        <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary btn-sm">{{ siteText('course.back_types') }}</a>
      </div>
    </div>
  </section>
@endsection
