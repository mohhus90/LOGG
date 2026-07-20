import 'package:dio/dio.dart';
import 'package:flutter/material.dart';

import '../config.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';

/// Lets the user point the app at a different NEXA server (e.g. after the
/// server moved to a new machine/IP on the local network) without a rebuild.
class ServerSettingsScreen extends StatefulWidget {
  const ServerSettingsScreen({super.key});

  @override
  State<ServerSettingsScreen> createState() => _ServerSettingsScreenState();
}

class _ServerSettingsScreenState extends State<ServerSettingsScreen> {
  late final TextEditingController _urlController;
  bool _testing = false;
  bool _saving = false;
  String? _testMessage;
  bool _testSucceeded = false;

  @override
  void initState() {
    super.initState();
    _urlController = TextEditingController(text: AppConfig.serverUrl);
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  String get _candidateUrl => _urlController.text.trim().replaceAll(RegExp(r'/+$'), '');

  Future<void> _testConnection() async {
    final url = _candidateUrl;
    if (url.isEmpty) return;

    setState(() {
      _testing = true;
      _testMessage = null;
    });

    try {
      final dio = Dio(BaseOptions(
        connectTimeout: const Duration(seconds: 8),
        receiveTimeout: const Duration(seconds: 8),
      ));
      final response = await dio.get('$url/api/employee/companies');
      final ok = response.statusCode != null && response.statusCode! < 400;
      setState(() {
        _testSucceeded = ok;
        _testMessage = ok ? 'تم الاتصال بالسيرفر بنجاح' : 'السيرفر رد لكن بخطأ غير متوقع';
      });
    } catch (_) {
      setState(() {
        _testSucceeded = false;
        _testMessage = 'تعذر الاتصال بهذا العنوان. تأكد أن الموبايل على نفس الشبكة وأن العنوان صحيح';
      });
    } finally {
      if (mounted) setState(() => _testing = false);
    }
  }

  Future<void> _save() async {
    final url = _candidateUrl;
    if (url.isEmpty) return;

    setState(() => _saving = true);
    try {
      await AppConfig.setServerUrl(url);
      ApiClient.instance.refreshBaseUrl();

      if (AuthService.instance.isLoggedIn) {
        await AuthService.instance.logout();
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم حفظ عنوان السيرفر')),
      );
      Navigator.of(context).pop(true);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  Future<void> _resetToDefault() async {
    await AppConfig.setServerUrl('');
    setState(() {
      _urlController.text = AppConfig.serverUrl;
      _testMessage = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('إعدادات السيرفر')),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                'اكتب عنوان سيرفر NEXA على الشبكة المحلية، مثال:\nhttp://192.168.1.10/NEXA',
                style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: Colors.grey[600]),
              ),
              const SizedBox(height: 20),
              TextField(
                controller: _urlController,
                keyboardType: TextInputType.url,
                textDirection: TextDirection.ltr,
                decoration: const InputDecoration(
                  labelText: 'عنوان السيرفر',
                  prefixIcon: Icon(Icons.dns_outlined),
                  border: OutlineInputBorder(),
                ),
                onChanged: (_) => setState(() => _testMessage = null),
              ),
              const SizedBox(height: 16),
              if (_testMessage != null) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: _testSucceeded ? Colors.green.shade50 : Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: _testSucceeded ? Colors.green.shade200 : Colors.red.shade200),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        _testSucceeded ? Icons.check_circle_outline : Icons.error_outline,
                        color: _testSucceeded ? Colors.green.shade700 : Colors.red.shade700,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Expanded(child: Text(_testMessage!)),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
              ],
              OutlinedButton.icon(
                onPressed: _testing ? null : _testConnection,
                icon: _testing
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.wifi_tethering),
                label: const Text('اختبار الاتصال'),
              ),
              const SizedBox(height: 12),
              FilledButton(
                onPressed: _saving ? null : _save,
                style: FilledButton.styleFrom(
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  backgroundColor: const Color(0xFF11998E),
                ),
                child: _saving
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                      )
                    : const Text('حفظ', style: TextStyle(fontSize: 16)),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: _saving ? null : _resetToDefault,
                child: const Text('استعادة الإعداد الافتراضي'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
