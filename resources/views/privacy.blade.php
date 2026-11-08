@extends('layouts.app')

{{-- Privacy policy for the "ابن الجزري" mobile app (required by Google Play). Keep it in sync with
     what the app actually collects — see docs/mobile-app/07-GOOGLE-PLAY-PUBLISHING.md (Data safety). --}}
@php
  $locale = app()->getLocale();
  $updated = '2026-11-02';
  $email = setting('contact_email');
@endphp

@section('title', $locale === 'ar' ? 'سياسة الخصوصية — تطبيق ابن الجزري' : 'Privacy Policy — Ibn Al-Jazari App')

@section('content')
  <section class="section-padding">
    <div class="container" style="max-width: 820px">
      @if ($locale === 'ar')
        <h1 class="section-title mb-2">سياسة الخصوصية</h1>
        <p class="text-muted mb-4">تطبيق «ابن الجزري» — آخر تحديث: {{ $updated }}</p>

        <p>نحترم خصوصيتك. التطبيق مصمَّم ليعمل <strong>بدون حساب وبدون جمع أي بيانات شخصية</strong>.</p>

        <h2 class="h5 mt-4">ما الذي يُحفظ على جهازك فقط</h2>
        <ul>
          <li>آخر صفحة قرأتها، وعلامات الورد (التسميع والمراجعة والترتيل).</li>
          <li>إعداداتك: شكل التطبيق، وشكل المصحف، والقارئ، والمذهب المختار.</li>
          <li>صفحات المصحف والدروس التي حُمِّلت للقراءة بدون إنترنت.</li>
        </ul>
        <p>هذه البيانات لا تُرسَل إلينا، وتُحذف بحذف التطبيق.</p>

        <h2 class="h5 mt-4">الاتصال بالإنترنت</h2>
        <p>يتصل التطبيق بالإنترنت فقط لـ:</p>
        <ul>
          <li>جلب دروس الفقه من خادم الأكاديمية.</li>
          <li>تنزيل خطوط صفحات مصحف المدينة من خدمة Quran.com (static.qurancdn.com).</li>
          <li>تشغيل التلاوات من موقع EveryAyah.com.</li>
        </ul>
        <p>كأي اتصال بالإنترنت، تصل هذه الخوادم معلومات تقنية عادية مثل عنوان IP، ولا نستخدمها لتتبعك أو التعرف عليك.</p>

        <h2 class="h5 mt-4">ما لا نفعله</h2>
        <ul>
          <li>لا إعلانات.</li>
          <li>لا تتبع ولا أدوات تحليل.</li>
          <li>لا نطلب موقعك ولا جهات الاتصال ولا الصور.</li>
          <li>لا نبيع أو نشارك أي بيانات مع أي جهة.</li>
        </ul>

        <h2 class="h5 mt-4">الأطفال</h2>
        <p>التطبيق مناسب لجميع الأعمار، ولا يجمع بيانات من أي مستخدم بما في ذلك الأطفال.</p>

        <h2 class="h5 mt-4">التغييرات على هذه السياسة</h2>
        <p>عند إضافة ميزات جديدة (مثل تسجيل الدخول أو الإشعارات) سنحدّث هذه الصفحة قبل إطلاقها.</p>

        @if ($email)
          <h2 class="h5 mt-4">التواصل</h2>
          <p>لأي استفسار: <a href="mailto:{{ $email }}">{{ $email }}</a></p>
        @endif
      @else
        <h1 class="section-title mb-2">Privacy Policy</h1>
        <p class="text-muted mb-4">“Ibn Al-Jazari” app — last updated: {{ $updated }}</p>

        <p>We respect your privacy. The app is designed to work <strong>without an account and without collecting any personal data</strong>.</p>

        <h2 class="h5 mt-4">Stored on your device only</h2>
        <ul>
          <li>Your last read page and your wird marks (memorization, review, recitation).</li>
          <li>Your settings: theme, mushaf display, reciter and chosen madhhab.</li>
          <li>Mushaf pages and lessons downloaded for offline reading.</li>
        </ul>
        <p>This data is never sent to us and is removed when you uninstall the app.</p>

        <h2 class="h5 mt-4">Internet access</h2>
        <p>The app connects to the internet only to:</p>
        <ul>
          <li>Load fiqh lessons from the academy's server.</li>
          <li>Download Madani mushaf page fonts from Quran.com (static.qurancdn.com).</li>
          <li>Stream recitations from EveryAyah.com.</li>
        </ul>
        <p>Like any internet request, these servers receive standard technical information such as your IP address; we do not use it to track or identify you.</p>

        <h2 class="h5 mt-4">What we don't do</h2>
        <ul>
          <li>No ads.</li>
          <li>No tracking or analytics.</li>
          <li>No access to your location, contacts or photos.</li>
          <li>We never sell or share any data.</li>
        </ul>

        <h2 class="h5 mt-4">Children</h2>
        <p>The app is suitable for all ages and collects no data from any user, including children.</p>

        <h2 class="h5 mt-4">Changes</h2>
        <p>If we add features such as sign-in or notifications, we will update this page before releasing them.</p>

        @if ($email)
          <h2 class="h5 mt-4">Contact</h2>
          <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
        @endif
      @endif
    </div>
  </section>
@endsection
