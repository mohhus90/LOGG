import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:shared_preferences/shared_preferences.dart';

class AppConfig {
  static const _prefsKey = 'server_url';

  /// In-memory copy of the persisted override, populated by [init].
  static String? _override;

  /// Base URL baked in at build/run time, e.g. --dart-define=SERVER_URL=http://192.168.1.10/NEXA
  static String get _defaultUrl {
    const override = String.fromEnvironment('SERVER_URL');
    if (override.isNotEmpty) return override;

    if (kIsWeb) return 'http://localhost/NEXA';

    // 10.0.2.2 is the Android emulator's alias for the host machine's localhost.
    return 'http://10.0.2.2/NEXA';
  }

  /// Loads any server URL saved from the in-app settings screen.
  /// Must be awaited once before runApp() so ApiClient picks up the right value.
  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_prefsKey);
    if (saved != null && saved.isNotEmpty) _override = saved;
  }

  /// Base URL of the NEXA server, e.g. http://192.168.1.10/NEXA
  static String get serverUrl => _override ?? _defaultUrl;

  static String get apiBase => '$serverUrl/api/employee';

  static bool get isCustom => _override != null;

  /// Persists a new server URL for this and future launches. Pass an empty
  /// string to clear the override and fall back to the built-in default.
  static Future<void> setServerUrl(String url) async {
    final trimmed = url.trim().replaceAll(RegExp(r'/+$'), '');
    final prefs = await SharedPreferences.getInstance();
    if (trimmed.isEmpty) {
      await prefs.remove(_prefsKey);
      _override = null;
    } else {
      await prefs.setString(_prefsKey, trimmed);
      _override = trimmed;
    }
  }
}
