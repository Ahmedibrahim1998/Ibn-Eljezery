// يبني assets/quran/mushaf_layout.json: ترتيب سطور مصحف المدينة (15 سطر) لكل صفحة من الـ 604،
// وكل كلمة معاها رمزها في خط المجمع (QCF V1) ورقم الآية الموحّد (0–6235).
//
// المصدر: Quran.com API v4 (verses/by_page مع code_v1 و line_number) — الخطوط نفسها بتتنزل وقت التشغيل من static.qurancdn.com.
// الاستخدام (من فولدر ibneljezery_app):
//   node tool/build_mushaf_layout.js
// بيقرأ assets/quran/quran.json عشان يحسب الترقيم الموحّد، وبيتأكد إن كل الـ 6236 آية موجودة.
const fs = require('fs');

const quran = JSON.parse(fs.readFileSync('assets/quran/quran.json', 'utf8'));
const suraStart = quran.suras.map((s) => s.start);
const indexOf = (key) => {
  const [s, a] = key.split(':').map(Number);
  return suraStart[s - 1] + a - 1;
};

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function fetchPage(page) {
  const verses = [];
  for (let p = 1; ; p++) {
    const url = `https://api.quran.com/api/v4/verses/by_page/${page}?words=true&word_fields=code_v1,line_number&per_page=50&page=${p}`;
    for (let attempt = 1; ; attempt++) {
      try {
        const res = await fetch(url);
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const json = await res.json();
        verses.push(...json.verses);
        if (!json.pagination || json.pagination.next_page == null) return verses;
        break;
      } catch (e) {
        if (attempt >= 5) throw new Error(`page ${page}: ${e.message}`);
        await sleep(1000 * attempt);
      }
    }
  }
}

(async () => {
  const pages = [];
  const seen = new Set();
  for (let page = 1; page <= 604; page++) {
    const verses = await fetchPage(page);
    const lines = new Map();
    for (const v of verses) {
      const idx = indexOf(v.verse_key);
      seen.add(idx);
      for (const w of v.words) {
        if (!lines.has(w.line_number)) lines.set(w.line_number, []);
        lines.get(w.line_number).push([w.code_v1, idx]);
      }
    }
    // [[رقم السطر, [[رمز, آية], ...]], ...] بالترتيب
    pages.push([...lines.entries()].sort((a, b) => a[0] - b[0]));
    if (page % 50 === 0) console.log(`… ${page}/604`);
    await sleep(120);
  }

  const ok = (c, m) => { if (!c) throw new Error('FAIL ' + m); console.log('✓', m); };
  ok(pages.length === 604, '604 pages');
  ok(seen.size === 6236, 'all 6236 verses present');
  ok(pages.every((p) => p.every(([n]) => n >= 1 && n <= 15)), 'line numbers within 1..15');

  fs.writeFileSync('assets/quran/mushaf_layout.json', JSON.stringify({
    source: 'Quran.com API v4 (code_v1, line_number) — King Fahd Complex QCF V1 Madani layout',
    pages,
  }));
  console.log('written', fs.statSync('assets/quran/mushaf_layout.json').size, 'bytes');
})().catch((e) => { console.error(e); process.exit(1); });
