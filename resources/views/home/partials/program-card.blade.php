{{-- Expects: $item (Program) --}}
<div class="pricing-card text-center p-4 h-100 {{ $item->is_featured ? 'shadow-lg featured' : 'shadow-sm' }}">
  @if ($item->localized('badge'))
    <span class="badge bg-main mb-2">{{ $item->localized('badge') }}</span>
  @endif
  <h5 class="mb-2">{{ $item->localized('title') }}</h5>
  <p class="text-muted small mb-3">{{ $item->localized('subtitle') }}</p>
  <div class="price mb-3">
    <span class="price-amount">{{ rtrim(rtrim(number_format((float) $item->price, 2), '0'), '.') }}</span>
    <span class="price-currency">{{ $item->localized('currency') }}</span>
  </div>
  <ul class="list-unstyled text-start small mb-4">
    @foreach ($item->localizedFeatures() as $line)
      <li>{{ $line }}</li>
    @endforeach
  </ul>
  <a href="{{ route('home') }}#contact" class="btn {{ $item->is_featured ? 'btn-main' : 'btn-outline-main' }} w-100 choose-package-btn"
     data-package="{{ $item->localized('title') }}">
    {{ siteText('programs.choose') }}
  </a>
</div>
