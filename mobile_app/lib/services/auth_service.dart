import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';

import '../models/employee.dart';
import 'api_client.dart';

class AuthService extends ChangeNotifier {
  AuthService._internal() {
    ApiClient.instance.onUnauthorized = () {
      _employee = null;
      notifyListeners();
    };
  }

  static final AuthService instance = AuthService._internal();

  Employee? _employee;
  Employee? get employee => _employee;
  bool get isLoggedIn => _employee != null;

  bool _restoring = true;
  bool get isRestoring => _restoring;

  /// Called once at app start: if a token is already stored, fetch /me to
  /// confirm it's still valid and restore the session without asking to log in again.
  Future<void> restoreSession() async {
    final token = await ApiClient.instance.readToken();
    if (token != null) {
      try {
        final response = await ApiClient.instance.dio.get('/me');
        _employee = Employee(
          id: response.data['id'],
          name: response.data['employee_name_A'] ?? '',
          photo: response.data['emp_photo'],
          department: null,
          job: null,
          locationTrackingEnabled: response.data['location_tracking_enabled'] == true,
        );
      } catch (_) {
        await ApiClient.instance.clearToken();
      }
    }
    _restoring = false;
    notifyListeners();
  }

  Future<void> login({
    required String username,
    required String password,
    required int comCode,
  }) async {
    final response = await ApiClient.instance.dio.post('/login', data: {
      'login_username': username,
      'login_password': password,
      'com_code': comCode,
      'device_name': defaultTargetPlatform.name,
    });

    await ApiClient.instance.saveToken(response.data['token']);
    _employee = Employee.fromJson(response.data['employee']);
    notifyListeners();
  }

  Future<void> logout() async {
    try {
      await ApiClient.instance.dio.post('/logout');
    } on DioException {
      // best-effort; clear locally regardless
    }
    await ApiClient.instance.clearToken();
    _employee = null;
    notifyListeners();
  }
}
