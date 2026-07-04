{{-- Expects: $item (Testimonial) --}}
<div class="testimonial-card p-4 shadow-sm h-100">
  <p class="mb-3">"{{ $item->localized('body') }}"</p>
  <h6 class="mb-0">{{ $item->localized('author_name') }}</h6>
  <span class="small text-muted">{{ $item->localized('author_role') }}</span>
</div>
