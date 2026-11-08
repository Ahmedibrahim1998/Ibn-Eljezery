import 'package:flutter_test/flutter_test.dart';
import 'package:ibneljezery_app/features/fiqh/presentation/lesson_screen.dart';
import 'package:ibneljezery_app/features/mushaf/data/quran_data.dart';

void main() {
  setUpAll(() async {
    TestWidgetsFlutterBinding.ensureInitialized();
    await QuranData.load();
  });

  test('[[2:255]] ← نص آية الكرسي من المصحف المدمج', () {
    final html = expandVerseReferences('<p>قال تعالى:</p>[[2:255]]');
    expect(
      html,
      contains(QuranData.instance.text(QuranData.instance.indexOf(2, 255))),
    );
    expect(html, contains('[البقرة ٢٥٥]'));
    expect(html, contains('class="ayah"'));
  });

  test('[[1:1-3]] ← مقطع من آية لآية', () {
    final html = expandVerseReferences('[[1:1-3]]');
    expect(html, contains('﴿١﴾'));
    expect(html, contains('﴿٣﴾'));
    expect(html, isNot(contains('﴿٤﴾')));
    expect(html, contains('[الفاتحة ١-٣]'));
  });

  test('المرجع الغلط يفضل زي ما هو', () {
    expect(expandVerseReferences('[[115:1]]'), '[[115:1]]');
    expect(expandVerseReferences('[[1:8]]'), '[[1:8]]'); // الفاتحة 7 آيات بس
    expect(expandVerseReferences('[[2:5-3]]'), '[[2:5-3]]');
  });
}
