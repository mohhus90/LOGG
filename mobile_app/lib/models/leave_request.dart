class LeaveRequest {
  final int id;
  final String requestType;
  final DateTime startDate;
  final DateTime? endDate;
  final String? timeFrom;
  final String? timeTo;
  final int daysCount;
  final String? reason;
  final int status; // 0 pending, 1 approved, 2 rejected, 3 cancelled

  LeaveRequest({
    required this.id,
    required this.requestType,
    required this.startDate,
    this.endDate,
    this.timeFrom,
    this.timeTo,
    required this.daysCount,
    this.reason,
    required this.status,
  });

  factory LeaveRequest.fromJson(Map<String, dynamic> json) {
    return LeaveRequest(
      id: json['id'],
      requestType: json['request_type'],
      startDate: DateTime.parse(json['start_date']),
      endDate: json['end_date'] != null ? DateTime.parse(json['end_date']) : null,
      timeFrom: json['time_from'],
      timeTo: json['time_to'],
      daysCount: json['days_count'] ?? 1,
      reason: json['reason'],
      status: json['status'] ?? 0,
    );
  }

  static const _typeLabels = {
    'annual_vacation': '🏖 إجازة اعتيادية',
    'casual_vacation': '📅 إجازة عارضة',
    'late_permission': '⏰ إذن تأخير',
    'early_leave': '🚪 إذن انصراف مبكر',
    'mission': '🏢 مأمورية',
    'resignation': '📝 طلب استقالة',
  };

  String get typeLabel => _typeLabels[requestType] ?? requestType;

  String get statusLabel => switch (status) {
        0 => 'قيد الانتظار',
        1 => 'مقبول',
        2 => 'مرفوض',
        _ => 'ملغي',
      };

  bool get isPending => status == 0;
}

class VacationBalance {
  final int annualBalance;
  final int annualRemaining;
  final int casualBalance;
  final int casualRemaining;

  VacationBalance({
    required this.annualBalance,
    required this.annualRemaining,
    required this.casualBalance,
    required this.casualRemaining,
  });

  factory VacationBalance.fromJson(Map<String, dynamic> json) {
    return VacationBalance(
      annualBalance: json['annual_balance'] ?? 0,
      annualRemaining: json['annual_remaining'] ?? 0,
      casualBalance: json['casual_balance'] ?? 0,
      casualRemaining: json['casual_remaining'] ?? 0,
    );
  }
}
