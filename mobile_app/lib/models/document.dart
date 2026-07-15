class EmployeeDocument {
  final int id;
  final String docType;
  final String originalName;
  final DateTime createdAt;

  EmployeeDocument({
    required this.id,
    required this.docType,
    required this.originalName,
    required this.createdAt,
  });

  factory EmployeeDocument.fromJson(Map<String, dynamic> json) {
    return EmployeeDocument(
      id: json['id'],
      docType: json['doc_type'],
      originalName: json['doc_original_name'],
      createdAt: DateTime.parse(json['created_at']),
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
