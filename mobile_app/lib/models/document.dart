enum DocumentAccessStatus { none, pending, approved }

class EmployeeDocument {
  final int id;
  final String docType;
  final String originalName;
  final DateTime createdAt;
  final DocumentAccessStatus accessStatus;

  EmployeeDocument({
    required this.id,
    required this.docType,
    required this.originalName,
    required this.createdAt,
    required this.accessStatus,
  });

  factory EmployeeDocument.fromJson(Map<String, dynamic> json) {
    return EmployeeDocument(
      id: json['id'],
      docType: json['doc_type'],
      originalName: json['doc_original_name'],
      createdAt: DateTime.parse(json['created_at']),
      accessStatus: switch (json['access_status']) {
        'approved' => DocumentAccessStatus.approved,
        'pending' => DocumentAccessStatus.pending,
        _ => DocumentAccessStatus.none,
      },
    );
  }

  static const _typeLabels = {
    'photo': 'صورة شخصية',
    'cv': 'السيرة الذاتية',
    'national_id': 'صورة الرقم القومي',
    'education_cert': 'شهادة المؤهل',
    'military_cert': 'شهادة الجيش',
    'criminal_record': 'فيش جنائي',
    'birth_cert': 'شهادة الميلاد',
    'work_history': 'كعب عمل / برينت تأمينات',
    'insurance_proof': 'إثبات القيد',
  };

  String get typeLabel => _typeLabels[docType] ?? docType;
}
