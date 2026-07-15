{{-- Hero registration form (source = "hero"). Submits via AJAX. --}}
<form action="{{ route('leads.store') }}" method="POST" class="small ajax-form" data-reset="1">
  @csrf
  <input type="hidden" name="source" value="hero" />

  <div class="form-message alert d-none small mb-3" role="alert"></div>

  <div class="mb-3">
    <label class="form-label">{{ siteText('hero.form_name') }}</label>
    <input type="text" name="name" class="form-control" required />
  </div>
  <div class="mb-3">
    <label class="form-label">{{ siteText('hero.form_phone') }}</label>
    <input type="tel" name="phone" class="form-control" required />
  </div>
  <div class="mb-3">
    <label class="form-label">{{ siteText('hero.form_course') }}</label>
    <select name="course_id" class="form-select" required>
      <option value="" disabled selected>{{ siteText('hero.form_course_placeholder') }}</option>
      @foreach (($formCourses ?? collect()) as $course)
        <option value="{{ $course->id }}">
          {{ $course->localized('title') }}
          @if ($course->teacher) — {{ $course->teacher->localized('name') }} @endif
        </option>
      @endforeach
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">{{ siteText('hero.form_age') }}</label>
    <select name="age_group" class="form-select">
      <option value="">{{ siteText('hero.form_age_placeholder') }}</option>
      <option>6 - 9</option>
      <option>10 - 14</option>
      <option>15 - 18</option>
      <option>18+</option>
    </select>
  </div>
  <div class="mb-3">
    <label class="form-label">{{ siteText('hero.form_level') }}</label>
    <select name="level" class="form-select">
      <option value="">{{ siteText('hero.form_level_placeholder') }}</option>
      <option>{{ app()->getLocale() === 'ar' ? 'مبتدئ' : 'Beginner' }}</option>
      <option>{{ app()->getLocale() === 'ar' ? 'أقل من 5 أجزاء' : 'Less than 5 parts' }}</option>
      <option>{{ app()->getLocale() === 'ar' ? 'من 5 إلى 15 جزءًا' : '5 to 15 parts' }}</option>
      <option>{{ app()->getLocale() === 'ar' ? 'أكثر من 15 جزءًا' : 'More than 15 parts' }}</option>
    </select>
  </div>
  <button type="submit" class="btn btn-main w-100">
    <span class="btn-label">{{ siteText('hero.form_submit') }}</span>
    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
  </button>
</form>
