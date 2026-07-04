@php($isHero = ($source ?? 'contact') === 'hero')

@if (session('lead_success') && old('source') === $source)
  <div class="alert alert-success small">{{ session('lead_success') }}</div>
@endif

<form action="{{ route('leads.store') }}" method="POST" class="small">
  @csrf
  <input type="hidden" name="source" value="{{ $source }}" />

  @if ($isHero)
    {{-- ===== Hero form ===== --}}
    <div class="mb-3">
      <label class="form-label">{{ siteText('hero.form_name') }}</label>
      <input type="text" name="name" class="form-control" value="{{ old('source') === $source ? old('name') : '' }}" required />
    </div>
    <div class="mb-3">
      <label class="form-label">{{ siteText('hero.form_phone') }}</label>
      <input type="tel" name="phone" class="form-control" value="{{ old('source') === $source ? old('phone') : '' }}" required />
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
    <button type="submit" class="btn btn-main w-100">{{ siteText('hero.form_submit') }}</button>
  @else
    {{-- ===== Contact form ===== --}}
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">{{ siteText('contact.form_name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ old('source') === $source ? old('name') : '' }}" required />
      </div>
      <div class="col-md-6">
        <label class="form-label">{{ siteText('contact.form_phone') }}</label>
        <input type="tel" name="phone" class="form-control" value="{{ old('source') === $source ? old('phone') : '' }}" required />
      </div>
      <div class="col-md-6">
        <label class="form-label">{{ siteText('contact.form_email') }}</label>
        <input type="email" name="email" class="form-control" value="{{ old('source') === $source ? old('email') : '' }}" />
      </div>
      <div class="col-md-6">
        <label class="form-label">{{ siteText('contact.form_program') }}</label>
        <select name="program" id="packageSelect" class="form-select">
          <option value="">{{ siteText('contact.form_program_placeholder') }}</option>
          @foreach (($programs ?? collect()) as $program)
            <option>{{ $program->localized('title') }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">{{ siteText('contact.form_message') }}</label>
        <textarea name="message" rows="3" class="form-control">{{ old('source') === $source ? old('message') : '' }}</textarea>
      </div>
    </div>
    <button type="submit" class="btn btn-main w-100 mt-3">{{ siteText('contact.form_submit') }}</button>
  @endif

  @if ($errors->any() && old('source') === $source)
    <div class="text-danger small mt-2">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif
</form>
