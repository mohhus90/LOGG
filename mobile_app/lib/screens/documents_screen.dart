import 'package:flutter/material.dart';

import '../models/document.dart';
import '../services/api_client.dart';
import '../services/file_download_service.dart';

class DocumentsScreen extends StatefulWidget {
  const DocumentsScreen({super.key});

  @override
  State<DocumentsScreen> createState() => _DocumentsScreenState();
}

class _DocumentsScreenState extends State<DocumentsScreen> {
  List<EmployeeDocument> _documents = [];
  bool _loading = true;
  String? _error;
  int? _busyId;

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
      final response = await ApiClient.instance.dio.get('/documents');
      setState(() => _documents = (response.data as List).map((e) => EmployeeDocument.fromJson(e)).toList());
    } catch (e) {
      setState(() => _error = ApiClient.errorMessage(e));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _download(EmployeeDocument doc) async {
    setState(() => _busyId = doc.id);
    final error = await FileDownloadService.downloadAndOpen('/documents/${doc.id}/download', doc.originalName);
    if (mounted) {
      setState(() => _busyId = null);
      if (error != null) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(error)));
      }
    }
  }

  Future<void> _requestAccess(EmployeeDocument doc) async {
    setState(() => _busyId = doc.id);
    try {
      await ApiClient.instance.dio.post('/documents/${doc.id}/request-access');
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم إرسال طلب الوصول، بانتظار الموافقة')));
      }
      await _load();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(ApiClient.errorMessage(e))));
    } finally {
      if (mounted) setState(() => _busyId = null);
    }
  }

  Widget _trailingFor(EmployeeDocument doc) {
    if (_busyId == doc.id) {
      return const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2));
    }
    switch (doc.accessStatus) {
      case DocumentAccessStatus.approved:
        return IconButton(icon: const Icon(Icons.download_outlined), onPressed: () => _download(doc));
      case DocumentAccessStatus.pending:
        return const Chip(
          label: Text('بانتظار الموافقة', style: TextStyle(fontSize: 11, color: Colors.white)),
          backgroundColor: Colors.orange,
          padding: EdgeInsets.zero,
          materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
        );
      case DocumentAccessStatus.none:
        return TextButton(onPressed: () => _requestAccess(doc), child: const Text('طلب الوصول'));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('مستنداتي')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _load,
              child: _error != null
                  ? ListView(children: [
                      Container(
                        margin: const EdgeInsets.all(16),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(color: Colors.red.shade50, borderRadius: BorderRadius.circular(8)),
                        child: Text(_error!, style: TextStyle(color: Colors.red.shade700)),
                      ),
                    ])
                  : _documents.isEmpty
                      ? ListView(children: const [
                          Padding(
                            padding: EdgeInsets.symmetric(vertical: 80),
                            child: Center(child: Text('لا توجد مستندات مرفوعة بعد', style: TextStyle(color: Colors.grey))),
                          ),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: _documents.length,
                          itemBuilder: (context, i) {
                            final doc = _documents[i];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 8),
                              child: ListTile(
                                leading: const CircleAvatar(
                                  backgroundColor: Color(0xFFE0F2F1),
                                  child: Icon(Icons.description_outlined, color: Color(0xFF11998E)),
                                ),
                                title: Text(doc.typeLabel),
                                subtitle: Text(doc.originalName, maxLines: 1, overflow: TextOverflow.ellipsis),
                                trailing: _trailingFor(doc),
                              ),
                            );
                          },
                        ),
            ),
    );
  }
}
