import 'package:flutter/material.dart';

import '../models/payslip.dart';
import '../services/api_client.dart';
import '../services/file_download_service.dart';

class PayslipsScreen extends StatefulWidget {
  const PayslipsScreen({super.key});

  @override
  State<PayslipsScreen> createState() => _PayslipsScreenState();
}

class _PayslipsScreenState extends State<PayslipsScreen> {
  List<Payslip> _payslips = [];
  bool _loading = true;
  String? _error;
  int? _downloadingId;
  bool _downloadingCertificate = false;

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
      final response = await ApiClient.instance.dio.get('/payslips');
      setState(() => _payslips = (response.data['data'] as List).map((e) => Payslip.fromJson(e)).toList());
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _downloadPayslip(Payslip p) async {
    setState(() => _downloadingId = p.id);
    final error = await FileDownloadService.downloadAndOpen('/payslips/${p.id}/pdf', 'payslip-${p.year}-${p.month}.pdf');
    if (mounted) {
      setState(() => _downloadingId = null);
      if (error != null) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error)));
      }
    }
  }

  Future<void> _downloadCertificate() async {
    setState(() => _downloadingCertificate = true);
    final error = await FileDownloadService.downloadAndOpen('/letters/salary-certificate', 'salary-certificate.pdf');
    if (mounted) {
      setState(() => _downloadingCertificate = false);
      if (error != null) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('الراتب')),
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
                    color: const Color(0xFF11998E),
                    child: ListTile(
                      leading: const Icon(Icons.description_outlined, color: Colors.white),
                      title: const Text('شهادة الراتب (HR Letter)', style: TextStyle(color: Colors.white)),
                      subtitle: const Text('تحميل شهادة راتب PDF جاهزة', style: TextStyle(color: Colors.white70)),
                      trailing: _downloadingCertificate
                          ? const SizedBox(
                              width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Icon(Icons.download, color: Colors.white),
                      onTap: _downloadingCertificate ? null : _downloadCertificate,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text('قسائم الراتب', style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  if (_payslips.isEmpty)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 24),
                      child: Center(child: Text('لا توجد قسائم راتب معتمدة بعد', style: TextStyle(color: Colors.grey))),
                    )
                  else
                    ..._payslips.map((p) => Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            title: Text(p.monthLabel),
                            subtitle: Text('صافي الراتب: ${p.netSalary.toStringAsFixed(2)} جنيه'),
                            trailing: _downloadingId == p.id
                                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                                : IconButton(
                                    icon: const Icon(Icons.download_outlined),
                                    onPressed: () => _downloadPayslip(p),
                                  ),
                            onTap: () => _showDetails(p),
                          ),
                        )),
                ],
              ),
            ),
    );
  }

  void _showDetails(Payslip p) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(p.monthLabel, style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            _row('الراتب الأساسي', p.basicSalary),
            _row('الإضافات الثابتة', p.fixedAllowances),
            _row('الأوفرتايم', p.overtimeAmount),
            _row('العمولات', p.commissionsAmount),
            const Divider(),
            _row('إجمالي الراتب', p.grossSalary),
            _row('خصم التأخير', -p.lateDeductions),
            _row('خصم الغياب', -p.absenceDeductions),
            _row('خصومات أخرى', -p.deductionsAmount),
            _row('قسط السلفة', -p.advanceInstallment),
            _row('التأمينات', -p.insuranceDeduction),
            const Divider(),
            _row('الصافي', p.netSalary, bold: true),
          ],
        ),
      ),
    );
  }

  Widget _row(String label, double value, {bool bold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
          Text(
            value.toStringAsFixed(2),
            style: TextStyle(
              fontWeight: bold ? FontWeight.bold : FontWeight.normal,
              color: value < 0 ? Colors.red : null,
            ),
          ),
        ],
      ),
    );
  }
}
