{{-- Package enrollment modal — opened by the "choose package" buttons.
     Submits a Lead (source=contact) with the chosen package name. --}}
<div class="modal fade" id="packageModal" tabindex="-1" aria-labelledby="packageModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="packageModalTitle">{{ siteText('package_modal.title') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ siteText('package_modal.close') }}"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">
          {{ siteText('package_modal.subtitle') }}
          <strong id="packageModalName" class="text-main"></strong>
        </p>

        <form action="{{ route('leads.store') }}" method="POST" class="small ajax-form" data-reset="1">
          @csrf
          <input type="hidden" name="source" value="contact" />
          <input type="hidden" name="program" id="packageModalProgram" value="" />

          <div class="form-message alert d-none small mb-3" role="alert"></div>

          <div class="mb-2">
            <label class="form-label">{{ siteText('booking.name') }}</label>
            <input type="text" name="name" class="form-control" required />
          </div>
          <div class="mb-2">
            <label class="form-label">{{ siteText('booking.phone') }}</label>
            <input type="tel" name="phone" class="form-control" required />
          </div>
          <div class="mb-2">
            <label class="form-label">{{ siteText('booking.email') }}</label>
            <input type="email" name="email" class="form-control" />
          </div>
          <div class="mb-3">
            <label class="form-label">{{ siteText('booking.notes') }}</label>
            <textarea name="message" rows="2" class="form-control"></textarea>
          </div>
          <button type="submit" class="btn btn-main w-100">
            <span class="btn-label">{{ siteText('package_modal.submit') }}</span>
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
