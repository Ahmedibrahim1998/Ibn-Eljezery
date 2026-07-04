// ضبط سنة الفوتر تلقائيًا
const yearSpan = document.getElementById("year");
if (yearSpan) {
  yearSpan.textContent = new Date().getFullYear();
}

// دالة مساعدة لعرض رسالة في النماذج
function showFormMessage(form, type, message) {
  const msgEl = form.querySelector(".form-message");
  if (!msgEl) return;
  msgEl.classList.remove("d-none", "text-success", "text-danger");
  msgEl.classList.add(type === "success" ? "text-success" : "text-danger");
  msgEl.textContent = message;
}

// التعامل مع نموذج الهيرو
const heroForm = document.getElementById("heroLeadForm");
if (heroForm) {
  heroForm.addEventListener("submit", function (e) {
    e.preventDefault();
    showFormMessage(
      heroForm,
      "success",
      "تم استلام طلبك بنجاح، سنتواصل معك قريبًا إن شاء الله."
    );
    heroForm.reset();
  });
}

// التعامل مع نموذج الاتصال
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  contactForm.addEventListener("submit", function (e) {
    e.preventDefault();
    showFormMessage(
      contactForm,
      "success",
      "تم إرسال طلبك، ستقوم إدارة المركز بالتواصل معك قريبًا."
    );
    contactForm.reset();
  });
}

// عند الضغط على زر "اختر هذه الباقة" يتم تعبئة اختيار البرنامج في نموذج الاتصال
const packageButtons = document.querySelectorAll(".choose-package-btn");
const packageSelect = document.getElementById("packageSelect");

if (packageButtons && packageSelect) {
  packageButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const pkg = btn.getAttribute("data-package");
      if (pkg) {
        packageSelect.value = pkg;
        // سكرول بسيط لقسم التواصل
        const contactSection = document.getElementById("contact");
        if (contactSection) {
          contactSection.scrollIntoView({ behavior: "smooth" });
        }
      }
    });
  });
}

// ────────────── Hero Stats Counter Animation (يدعم ar/en) ──────────────

function animateCounter(el, target, duration = 1800) {
  let startTime = null;
  const currentLang = document.documentElement.lang || 'ar'; // 'ar' أو 'en'
  
  // نحدد نوع الأرقام حسب اللغة
  const numberStyle = currentLang === 'ar' ? 'ar-EG' : 'en-US';
  
  // ننظف الرقم من + وأي رموز
  const cleanTarget = parseInt(target.replace(/[^0-9]/g, ''), 10);
  const hasPlus = target.includes('+');

  const step = (timestamp) => {
    if (!startTime) startTime = timestamp;
    const progress = Math.min((timestamp - startTime) / duration, 1);
    
    // easing أكثر سلاسة (easeOutCubic)
    const eased = 1 - Math.pow(1 - progress, 3);
    
    const current = Math.floor(cleanTarget * eased);
    
    // نعرض الرقم بالصيغة المناسبة للغة
    let displayed = current.toLocaleString(numberStyle);
    if (hasPlus) displayed = '+' + displayed;
    
    el.textContent = displayed;

    if (progress < 1) {
      requestAnimationFrame(step);
    } else {
      // الرقم النهائي بالضبط
      let final = cleanTarget.toLocaleString(numberStyle);
      if (hasPlus) final = '+' + final;
      el.textContent = final;
    }
  };

  requestAnimationFrame(step);
}

function startCountersWhenVisible() {
  const statsSection = document.querySelector('.hero-stats');
  if (!statsSection) return;

  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        const numbers = statsSection.querySelectorAll('.stat-number');
        numbers.forEach((numEl) => {
          // نشغل العد مرة واحدة فقط
          if (!numEl.dataset.animated) {
            const targetValue = numEl.textContent.trim();
            animateCounter(numEl, targetValue);
            numEl.dataset.animated = 'true';
          }
        });
        // observer.unobserve(statsSection);   // ← اختياري
      }
    },
    { threshold: 0.3 }
  );

  observer.observe(statsSection);
}

// شغّل بعد التحميل
document.addEventListener('DOMContentLoaded', () => {
  startCountersWhenVisible();
  handleScrollAnimations();
  handleAboutAnimations();
  handleHeroAnimations();
  handleTeachersAnimations();
  fixMobileHero(); // إصلاح الموبايل
});

// شغّل عند تغيير حجم الشاشة
window.addEventListener('resize', () => {
  fixMobileHero();
});

// ────────────── Teachers Section Animations ──────────────

function handleTeachersAnimations() {
  const teachersSection = document.querySelector('#teachers');
  if (!teachersSection) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // تفعيل حركات قسم المعلمين
          const teachersElements = entry.target.querySelectorAll('.teachers-section');
          teachersElements.forEach(element => {
            if (!element.classList.contains('visible')) {
              element.classList.add('visible');
            }
          });
        }
      });
    },
    {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    }
  );

  observer.observe(teachersSection);
}

// ────────────── Hero Section Animations ──────────────

function handleHeroAnimations() {
  // تأخير بسيط عشان المحتوى يظهر بشكل سلس
  setTimeout(() => {
    const heroLeftContent = document.querySelector('.hero .col-lg-7');
    const heroRightContent = document.querySelector('.hero .col-lg-5');
    
    if (heroLeftContent) {
      heroLeftContent.classList.add('loaded');
    }
    
    if (heroRightContent) {
      heroRightContent.classList.add('loaded');
    }
  }, 100); // تم تقليل التأخير من 300 إلى 100ms
}

// إضافة دالة لإصلاح مشاكل الموبايل
function fixMobileHero() {
  if (window.innerWidth <= 576) {
    const hero = document.querySelector('.hero');
    const heroContainer = document.querySelector('.hero .container');
    
    if (hero && heroContainer) {
      // التأكد من أن الهيرو يملأ العرض الكامل
      hero.style.width = '100vw';
      hero.style.minWidth = '100vw';
      hero.style.marginLeft = '0';
      hero.style.marginRight = '0';
      hero.style.paddingLeft = '0';
      hero.style.paddingRight = '0';
      
      heroContainer.style.maxWidth = '100%';
      heroContainer.style.width = '100%';
      heroContainer.style.paddingLeft = '15px';
      heroContainer.style.paddingRight = '15px';
    }
  }
}

// ────────────── Enhanced Button Effects ──────────────

function addRippleEffect() {
  document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function(e) {
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.width = ripple.style.height = size + 'px';
      ripple.style.left = x + 'px';
      ripple.style.top = y + 'px';
      ripple.classList.add('ripple');
      
      this.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });
}

// تفعيل الـ ripple effects
addRippleEffect();

// ────────────── About Section Animations ──────────────

function handleAboutAnimations() {
  const aboutSection = document.querySelector('#about');
  if (!aboutSection) return;

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          // تفعيل حركات المحتوى الأيسر
          const aboutContent = entry.target.querySelector('.about-content');
          if (aboutContent && !aboutContent.classList.contains('visible')) {
            aboutContent.classList.add('visible');
          }

          // تفعيل حركات الـ about-box
          const aboutBox = entry.target.querySelector('.about-box');
          if (aboutBox && !aboutBox.classList.contains('visible')) {
            aboutBox.classList.add('visible');
          }
        }
      });
    },
    {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    }
  );

  observer.observe(aboutSection);
}


// ────────────── Scroll Entrance Animations ──────────────

function handleScrollAnimations() {
  const elements = document.querySelectorAll('.animate-on-scroll, .stagger');
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
        // observer.unobserve(entry.target);   ← اختياري لو عايز مرة واحدة بس
      }
    });
  }, {
    threshold: 0.15,          // يشتغل لما 15% من العنصر يظهر
    rootMargin: "0px 0px -60px 0px"
  });

  elements.forEach(el => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
  handleScrollAnimations();
});