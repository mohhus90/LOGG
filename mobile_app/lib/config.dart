import 'package:flutter/foundation.dart' show kIsWeb;

class AppConfig {
  /// Base URL of the NEXA server, e.g. http://localhost/NEXA
  /// Override at build/run time with: --dart-define=SERVER_URL=http://192.168.1.10/NEXA
  static String get serverUrl {
    const override = String.fromEnvironment('SERVER_URL');
    if (override.isNotEmpty) return override;

    if (kIsWeb) return 'http://localhost/NEXA';

    // 10.0.2.2 is the Android emulator's alias for the host machine's localhost.
    // A real device on the same network needs --dart-define=SERVER_URL=http://<lan-ip>/NEXA
    return 'http://10.0.2.2/NEXA';
  }

  static String get apiBase => '$serverUrl/api/employee';
}
