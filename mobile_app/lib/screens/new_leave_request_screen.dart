import 'package:flutter/material.dart';

import '../services/api_client.dart';

class NewLeaveRequestScreen extends StatefulWidget {
  const NewLeaveRequestScreen({super.key});

  @override
  State<NewLeaveRequestScreen> createState() => _NewLeaveRequestScreenState();
}

class _NewLeaveRequestScreenState extends State<NewLeaveRequestScreen> {
  static const _vacationTypes = {
    'annual_vacation': '🏖 إجازة اعتيادية',
    'casual_vacation': '📅 إجازة عارضة',
  };
  static const _permissionTypes = {
    'late_permission': '⏰ إذن تأخير',
    'early_leave': '🚪 إذن انصراف مبكر',
    'mission': '🏢 مأمورية',
  };

  String _type = 'annual_vacation';
  DateTime _startDate = DateTime.now();
  DateTime _endDate = DateTime.now();
  TimeOfDay _timeFrom = TimeOfDay.now();
  TimeOfDay _timeTo = TimeOfDay.now();
  final _reasonController = TextEditingController();

  bool _loading = false;
  String? _error;

  bool get _isVacation => _vacationTypes.containsKey(_type);

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  Future<void> _pickDate({required bool isStart}) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isStart ? _startDate : _endDate,
      firstDate: DateTime.now().subtract(const Duration(days: 1)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startDate = picked;
          if (_endDate.isBefore(_startDate)) _endDate = _startDate;
        } else {
          _endDate = picked;
        }
      });
    }
  }

  Future<void> _pickTime({required bool isFrom}) async {
    final picked = await showTimePicker(context: context, initialTime: isFrom ? _timeFrom : _timeTo);
    if (picked != null) {
      setState(() {
        if (isFrom) {
          _timeFrom = picked;
        } else {
          _timeTo = picked;
        }
      });
    }
  }

  String _fmtDate(DateTime d) => '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
  String _fmtTime(TimeOfDay t) => '${t.hour.toString().padLeft(2, '0')}:${t.minute.toString().padLeft(2, '0')}';

  Future<void> _submit() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final data = <String, dynamic>{
        'request_type': _type,
        'reason': _reasonController.text.trim(),
      };

      if (_isVacation) {
        data['start_date'] = _fmtDate(_startDate);
        data['end_date'] = _fmtDate(_endDate);
      } else {
        data['start_date'] = _fmtDate(_startDate);
        data['time_from'] = _fmtTime(_timeFrom);
        data['time_to'] = _fmtTime(_timeTo);
      }

      await ApiClient.instance.dio.post('/leave-requests', data: data);
      if (mounted) Navigator.of(context).pop(true);
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('طلب جديد')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
              child: Text(_error!, style: TextStyle(color: Colors.red.shade700)),
            ),
            const SizedBox(height: 16),
          ],
          DropdownButtonFormField<String>(
            initialValue: _type,
            decoration: const InputDecoration(labelText: 'نوع الطلب', border: OutlineInputBorder()),
            items: [
              ..._vacationTypes.entries.map((e) => DropdownMenuItem(value: e.key, child: Text(e.value))),
              ..._permissionTypes.entries.map((e) => DropdownMenuItem(value: e.key, child: Text(e.value))),
            ],
            onChanged: (v) => setState(() => _type = v!),
          ),
          const SizedBox(height: 16),
          if (_isVacation) ...[
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('من تاريخ'),
              subtitle: Text(_fmtDate(_startDate)),
              trailing: const Icon(Icons.calendar_today_outlined),
              onTap: () => _pickDate(isStart: true),
            ),
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('إلى تاريخ'),
              subtitle: Text(_fmtDate(_endDate)),
              trailing: const Icon(Icons.calendar_today_outlined),
              onTap: () => _pickDate(isStart: false),
            ),
          ] else ...[
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('التاريخ'),
              subtitle: Text(_fmtDate(_startDate)),
              trailing: const Icon(Icons.calendar_today_outlined),
              onTap: () => _pickDate(isStart: true),
            ),
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('من الساعة'),
              subtitle: Text(_fmtTime(_timeFrom)),
              trailing: const Icon(Icons.access_time),
              onTap: () => _pickTime(isFrom: true),
            ),
            ListTile(
              contentPadding: EdgeInsets.zero,
              title: const Text('إلى الساعة'),
              subtitle: Text(_fmtTime(_timeTo)),
              trailing: const Icon(Icons.access_time),
              onTap: () => _pickTime(isFrom: false),
            ),
          ],
          const SizedBox(height: 16),
          TextField(
            controller: _reasonController,
            maxLines: 3,
            decoration: const InputDecoration(labelText: 'السبب', border: OutlineInputBorder()),
          ),
          const SizedBox(height: 24),
          FilledButton(
            onPressed: _loading ? null : _submit,
            style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
            child: _loading
                ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(strokeWidth: 2.5))
                : const Text('إرسال الطلب'),
          ),
        ],
      ),
    );
  }
}
