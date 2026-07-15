import 'package:flutter/material.dart';

import '../services/api_client.dart';

class ResignationScreen extends StatefulWidget {
  const ResignationScreen({super.key});

  @override
  State<ResignationScreen> createState() => _ResignationScreenState();
}

class _ResignationScreenState extends State<ResignationScreen> {
  Map<String, dynamic>? _existing;
  bool _loading = true;
  bool _submitting = false;
  String? _error;

  DateTime _lastWorkingDate = DateTime.now().add(const Duration(days: 14));
  final _reasonController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final response = await ApiClient.instance.dio.get('/resignation');
      setState(() => _existing = response.data);
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _lastWorkingDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) setState(() => _lastWorkingDate = picked);
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _error = null;
    });
    try {
      final d = _lastWorkingDate;
      await ApiClient.instance.dio.post('/resignation', data: {
        'last_working_date': '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}',
        'reason': _reasonController.text.trim(),
      });
      _load();
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  String _statusLabel(int status) => switch (status) {
        1 => 'مقبول',
        2 => 'مرفوض',
        3 => 'ملغي',
        _ => 'قيد الانتظار',
      };

  bool get _hasPendingResignation => _existing != null && _existing!['status'] == 0;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('طلب استقالة')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (_error != null)
                  Container(
                    padding: const EdgeInsets.all(12),
                    margin: const EdgeInsets.only(bottom: 16),
                    decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
                    child: Text(_error!, style: TextStyle(color: Colors.red.shade700)),
                  ),
                if (_existing != null) ...[
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('آخر طلب استقالة', style: Theme.of(context).textTheme.titleMedium),
                          const SizedBox(height: 8),
                          Text('تاريخ آخر يوم عمل: ${_existing!['start_date']}'),
                          Text('الحالة: ${_statusLabel(_existing!['status'])}'),
                          if (_existing!['reason'] != null) Text('السبب: ${_existing!['reason']}'),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
                if (_hasPendingResignation)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 16),
                    child: Text(
                      'لديك طلب استقالة قيد الانتظار بالفعل، لا يمكنك تقديم طلب جديد حتى تتم معالجته.',
                      style: TextStyle(color: Colors.orange),
                    ),
                  )
                else ...[
                  Text('تقديم طلب استقالة جديد', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  ListTile(
                    tileColor: Colors.grey.shade100,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                    title: const Text('تاريخ آخر يوم عمل'),
                    subtitle: Text(
                      '${_lastWorkingDate.year}-${_lastWorkingDate.month.toString().padLeft(2, '0')}-${_lastWorkingDate.day.toString().padLeft(2, '0')}',
                    ),
                    trailing: const Icon(Icons.calendar_today_outlined),
                    onTap: _pickDate,
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _reasonController,
                    maxLines: 3,
                    decoration: const InputDecoration(labelText: 'السبب (اختياري)', border: OutlineInputBorder()),
                  ),
                  const SizedBox(height: 24),
                  FilledButton(
                    onPressed: _submitting ? null : _submit,
                    style: FilledButton.styleFrom(backgroundColor: Colors.red.shade700, padding: const EdgeInsets.symmetric(vertical: 16)),
                    child: _submitting
                        ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white))
                        : const Text('إرسال طلب الاستقالة'),
                  ),
                ],
              ],
            ),
    );
  }
}
