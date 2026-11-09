@extends('layouts.app')

{{-- Privacy policy for the "ابن الجزري" mobile app (required by Google Play). Keep it in sync with
     what the app actually collects (Google Play "Data safety" form). --}}
@php
  $locale = app()->getLocale();
  $updated = '2026-11-09';
  $email = setting('contact_email');
@endphp

@section('title', $locale === 'ar' ? 'سياسة الخصوصية — تطبيق ابن الجزري' : 'Privacy Policy — Ibn Al-Jazari App')

@section('content')
  <section class="section-padding">
    <div class="container" style="max-width: 820px">
      @if ($locale === 'ar')
        <h1 class="section-title mb-2">سياسة الخصوصية</h1>
        <p class="text-muted mb-4">تطبيق «ابن الجزري» — آخر تحديث: {{ $updated }}</p>

        <p>نحترم خصوصيتك. المصحف والورد وكل أدوات القراءة تعمل <strong>بدون حساب</strong>. الحساب مطلوب فقط لمن يريد حجز دورات الأكاديمية أو للمعلمين.</p>

        <h2 class="h5 mt-4">ما الذي يُحفظ على جهازك فقط</h2>
        <ul>
          <li>آخر صفحة قرأتها، وعلامات الورد، وسجل الحفظ والمراجعة، وخطة الختمة.</li>
          <li>إعداداتك: شكل التطبيق، وشكل المصحف، والقارئ، وموعد التذكير اليومي.</li>
          <li>صفحات المصحف والتلاوات التي حُمِّلت للاستخدام بدون إنترنت.</li>
        </ul>
        <p>هذه البيانات لا تُرسَل إلينا، وتُحذف بحذف التطبيق.</p>

        <h2 class="h5 mt-4">حساب الأكاديمية (اختياري)</h2>
        <p>عند إنشاء حساب طالب نجمع: <strong>الاسم ورقم الهاتف وكلمة المرور</strong> (مخزّنة مشفّرة). ويسجّل المعلمون الدخول بحساباتهم في الأكاديمية. نستخدم هذه البيانات فقط من أجل:</p>
        <ul>
          <li>تسجيل الدخول وحجز الدورات وعرض حجوزاتك.</li>
          <li>تمكين المعلم والأكاديمية من التواصل معك بخصوص الدورة التي حجزتها.</li>
        </ul>
        <p>يُحفَظ رمز الدخول على جهازك في التخزين الآمن. ويمكنك <strong>مسح حسابك نهائيًا</strong> في أي وقت من: الإعدادات ← مسح الحساب، فتُحذف بياناتك من خوادمنا.</p>

        <h2 class="h5 mt-4">الاتصال بالإنترنت</h2>
        <p>يتصل التطبيق بالإنترنت فقط لـ:</p>
        <ul>
          <li>عرض دورات الأكاديمية والمعلمين وإتمام الحجز من خادم الأكاديمية.</li>
          <li>تنزيل خطوط صفحات مصحف المدينة من خدمة Quran.com (static.qurancdn.com).</li>
          <li>تشغيل التلاوات من موقع EveryAyah.com.</li>
        </ul>
        <p>وعند ضغطك على رابط حصة زووم أو زر واتساب يُفتح التطبيق الخاص بهما، وتخضع حينها لسياسة خصوصيتهما.</p>
        <p>كأي اتصال بالإنترنت، تصل هذه الخوادم معلومات تقنية عادية مثل عنوان IP، ولا نستخدمها لتتبعك أو التعرف عليك.</p>

        <h2 class="h5 mt-4">التنبيهات</h2>
        <p>التذكير اليومي بالورد يُجدوَل على جهازك فقط، ويمكنك إيقافه من الإعدادات.</p>

        <h2 class="h5 mt-4">ما لا نفعله</h2>
        <ul>
          <li>لا إعلانات.</li>
          <li>لا تتبع ولا أدوات تحليل.</li>
          <li>لا نطلب موقعك ولا جهات الاتصال ولا الصور.</li>
          <li>لا نبيع أو نشارك أي بيانات مع أي جهة.</li>
        </ul>

        <h2 class="h5 mt-4">الأطفال</h2>
        <p>التطبيق مناسب لجميع الأعمار. ويُستحسن أن ينشئ ولي الأمر حساب الأكاديمية لأبنائه الصغار.</p>

        <h2 class="h5 mt-4">التغييرات على هذه السياسة</h2>
        <p>عند إضافة ميزات تؤثر على بياناتك سنحدّث هذه الصفحة قبل إطلاقها.</p>

        @if ($email)
          <h2 class="h5 mt-4">التواصل</h2>
          <p>لأي استفسار أو لطلب حذف بياناتك: <a href="mailto:{{ $email }}">{{ $email }}</a></p>
        @endif
      @else
        <h1 class="section-title mb-2">Privacy Policy</h1>
        <p class="text-muted mb-4">“Ibn Al-Jazari” app — last updated: {{ $updated }}</p>

        <p>We respect your privacy. The mushaf, wird and all reading tools work <strong>without an account</strong>. An account is only needed to book academy courses, or for teachers.</p>

        <h2 class="h5 mt-4">Stored on your device only</h2>
        <ul>
          <li>Your last read page, wird marks, memorization log and khatma plan.</li>
          <li>Your settings: theme, mushaf display, reciter and daily reminder time.</li>
          <li>Mushaf pages and recitations downloaded for offline use.</li>
        </ul>
        <p>This data is never sent to us and is removed when you uninstall the app.</p>

        <h2 class="h5 mt-4">Academy account (optional)</h2>
        <p>When you create a student account we collect your <strong>name, phone number and password</strong> (stored hashed). Teachers sign in with their academy accounts. We use this data only to:</p>
        <ul>
          <li>Sign you in, book courses and show your bookings.</li>
          <li>Let your teacher and the academy contact you about the course you booked.</li>
        </ul>
        <p>Your sign-in token is kept in the device's secure storage. You can <strong>permanently delete your account</strong> at any time from Settings → Delete account, which removes your data from our servers.</p>

        <h2 class="h5 mt-4">Internet access</h2>
        <p>The app connects to the internet only to:</p>
        <ul>
          <li>Show academy courses and teachers and complete bookings via the academy's server.</li>
          <li>Download Madani mushaf page fonts from Quran.com (static.qurancdn.com).</li>
          <li>Stream recitations from EveryAyah.com.</li>
        </ul>
        <p>Tapping a Zoom class link or the WhatsApp button opens those apps, which are governed by their own privacy policies.</p>
        <p>Like any internet request, these servers receive standard technical information such as your IP address; we do not use it to track or identify you.</p>

        <h2 class="h5 mt-4">Notifications</h2>
        <p>The daily wird reminder is scheduled on your device only and can be turned off in Settings.</p>

        <h2 class="h5 mt-4">What we don't do</h2>
        <ul>
          <li>No ads.</li>
          <li>No tracking or analytics.</li>
          <li>No access to your location, contacts or photos.</li>
          <li>We never sell or share any data.</li>
        </ul>

        <h2 class="h5 mt-4">Children</h2>
        <p>The app is suitable for all ages. We recommend that a parent creates the academy account for young children.</p>

        <h2 class="h5 mt-4">Changes</h2>
        <p>If we add features that affect your data, we will update this page before releasing them.</p>

        @if ($email)
          <h2 class="h5 mt-4">Contact</h2>
          <p>Questions or data deletion requests: <a href="mailto:{{ $email }}">{{ $email }}</a></p>
        @endif
      @endif
    </div>
  </section>
@endsection
