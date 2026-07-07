{{-- Expects: $item (Course) --}}
<div class="pricing-card p-4 h-100 shadow-sm">
  @if ($item->daysLeftLabel())
    <span class="badge bg-danger mb-2 d-inline-flex align-items-center gap-1">
      <span aria-hidden="true">⏳</span> {{ siteText('course.hurry') }} — {{ $item->daysLeftLabel() }}
    </span>
  @endif
  @if ($item->localized('badge'))
    <span class="badge {{ $item->type->value === 'online' ? 'bg-main' : 'bg-main-subtle text-main' }} mb-2">{{ $item->localized('badge') }}</span>
  @endif
  <h5 class="mb-2">{{ $item->localized('title') }}</h5>
  <p class="text-muted small mb-3">{{ $item->localized('description') }}</p>
  <ul class="small mb-3">
    @foreach ($item->localizedItems() as $line)
      <li>{{ $line }}</li>
    @endforeach
  </ul>
  <a href="{{ route('courses.show', $item) }}" class="btn {{ $item->type->value === 'online' ? 'btn-main' : 'btn-outline-main' }} btn-sm">
    {{ siteText('course.details') }}
  </a>
</div>
