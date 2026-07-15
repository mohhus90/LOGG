class Employee {
  final int id;
  final String name;
  final String? photo;
  final String? department;
  final String? job;
  final bool locationTrackingEnabled;

  Employee({
    required this.id,
    required this.name,
    this.photo,
    this.department,
    this.job,
    required this.locationTrackingEnabled,
  });

  factory Employee.fromJson(Map<String, dynamic> json) {
    return Employee(
      id: json['id'],
      name: json['name'] ?? '',
      photo: json['photo'],
      department: json['department'],
      job: json['job'],
      locationTrackingEnabled: json['location_tracking_enabled'] == true,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'photo': photo,
        'department': department,
        'job': job,
        'location_tracking_enabled': locationTrackingEnabled,
      };
}
