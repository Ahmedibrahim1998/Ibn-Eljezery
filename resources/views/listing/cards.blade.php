@extends('layouts.app')

@section('title', $title)

@section('content')
  <section class="section-padding listing-page">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title mb-2">{{ $title }}</h2>
      </div>

      @if ($items->total() > 0)
        <div class="row g-4">
          @foreach ($items as $item)
            <div class="{{ $colClass }}">
              @include($cardPartial, ['item' => $item])
            </div>
          @endforeach
        </div>

        <div class="mt-5 d-flex justify-content-center">
          {{ $items->onEachSide(1)->links() }}
        </div>
      @else
        <p class="text-center text-muted">—</p>
      @endif

      <div class="text-center mt-3">
        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">{{ siteText('buttons.back_home') }}</a>
      </div>
    </div>
  </section>
@endsection
