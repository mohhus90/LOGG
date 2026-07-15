import 'package:flutter/material.dart';

import '../models/leave_request.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';
import 'new_leave_request_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  VacationBalance? _balance;
  int _pending = 0;
  int _approved = 0;
  List<LeaveRequest> _requests = [];
  bool _loading = true;
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
        ApiClient.instance.dio.get('/leave-requests/balance'),
        ApiClient.instance.dio.get('/leave-requests'),
      ]);

      final balanceData = results[0].data;
      final requestsData = results[1].data['data'] as List;

      setState(() {
        _balance = balanceData['balance'] != null ? VacationBalance.fromJson(balanceData['balance']) : null;
        _pending = balanceData['pending_requests'] ?? 0;
        _approved = balanceData['approved_requests'] ?? 0;
        _requests = requestsData.map((e) => LeaveRequest.fromJson(e)).toList();
      });
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _cancel(LeaveRequest r) async {
    try {
      await ApiClient.instance.dio.post('/leave-requests/${r.id}/cancel');
      _load();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
      }
    }
  }

  Color _statusColor(int status) => switch (status) {
        1 => Colors.green,
        2 => Colors.red,
        3 => Colors.grey,
        _ => Colors.orange,
      };

  @override
  Widget build(BuildContext context) {
    final employee = AuthService.instance.employee;

    return Scaffold(
      appBar: AppBar(
        title: Text('مرحباً، ${employee?.name ?? ''}'),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final created = await Navigator.of(context).push<bool>(
            MaterialPageRoute(builder: (_) => const NewLeaveRequestScreen()),
          );
          if (created == true) _load();
        },
        icon: const Icon(Icons.add),
        label: const Text('طلب جديد'),
      ),
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
                  if (_balance != null) ...[
                    Row(
                      children: [
                        Expanded(
                          child: _BalanceCard(
                            label: 'رصيد إجازة اعتيادية',
                            value: formatDays(_balance!.annualRemaining),
                            sub: 'من أصل ${formatDays(_balance!.annualBalance)} يوم',
                            color: const Color(0xFF11998E),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _BalanceCard(
                            label: 'رصيد إجازة عارضة',
                            value: formatDays(_balance!.casualRemaining),
                            sub: 'من أصل ${formatDays(_balance!.casualBalance)} يوم',
                            color: Colors.orange,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(child: _BalanceCard(label: 'طلبات قيد الانتظار', value: '$_pending', color: Colors.blue)),
                        const SizedBox(width: 12),
                        Expanded(child: _BalanceCard(label: 'طلبات مقبولة هذا الشهر', value: '$_approved', color: Colors.green)),
                      ],
                    ),
                    const SizedBox(height: 24),
                  ],
                  Text('طلباتي السابقة', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  if (_requests.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Center(child: Text('لا توجد طلبات', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ..._requests.map((r) => Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            title: Text(r.typeLabel),
                            subtitle: Text(
                              '${r.startDate.year}-${r.startDate.month.toString().padLeft(2, '0')}-${r.startDate.day.toString().padLeft(2, '0')}',
                            ),
                            trailing: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Chip(
                                  label: Text(r.statusLabel, style: const TextStyle(color: Colors.white, fontSize: 12)),
                                  backgroundColor: _statusColor(r.status),
                                  padding: EdgeInsets.zero,
                                  materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                                ),
                                if (r.isPending)
                                  IconButton(
                                    icon: const Icon(Icons.close, size: 20),
                                    onPressed: () => _cancel(r),
                                    tooltip: 'إلغاء',
                                  ),
                              ],
                            ),
                          ),
                        )),
                  const SizedBox(height: 80),
                ],
              ),
            ),
    );
  }
}

class _BalanceCard extends StatelessWidget {
  final String label;
  final String value;
  final String? sub;
  final Color color;

  const _BalanceCard({required this.label, required this.value, this.sub, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border(right: BorderSide(color: color, width: 4)),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 8, offset: const Offset(0, 2))],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(value, style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color)),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(fontSize: 13)),
          if (sub != null) Text(sub!, style: const TextStyle(fontSize: 11, color: Colors.grey)),
        ],
      ),
    );
  }
}
