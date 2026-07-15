import 'package:flutter_test/flutter_test.dart';

import 'package:employee_app/main.dart';

void main() {
  testWidgets('shows the login screen when no session is stored', (WidgetTester tester) async {
    await tester.pumpWidget(const EmployeeApp());
    await tester.pump();

    expect(find.text('بوابة الموظف'), findsWidgets);
  });
}
