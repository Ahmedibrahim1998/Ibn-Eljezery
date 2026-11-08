// يبني assets/quran/quran.json من ملفات Tanzil ويتأكد من الأعداد (6236 آية، 114 سورة، 30 جزء، 604 صفحة).
// الاستخدام: نزّل quran-uthmani.txt (Uthmani, txt-2) و quran-simple-clean.txt (للبحث) و quran-data.js من tanzil.net في نفس الفولدر، ثم:
//   node tool/build_quran_data.js assets/quran
// الملف بيتحلل بالـ regex بس — ما بيشغّلش أي كود من الملفات المنزّلة.
const fs=require('fs');
const src=fs.readFileSync('quran-data.js','utf8');
// اقتطاع قسم واحد من الملف كنص، وتحليله بالـ regex (من غير تشغيل أي كود منه)
const section=name=>{const i=src.indexOf('QuranData.'+name+' = [');return src.slice(i,src.indexOf('];',i));};
const pairs=name=>[...section(name).matchAll(/\[(\d+),\s*(\d+)\]/g)].map(m=>[+m[1],+m[2]]).filter(p=>p[0]<=114); // آخر عنصر [115,1] علامة نهاية
const suras=[...section('Sura').matchAll(/\[(\d+), (\d+), \d+, \d+, '([^']+)', "([^"]+)", .*?'(Meccan|Medinan)'\]/g)]
  .map((m,i)=>({n:i+1,start:+m[1],ayas:+m[2],name:m[3],tname:m[4],type:m[5]==='Meccan'?'meccan':'medinan'}));
const juz=pairs('Juz'),pages=pairs('Page'),hizbQ=pairs('HizbQaurter');
const lines=fs.readFileSync('quran-uthmani.txt','utf8').split('\n');
const verses=[],header=[];
for(const l of lines){if(l.startsWith('#')){header.push(l);continue;}const p=l.split('|');if(p.length===3)verses.push({s:+p[0],a:+p[1],t:p[2].trim()});}
const ok=(c,m)=>{if(!c)throw new Error('FAIL '+m);console.log('✓',m)};
ok(verses.length===6236,'6236 verses');ok(suras.length===114,'114 suras');ok(juz.length===30,'30 juz');
ok(pages.length===604,'604 pages');ok(hizbQ.length===240,'240 hizb quarters');
ok(suras.reduce((s,x)=>s+x.ayas,0)===6236,'sura ayah counts sum to 6236');
let idx=0;for(const s of suras){for(let a=1;a<=s.ayas;a++){const v=verses[idx++];if(v.s!==s.n||v.a!==a)throw new Error('order '+s.n+':'+a);}}
console.log('✓ verse order matches sura metadata');
// نص إملائي بدون تشكيل ← للبحث فقط (العرض دايماً بالعثماني)
const search=fs.readFileSync('quran-simple-clean.txt','utf8').split('\n').filter(l=>!l.startsWith('#')).map(l=>l.split('|')).filter(p=>p.length===3).map(p=>p[2].trim());
ok(search.length===6236,'6236 search verses');
fs.writeFileSync(process.argv[2]+'/quran.json',JSON.stringify({source:'Tanzil Project — tanzil.net (Uthmani, Hafs). Verbatim copy, see LICENSE.txt',suras,juz,hizbQuarters:hizbQ,pages,verses:verses.map(v=>v.t),search}));
fs.writeFileSync(process.argv[2]+'/LICENSE.txt',header.join('\n')+'\n');
console.log('sample:',suras[0].name,suras[113].name,'juz30',juz[29],'page604',pages[603]);
