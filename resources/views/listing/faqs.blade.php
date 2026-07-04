@extends('layouts.app')

@section('title', $title)

@section('content')
  <section class="section-padding listing-page">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title mb-2">{{ $title }}</h2>
      </div>

      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="accordion" id="faqAccordion">
            @foreach ($items as $i => $faq)
              <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading{{ $i }}">
                  <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faqCollapse{{ $i }}"
                    aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faqCollapse{{ $i }}">
                    {{ $faq->localized('question') }}
                  </button>
                </h2>
                <div id="faqCollapse{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}"
                  aria-labelledby="faqHeading{{ $i }}" data-bs-parent="#faqAccordion">
                  <div class="accordion-body">{{ $faq->localized('answer') }}</div>
                </div>
              </div>
            @endforeach
          </div>

          <div class="mt-5 d-flex justify-content-center">
            {{ $items->onEachSide(1)->links() }}
          </div>

          <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">{{ siteText('buttons.back_home') }}</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
