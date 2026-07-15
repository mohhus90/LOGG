class AttendanceRecord {
  final int id;
  final DateTime attendanceDate;
  final String? checkInTime;
  final String? checkOutTime;
  final int lateMinutes;
  final double overtimeHours;
  final int status;

  AttendanceRecord({
    required this.id,
    required this.attendanceDate,
    this.checkInTime,
    this.checkOutTime,
    required this.lateMinutes,
    required this.overtimeHours,
    required this.status,
  });

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      id: json['id'],
      attendanceDate: DateTime.parse(json['attendance_date']),
      checkInTime: json['check_in_time'],
      checkOutTime: json['check_out_time'],
      lateMinutes: json['late_minutes'] ?? 0,
      overtimeHours: double.tryParse('${json['overtime_hours'] ?? 0}') ?? 0,
      status: json['status'] ?? 1,
    );
  }

  bool get hasCheckedIn => checkInTime != null;
  bool get hasCheckedOut => checkOutTime != null;
}
