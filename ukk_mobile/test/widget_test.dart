import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:school_class_progress/main.dart';

void main() {
  setUpAll(() {
    GoogleFonts.config.allowRuntimeFetching = false;
  });

  testWidgets('Login screen smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: SchoolClassProgressApp(),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('School Class Progress'), findsWidgets);
    expect(find.text('Masuk ke Sistem'), findsOneWidget);
  });
}
