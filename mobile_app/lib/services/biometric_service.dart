import 'package:local_auth/local_auth.dart';
import 'package:flutter/foundation.dart' show kIsWeb;

class BiometricService {
  static final _auth = LocalAuthentication();

  /// Verifies the device owner via fingerprint/face. On web (no local_auth
  /// support) this is skipped and treated as verified, since attendance from
  /// the web portal already relies on a plain session login instead.
  static Future<bool> verifyIdentity() async {
    if (kIsWeb) return true;

    try {
      final canCheck = await _auth.canCheckBiometrics || await _auth.isDeviceSupported();
      if (!canCheck) return false;

      return await _auth.authenticate(
        localizedReason: 'تحقق من هويتك لتسجيل الحضور',
        biometricOnly: false,
        persistAcrossBackgrounding: true,
      );
    } catch (_) {
      return false;
    }
  }
}
