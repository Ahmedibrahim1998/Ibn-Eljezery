{{-- Customer review form — feeds the testimonials table (pending approval). --}}
<form action="{{ route('testimonials.store') }}" method="POST" class="small ajax-form" data-reset="1">
  @csrf

  <div class="form-message alert d-none small mb-3" role="alert"></div>

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">{{ siteText('review.name') }}</label>
      <input type="text" name="name" class="form-control" required />
    </div>
    <div class="col-md-6">
      <label class="form-label">{{ siteText('review.role') }}</label>
      <input type="text" name="role" class="form-control" placeholder="{{ siteText('review.role_placeholder') }}" />
    </div>
    <div class="col-12">
      <label class="form-label">{{ siteText('review.body') }}</label>
      <textarea name="body" rows="3" class="form-control" required></textarea>
    </div>
  </div>
  <button type="submit" class="btn btn-main w-100 mt-3">
    <span class="btn-label">{{ siteText('review.submit') }}</span>
    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  </button>
</form>
