class AttendanceRecord {
  final int id;
  final DateTime attendanceDate;
  final String? checkInTime;
  final String? checkOutTime;
  final int lateMinutes;
  final double overtimeHours;
  final int status;
  final double? checkInLat;
  final double? checkInLng;
  final double? checkOutLat;
  final double? checkOutLng;

  AttendanceRecord({
    required this.id,
    required this.attendanceDate,
    this.checkInTime,
    this.checkOutTime,
    required this.lateMinutes,
    required this.overtimeHours,
    required this.status,
    this.checkInLat,
    this.checkInLng,
    this.checkOutLat,
    this.checkOutLng,
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
      checkInLat: json['check_in_lat'] != null ? double.tryParse('${json['check_in_lat']}') : null,
      checkInLng: json['check_in_lng'] != null ? double.tryParse('${json['check_in_lng']}') : null,
      checkOutLat: json['check_out_lat'] != null ? double.tryParse('${json['check_out_lat']}') : null,
      checkOutLng: json['check_out_lng'] != null ? double.tryParse('${json['check_out_lng']}') : null,
    );
  }

  bool get hasCheckedIn => checkInTime != null;
  bool get hasCheckedOut => checkOutTime != null;
}
