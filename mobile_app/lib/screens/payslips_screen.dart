import 'package:flutter/material.dart';

import '../models/payslip.dart';
import '../services/api_client.dart';
import '../services/file_download_service.dart';
import 'pdf_viewer_screen.dart';

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
  bool _certificateBusy = false;
  String _certificateStatus = 'none'; // none | pending | approved

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
        ApiClient.instance.dio.get('/payslips'),
        ApiClient.instance.dio.get('/letters/salary-certificate/status'),
      ]);
      setState(() {
        _payslips = (results[0].data['data'] as List).map((e) => Payslip.fromJson(e)).toList();
        _certificateStatus = results[1].data['access_status'] ?? 'none';
      });
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _viewPayslip(Payslip p) async {
    setState(() => _downloadingId = p.id);
    try {
      final bytes = await FileDownloadService.fetchBytes('/payslips/${p.id}/pdf');
      if (mounted) {
        Navigator.of(context).push(MaterialPageRoute(
          builder: (_) => PdfViewerScreen(title: p.monthLabel, bytes: bytes, fileName: 'payslip-${p.year}-${p.month}.pdf'),
        ));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
    } finally {
      if (mounted) setState(() => _downloadingId = null);
    }
  }

  Future<void> _viewCertificate() async {
    setState(() => _certificateBusy = true);
    try {
      final bytes = await FileDownloadService.fetchBytes('/letters/salary-certificate');
      if (mounted) {
        Navigator.of(context).push(MaterialPageRoute(
          builder: (_) => PdfViewerScreen(title: 'شهادة راتب', bytes: bytes, fileName: 'salary-certificate.pdf'),
        ));
      }
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
    } finally {
      if (mounted) setState(() => _certificateBusy = false);
    }
  }

  Future<void> _requestCertificate() async {
    final reasonController = TextEditingController();
    final reason = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('طلب شهادة راتب'),
        content: TextField(
          controller: reasonController,
          maxLines: 2,
          autofocus: true,
          decoration: const InputDecoration(
            labelText: 'سبب الطلب',
            hintText: 'مثال: لتقديمها للبنك',
            border: OutlineInputBorder(),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('إلغاء')),
          FilledButton(
            onPressed: () => Navigator.pop(context, reasonController.text.trim()),
            child: const Text('إرسال'),
          ),
        ],
      ),
    );

    if (reason == null || reason.isEmpty) return;

    setState(() => _certificateBusy = true);
    try {
      await ApiClient.instance.dio.post('/letters/salary-certificate/request-access', data: {'reason': reason});
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم إرسال الطلب، بانتظار الموافقة')));
      }
      await _load();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
    } finally {
      if (mounted) setState(() => _certificateBusy = false);
    }
  }

  Widget _certificateTile() {
    if (_certificateBusy) {
      return const ListTile(
        leading: Icon(Icons.description_outlined, color: Colors.white),
        title: Text('شهادة الراتب (HR Letter)', style: TextStyle(color: Colors.white)),
        trailing: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white)),
      );
    }
    switch (_certificateStatus) {
      case 'approved':
        return ListTile(
          leading: const Icon(Icons.description_outlined, color: Colors.white),
          title: const Text('شهادة الراتب (HR Letter)', style: TextStyle(color: Colors.white)),
          subtitle: const Text('تمت الموافقة - اضغط للعرض', style: TextStyle(color: Colors.white70)),
          trailing: const Icon(Icons.picture_as_pdf, color: Colors.white),
          onTap: _viewCertificate,
        );
      case 'pending':
        return const ListTile(
          leading: Icon(Icons.description_outlined, color: Colors.white),
          title: Text('شهادة الراتب (HR Letter)', style: TextStyle(color: Colors.white)),
          subtitle: Text('⏳ بانتظار موافقة المسؤول', style: TextStyle(color: Colors.white70)),
        );
      default:
        return ListTile(
          leading: const Icon(Icons.description_outlined, color: Colors.white),
          title: const Text('شهادة الراتب (HR Letter)', style: TextStyle(color: Colors.white)),
          subtitle: const Text('تحتاج طلب وموافقة قبل التنزيل', style: TextStyle(color: Colors.white70)),
          trailing: TextButton(
            onPressed: _requestCertificate,
            style: TextButton.styleFrom(foregroundColor: Colors.white, backgroundColor: Colors.white24),
            child: const Text('طلب'),
          ),
        );
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
                    child: _certificateTile(),
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
                                    onPressed: () => _viewPayslip(p),
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
