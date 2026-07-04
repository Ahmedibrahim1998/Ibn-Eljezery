{{-- Expects: $item (Teacher) --}}
<a href="{{ route('teachers.show', $item) }}"
   class="teacher-card teacher-card-link d-block text-decoration-none text-reset text-center p-3 shadow-sm h-100">
  <div class="teacher-avatar mx-auto mb-3">
    @if ($item->photo)
      <img src="{{ asset('storage/'.$item->photo) }}" alt="{{ $item->localized('name') }}" class="w-100 h-100 rounded-circle" style="object-fit:cover" />
    @else
      <span>{{ mb_substr((string) $item->localized('name'), 0, 1) }}</span>
    @endif
  </div>
  <h5 class="mb-1">{{ $item->localized('name') }}</h5>
  <p class="small text-muted mb-2">{{ $item->localized('certification') }}</p>
  <p class="small mb-2">{{ $item->localized('description') }}</p>
  @if ($item->localized('badge'))
    <span class="badge bg-main-subtle text-main small">{{ $item->localized('badge') }}</span>
  @endif
</a>
