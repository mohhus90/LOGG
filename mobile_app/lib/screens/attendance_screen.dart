import 'package:flutter/material.dart';

import '../models/attendance.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../services/biometric_service.dart';
import '../services/location_service.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({super.key});

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  AttendanceRecord? _today;
  List<AttendanceRecord> _history = [];
  bool _loading = true;
  bool _busy = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final results = await Future.wait([
        ApiClient.instance.dio.get('/attendance/today'),
        ApiClient.instance.dio.get('/attendance/history'),
      ]);

      setState(() {
        _today = results[0].data['attendance'] != null ? AttendanceRecord.fromJson(results[0].data['attendance']) : null;
        _history = (results[1].data['data'] as List).map((e) => AttendanceRecord.fromJson(e)).toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _punch({required bool isCheckIn}) async {
    setState(() => _busy = true);

    try {
      final verified = await BiometricService.verifyIdentity();
      if (!verified) {
        _showMessage('فشل التحقق من الهوية، حاول مرة أخرى');
        return;
      }

      final employee = AuthService.instance.employee;
      final needsLocation = employee?.locationTrackingEnabled ?? false;

      double? lat;
      double? lng;
      if (needsLocation) {
        final position = await LocationService.getCurrentPosition();
        if (!position.hasCoordinates) {
          _showMessage(position.error ?? 'تعذر تحديد الموقع، حاول مرة أخرى');
          return;
        }
        lat = position.latitude;
        lng = position.longitude;
      }

      await ApiClient.instance.dio.post(
        isCheckIn ? '/attendance/check-in' : '/attendance/check-out',
        data: {
          'latitude': lat,
          'longitude': lng,
          'device_verified': true,
        },
      );

      _showMessage(isCheckIn ? 'تم تسجيل الحضور بنجاح' : 'تم تسجيل الانصراف بنجاح');
      _load();
    } catch (e) {
      _showMessage(ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    final canCheckIn = _today == null || !_today!.hasCheckedIn;
    final canCheckOut = _today != null && _today!.hasCheckedIn && !_today!.hasCheckedOut;

    return Scaffold(
      appBar: AppBar(title: const Text('الحضور والانصراف')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (_error != null)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 16),
                      decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
                      child: Text(_error!, style: TextStyle(color: Colors.red.shade700)),
                    ),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        children: [
                          Text('اليوم', style: Theme.of(context).textTheme.titleMedium),
                          const SizedBox(height: 16),
                          Row(
                            children: [
                              Expanded(
                                child: _TimeBox(label: 'الحضور', time: _today?.checkInTime, icon: Icons.login),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: _TimeBox(label: 'الانصراف', time: _today?.checkOutTime, icon: Icons.logout),
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),
                          if (canCheckIn)
                            FilledButton.icon(
                              onPressed: _busy ? null : () => _punch(isCheckIn: true),
                              icon: _busy
                                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                  : const Icon(Icons.fingerprint),
                              label: const Text('تسجيل حضور'),
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFF11998E),
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                minimumSize: const Size.fromHeight(48),
                              ),
                            )
                          else if (canCheckOut)
                            FilledButton.icon(
                              onPressed: _busy ? null : () => _punch(isCheckIn: false),
                              icon: _busy
                                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                                  : const Icon(Icons.fingerprint),
                              label: const Text('تسجيل انصراف'),
                              style: FilledButton.styleFrom(
                                backgroundColor: Colors.orange.shade700,
                                padding: const EdgeInsets.symmetric(vertical: 16),
                                minimumSize: const Size.fromHeight(48),
                              ),
                            )
                          else
                            const Padding(
                              padding: EdgeInsets.symmetric(vertical: 8),
                              child: Text('تم تسجيل الحضور والانصراف لليوم', style: TextStyle(color: Colors.grey)),
                            ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text('سجل الحضور', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  if (_history.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Center(child: Text('لا يوجد سجل حضور', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ..._history.map((a) => Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            title: Text(
                              '${a.attendanceDate.year}-${a.attendanceDate.month.toString().padLeft(2, '0')}-${a.attendanceDate.day.toString().padLeft(2, '0')}',
                            ),
                            subtitle: Text('حضور: ${a.checkInTime ?? '—'}   |   انصراف: ${a.checkOutTime ?? '—'}'),
                            trailing: a.lateMinutes > 0
                                ? Text('تأخير ${a.lateMinutes} د', style: const TextStyle(color: Colors.red, fontSize: 12))
                                : null,
                          ),
                        )),
                ],
              ),
            ),
    );
  }
}

class _TimeBox extends StatelessWidget {
  final String label;
  final String? time;
  final IconData icon;

  const _TimeBox({required this.label, required this.time, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(color: Colors.grey.shade100, borderRadius: BorderRadius.circular(10)),
      child: Column(
        children: [
          Icon(icon, color: time != null ? const Color(0xFF11998E) : Colors.grey, size: 22),
          const SizedBox(height: 6),
          Text(label, style: const TextStyle(fontSize: 12, color: Colors.grey)),
          const SizedBox(height: 2),
          Text(time ?? '—', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
        ],
      ),
    );
  }
}
