<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>بوابة الدخول — مركز ابن الجزري</title>
  <link rel="icon" href="{{ asset('assets/LOGO/logo.png') }}" />
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      background: #ffffff;
      font-family: "Segoe UI", Tahoma, "Noto Sans Arabic", Arial, sans-serif;
      color: #16241c;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    /* Square 1:1 logo — reserve its box so the centered layout doesn't jump while it loads. */
    .logo { width: 84px; height: 84px; object-fit: contain; margin-bottom: .5rem; }
    h1 { font-size: 1.5rem; font-weight: 800; margin-bottom: .35rem; text-align: center; }
    p.sub { color: #6b7c72; margin-bottom: 2.5rem; text-align: center; }
    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      width: 100%;
      max-width: 860px;
    }
    .tile {
      display: flex; flex-direction: column; align-items: center; text-align: center;
      text-decoration: none; color: inherit;
      border: 1px solid #e3ebe4; border-radius: 20px;
      padding: 2.25rem 1.25rem;
      background: #fff;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .tile:hover, .tile:focus-visible {
      transform: translateY(-6px);
      box-shadow: 0 1.2rem 2.4rem rgba(21,115,71,.16);
      border-color: #198754;
      outline: none;
    }
    .tile .ic {
      width: 96px; height: 96px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2.8rem; margin-bottom: 1.1rem; color: #fff;
    }
    .tile.teacher .ic { background: linear-gradient(135deg,#198754,#0f5132); }
    .tile.supervisor .ic { background: linear-gradient(135deg,#1f8a9c,#155e6b); }
    .tile.admin .ic { background: linear-gradient(135deg,#8a6d1c,#5e4a12); }
    .tile h2 { font-size: 1.2rem; font-weight: 700; }
    .tile span { color: #6b7c72; font-size: .92rem; margin-top: .3rem; }
    .foot { margin-top: 2.75rem; color: #9aa9a0; font-size: .85rem; }
    @media (max-width: 640px) { .grid { grid-template-columns: 1fr; max-width: 360px; } }
  </style>
</head>
<body>
  <img class="logo" src="{{ asset('assets/LOGO/logo.png') }}" alt="مركز ابن الجزري" width="84" height="84" />
  <h1>بوابة الدخول</h1>
  <p class="sub">اختر نوع الحساب للدخول إلى لوحة التحكم الخاصة بك</p>

  <div class="grid">
    <a class="tile teacher" href="{{ url('/teacher/login') }}">
      <span class="ic" aria-hidden="true">👨‍🏫</span>
      <h2>المعلم</h2>
      <span>إدارة الدورات والمواعيد والحجوزات</span>
    </a>

    <a class="tile supervisor" href="{{ url('/supervisor/login') }}">
      <span class="ic" aria-hidden="true">📋</span>
      <h2>مشرف الحضور</h2>
      <span>تسجيل حضور وانصراف الطلاب</span>
    </a>

    <a class="tile admin" href="{{ url('/admin/login') }}">
      <span class="ic" aria-hidden="true">🛡️</span>
      <h2>الإدارة</h2>
      <span>التحكم الكامل في الموقع والمحتوى</span>
    </a>
  </div>

  <div class="foot">مركز ابن الجزري لتحفيظ القرآن الكريم</div>
</body>
</html>
