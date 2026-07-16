class Company {
  final int comCode;
  final String name;

  Company({required this.comCode, required this.name});

  factory Company.fromJson(Map<String, dynamic> json) {
    return Company(
      comCode: json['com_code'],
      name: json['name'] ?? '',
    );
  }
}
