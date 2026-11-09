// يبني assets/quran/tafsir_muyassar.json (التفسير الميسر — مجمع الملك فهد) من ملف Tanzil ويتأكد إن كل آية في مكانها.
// الاستخدام: نزّل https://tanzil.net/trans/ar.muyassar (نص: سورة|آية|تفسير) ثم:
//   node tool/build_tafsir.js path/to/ar.muyassar.txt assets/quran
// ترتيب الآيات بيتراجع على quran.json (نفس عدد آيات كل سورة).
const fs=require('fs');
const [src,out]=process.argv.slice(2);
const lines=fs.readFileSync(src,'utf8').split('\n');
const header=lines.filter(l=>l.startsWith('#'));
const rows=lines.map(l=>l.split('|')).filter(p=>p.length===3).map(p=>({s:+p[0],a:+p[1],t:p[2].trim()}));
const ok=(c,m)=>{if(!c)throw new Error('FAIL '+m);console.log('✓',m)};
ok(rows.length===6236,'6236 tafsir entries');
const {suras}=JSON.parse(fs.readFileSync(out+'/quran.json','utf8'));
let i=0;for(const s of suras){for(let a=1;a<=s.ayas;a++){const r=rows[i++];if(r.s!==s.n||r.a!==a)throw new Error('order '+s.n+':'+a);}}
console.log('✓ order matches quran.json');
ok(rows.every(r=>r.t.length>0),'no empty entries');
fs.writeFileSync(out+'/tafsir_muyassar.json',JSON.stringify({source:'التفسير الميسر — مجمع الملك فهد لطباعة المصحف الشريف، عبر Tanzil.net',tafsir:rows.map(r=>r.t)}));
fs.appendFileSync(out+'/LICENSE.txt','\n'+header.join('\n')+'\n');
console.log('sample 112:1 →',rows[suras.slice(0,111).reduce((n,s)=>n+s.ayas,0)].t);
