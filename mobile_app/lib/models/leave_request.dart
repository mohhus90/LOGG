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
      daysCount: (double.tryParse('${json['days_count']}') ?? 1).round(),
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
  final double annualBalance;
  final double annualRemaining;
  final double casualBalance;
  final double casualRemaining;

  VacationBalance({
    required this.annualBalance,
    required this.annualRemaining,
    required this.casualBalance,
    required this.casualRemaining,
  });

  static double _num(dynamic v) => double.tryParse('$v') ?? 0;

  factory VacationBalance.fromJson(Map<String, dynamic> json) {
    return VacationBalance(
      annualBalance: _num(json['annual_balance']),
      annualRemaining: _num(json['annual_remaining']),
      casualBalance: _num(json['casual_balance']),
      casualRemaining: _num(json['casual_remaining']),
    );
  }
}

/// Formats a day count without a trailing ".0" for whole numbers.
String formatDays(double v) => v == v.roundToDouble() ? v.toInt().toString() : v.toStringAsFixed(1);
