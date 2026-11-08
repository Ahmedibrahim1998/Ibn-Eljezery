/// قارئ من EveryAyah.com (ملفات آية بآية: https://everyayah.com/data/{id}/SSSAAA.mp3).
class Reciter {
  const Reciter(this.id, this.name);

  final String id;
  final String name;

  String urlFor(int sura, int ayah) =>
      'https://everyayah.com/data/$id/${sura.toString().padLeft(3, '0')}${ayah.toString().padLeft(3, '0')}.mp3';

  static const all = [
    Reciter('Alafasy_128kbps', 'مشاري العفاسي'),
    Reciter('Husary_128kbps', 'محمود خليل الحصري'),
    Reciter('Husary_Muallim_128kbps', 'الحصري (المعلّم)'),
    Reciter('Minshawy_Murattal_128kbps', 'محمد صديق المنشاوي'),
    Reciter('Abdul_Basit_Murattal_192kbps', 'عبد الباسط عبد الصمد'),
    Reciter('Hudhaify_128kbps', 'علي الحذيفي'),
    Reciter('Abdurrahmaan_As-Sudais_192kbps', 'عبد الرحمن السديس'),
  ];

  static Reciter byId(String id) =>
      all.firstWhere((r) => r.id == id, orElse: () => all.first);
}
