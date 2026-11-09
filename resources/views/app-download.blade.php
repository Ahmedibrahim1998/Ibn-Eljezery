{{-- صفحة تنزيل تطبيق «ابن الجزري» (Android).
     ملف الـ APK مش في git (حجمه كبير): بيترفع يدويًا على السيرفر في public/downloads/ibn-eljezery.apk
     والزرار بيظهر تلقائي أول ما الملف يكون موجود. --}}
@php
  $apk = public_path('downloads/ibn-eljezery.apk');
  $available = is_file($apk);
  $sizeMb = $available ? number_format(filesize($apk) / 1048576, 0) : null;
  $updated = $available ? date('Y-m-d', filemtime($apk)) : null;
  $version = $available ? filemtime($apk) : null;
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="theme-color" content="#063D2E" />
  <title>تطبيق ابن الجزري — حمّل التطبيق</title>
  <meta name="description" content="تطبيق أكاديمية ابن الجزري: المصحف الشريف بخط المدينة، الورد والتسميع، التفسير، ودورات الأكاديمية أونلاين وحضوري." />
  <meta property="og:title" content="تطبيق ابن الجزري" />
  <meta property="og:description" content="المصحف الشريف + ورد التسميع والمراجعة + دورات الأكاديمية — حمّله دلوقتي." />
  <meta property="og:image" content="{{ asset('assets/LOGO/logo.png') }}" />
  <link rel="icon" href="{{ asset('assets/LOGO/logo.png') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Amiri+Quran&display=swap" rel="stylesheet" />
  <style>
    :root {
      --emerald: #0B5D45; --emerald-deep: #063D2E; --gold: #C9A34E; --gold-soft: #E8D5A3;
      --ivory: #FBF8F1; --ink: #1D2A25; --muted: #5E6B66; --card: #FFFFFF; --line: #E7E1D3;
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; }
    body { font-family: 'Tajawal', sans-serif; background: var(--ivory); color: var(--ink); line-height: 1.7; }
    a { color: inherit; }

    .hero {
      position: relative; overflow: hidden; color: #fff; text-align: center;
      background: radial-gradient(circle at 50% 0%, #11785A 0%, var(--emerald) 40%, var(--emerald-deep) 100%);
      padding: 56px 16px 120px;
    }
    .hero::before, .hero::after {
      content: ''; position: absolute; width: 340px; height: 340px; border: 2px solid rgba(201,163,78,.18);
      transform: rotate(45deg); border-radius: 28px;
    }
    .hero::before { top: -170px; inset-inline-start: -120px; }
    .hero::after { bottom: -200px; inset-inline-end: -140px; }
    .logo {
      width: 112px; height: 112px; border-radius: 30px; background: #fff; padding: 12px;
      box-shadow: 0 12px 40px rgba(0,0,0,.25), 0 0 0 4px rgba(201,163,78,.55); object-fit: contain;
    }
    .basmala { font-family: 'Amiri Quran', serif; color: var(--gold-soft); font-size: 20px; margin: 18px 0 4px; }
    h1 { font-size: clamp(30px, 7vw, 44px); font-weight: 800; margin: 0; }
    .tagline { color: var(--gold-soft); font-size: 18px; margin: 6px auto 0; max-width: 520px; }

    .panel {
      position: relative; max-width: 560px; margin: -84px auto 0; padding: 24px; text-align: center;
      background: var(--card); border-radius: 28px; border: 1px solid var(--line);
      box-shadow: 0 20px 50px rgba(6,61,46,.12);
    }
    .btn {
      display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%;
      padding: 16px 20px; border-radius: 18px; text-decoration: none; font-weight: 800; font-size: 19px;
      background: linear-gradient(135deg, var(--emerald), var(--emerald-deep)); color: #fff;
      box-shadow: 0 10px 24px rgba(11,93,69,.35); transition: transform .15s ease, box-shadow .15s ease;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(11,93,69,.42); }
    .btn svg { width: 26px; height: 26px; flex: none; }
    .btn.disabled { background: #B8C2BE; box-shadow: none; pointer-events: none; }
    .meta { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .chip { background: #F3EEE2; color: var(--muted); border-radius: 999px; padding: 4px 12px; font-size: 14px; font-weight: 500; }
    .soon { color: var(--muted); margin: 12px 0 0; }

    main { max-width: 960px; margin: 0 auto; padding: 0 16px 48px; }
    h2 { text-align: center; font-size: 26px; margin: 48px 0 20px; }
    h2 span { color: var(--gold); }
    .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .feature { background: var(--card); border: 1px solid var(--line); border-radius: 22px; padding: 20px; }
    .feature .ic {
      width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; margin-bottom: 10px;
      background: linear-gradient(135deg, var(--emerald), var(--emerald-deep)); color: var(--gold-soft);
    }
    .feature .ic.gold { background: linear-gradient(135deg, #D8B565, #A9822F); color: #fff; }
    .feature .ic svg { width: 26px; height: 26px; }
    .feature h3 { margin: 0 0 4px; font-size: 18px; }
    .feature p { margin: 0; color: var(--muted); font-size: 15px; }

    .steps { counter-reset: s; list-style: none; padding: 0; margin: 0 auto; max-width: 620px; }
    .steps li {
      counter-increment: s; position: relative; background: var(--card); border: 1px solid var(--line);
      border-radius: 18px; padding: 14px 64px 14px 16px; margin-bottom: 10px; font-size: 16px;
    }
    .steps li::before {
      content: counter(s); position: absolute; inset-inline-start: 14px; top: 50%; transform: translateY(-50%);
      width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; font-weight: 800;
      background: var(--gold); color: #fff;
    }
    .note { max-width: 620px; margin: 14px auto 0; color: var(--muted); font-size: 14px; text-align: center; }

    footer { background: var(--emerald-deep); color: rgba(255,255,255,.7); text-align: center; padding: 22px 16px; font-size: 14px; }
    footer a { color: var(--gold-soft); text-decoration: none; margin: 0 8px; }

    @media (prefers-color-scheme: dark) {
      :root { --ivory: #0E1714; --ink: #ECE7DA; --muted: #A4B0AB; --card: #15211D; --line: #24332E; }
      .chip { background: #1D2B26; }
    }
  </style>
</head>
<body>
  <header class="hero">
    <img class="logo" src="{{ asset('assets/LOGO/logo.png') }}" alt="ابن الجزري" />
    <p class="basmala">بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ</p>
    <h1>تطبيق ابن الجزري</h1>
    <p class="tagline">مصحفك وورْدك ودوراتك في الأكاديمية — في مكان واحد</p>
  </header>

  <section class="panel" id="download">
    @if ($available)
      <a class="btn" href="{{ asset('downloads/ibn-eljezery.apk') }}?v={{ $version }}" download="ibn-eljezery.apk">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.6 9.48l1.84-3.18a.38.38 0 0 0-.66-.38l-1.87 3.22a11.5 11.5 0 0 0-9.82 0L5.22 5.92a.38.38 0 0 0-.66.38L6.4 9.48A10.8 10.8 0 0 0 1 18h22a10.8 10.8 0 0 0-5.4-8.52zM7 15.25a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5zm10 0a1.25 1.25 0 1 1 0-2.5 1.25 1.25 0 0 1 0 2.5z"/></svg>
        حمّل التطبيق لأندرويد
      </a>
      <div class="meta">
        <span class="chip">مجاني</span>
        <span class="chip">الحجم {{ $sizeMb }} ميجا</span>
        <span class="chip">أندرويد 7 أو أحدث</span>
        <span class="chip">آخر تحديث {{ $updated }}</span>
      </div>
    @else
      <span class="btn disabled">التطبيق قريبًا</span>
      <p class="soon">بنجهّز رابط التنزيل — ارجع للصفحة دي قريب.</p>
    @endif
  </section>

  <main>
    <h2>إيه اللي <span>في التطبيق؟</span></h2>
    <div class="features">
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.9 1 6v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1zm0 13.5c-1.1-.35-2.3-.5-3.5-.5-1.7 0-4.15.65-5.5 1.5V8c1.35-.85 3.8-1.5 5.5-1.5 1.2 0 2.4.15 3.5.5v11.5z"/></svg></div>
        <h3>المصحف الشريف</h3>
        <p>بخط مصحف المدينة صفحة بصفحة، مع البحث والفهرس والتلاوة بصوت كبار القرّاء — ويشتغل من غير نت.</p>
      </div>
      <div class="feature">
        <div class="ic gold"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-2 .9-2 2v16l7-3 7 3V5c0-1.1-.9-2-2-2z"/></svg></div>
        <h3>وِردي</h3>
        <p>علّم التسميع الجديد والمراجعة القريبة والبعيدة والترتيل، واختبر حفظك وتابع إنجازك يوم بيوم.</p>
      </div>
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg></div>
        <h3>التفسير الميسّر</h3>
        <p>اضغط على أي آية تقرأ تفسيرها، وتشاركها كصورة جميلة مع أصحابك.</p>
      </div>
      <div class="feature">
        <div class="ic gold"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg></div>
        <h3>دورات الأكاديمية</h3>
        <p>شوف دورات المعلمين أونلاين وحضوري، احجز مكانك، وادخل حصتك على زووم أو كلّم معلمك على واتساب.</p>
      </div>
      <div class="feature">
        <div class="ic"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg></div>
        <h3>خطة الختمة</h3>
        <p>حدد هتختم في كام يوم، والتطبيق يقسّم لك الورد اليومي ويفكّرك بيه بتنبيه.</p>
      </div>
      <div class="feature">
        <div class="ic gold"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg></div>
        <h3>من غير إعلانات</h3>
        <p>مجاني تمامًا، من غير إعلانات ولا تتبّع.</p>
      </div>
    </div>

    <h2>إزاي <span>تسطّبه؟</span></h2>
    <ol class="steps">
      <li>اضغط زرار <strong>«حمّل التطبيق»</strong> فوق من موبايلك الأندرويد.</li>
      <li>بعد ما التنزيل يخلص، افتح الملف <strong>ibn-eljezery.apk</strong> من الإشعارات أو من «التنزيلات».</li>
      <li>لو ظهرلك تنبيه، اختار <strong>«الإعدادات»</strong> وفعّل <strong>«السماح من هذا المصدر»</strong> وارجع.</li>
      <li>اضغط <strong>«تثبيت»</strong> — وبعدها <strong>«فتح»</strong>. بالتوفيق 🌿</li>
    </ol>
    <p class="note">التطبيق متاح دلوقتي لأندرويد بس، وقريبًا على Google Play. لو عندك نسخة قديمة، نزّل الجديدة وسطّبها فوقيها عادي — بياناتك مش هتتمسح.</p>
  </main>

  <footer>
    &copy; {{ date('Y') }} {{ localizedSetting('brand_name', 'ابن الجزري') }}
    <div style="margin-top:6px">
      <a href="{{ route('home') }}">الموقع</a>·
      <a href="{{ route('privacy') }}">سياسة الخصوصية</a>
    </div>
  </footer>
</body>
</html>
