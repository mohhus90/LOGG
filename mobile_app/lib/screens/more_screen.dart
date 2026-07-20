import 'package:flutter/material.dart';

import '../config.dart';
import '../services/auth_service.dart';
import 'change_password_screen.dart';
import 'resignation_screen.dart';
import 'server_settings_screen.dart';

class MoreScreen extends StatelessWidget {
  const MoreScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final employee = AuthService.instance.employee;

    return Scaffold(
      appBar: AppBar(title: const Text('المزيد')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  const CircleAvatar(radius: 28, backgroundColor: Color(0xFF11998E), child: Icon(Icons.person, color: Colors.white, size: 30)),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(employee?.name ?? '', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                        if (employee?.job != null) Text(employee!.job!, style: const TextStyle(color: Colors.grey)),
                        if (employee?.department != null) Text(employee!.department!, style: const TextStyle(color: Colors.grey)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          Card(
            child: Column(
              children: [
                ListTile(
                  leading: const Icon(Icons.lock_reset_outlined, color: Color(0xFF11998E)),
                  title: const Text('تغيير كلمة المرور'),
                  trailing: const Icon(Icons.chevron_left),
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ChangePasswordScreen())),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.logout_outlined, color: Colors.red),
                  title: const Text('طلب استقالة'),
                  trailing: const Icon(Icons.chevron_left),
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ResignationScreen())),
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.dns_outlined, color: Color(0xFF11998E)),
                  title: const Text('إعدادات السيرفر'),
                  subtitle: Text(AppConfig.serverUrl, style: const TextStyle(fontSize: 12), textDirection: TextDirection.ltr),
                  trailing: const Icon(Icons.chevron_left),
                  onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const ServerSettingsScreen())),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          OutlinedButton.icon(
            onPressed: () => _confirmLogout(context),
            icon: const Icon(Icons.power_settings_new, color: Colors.red),
            label: const Text('تسجيل الخروج', style: TextStyle(color: Colors.red)),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              side: const BorderSide(color: Colors.red),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _confirmLogout(BuildContext context) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تسجيل الخروج'),
        content: const Text('هل تريد تسجيل الخروج من التطبيق؟'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('إلغاء')),
          TextButton(onPressed: () => Navigator.pop(context, true), child: const Text('تسجيل الخروج')),
        ],
      ),
    );
    if (confirmed == true) {
      await AuthService.instance.logout();
    }
  }
}
