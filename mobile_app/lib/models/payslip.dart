class Payslip {
  final int id;
  final int month;
  final int year;
  final double grossSalary;
  final double netSalary;
  final double basicSalary;
  final double fixedAllowances;
  final double overtimeAmount;
  final double commissionsAmount;
  final double lateDeductions;
  final double absenceDeductions;
  final double deductionsAmount;
  final double advanceInstallment;
  final double insuranceDeduction;
  final int status; // 1 draft, 2 approved, 3 paid

  Payslip({
    required this.id,
    required this.month,
    required this.year,
    required this.grossSalary,
    required this.netSalary,
    required this.basicSalary,
    required this.fixedAllowances,
    required this.overtimeAmount,
    required this.commissionsAmount,
    required this.lateDeductions,
    required this.absenceDeductions,
    required this.deductionsAmount,
    required this.advanceInstallment,
    required this.insuranceDeduction,
    required this.status,
  });

  factory Payslip.fromJson(Map<String, dynamic> json) {
    double n(dynamic v) => double.tryParse('$v') ?? 0;
    return Payslip(
      id: json['id'],
      month: json['month'],
      year: json['year'],
      grossSalary: n(json['gross_salary']),
      netSalary: n(json['net_salary']),
      basicSalary: n(json['basic_salary']),
      fixedAllowances: n(json['fixed_allowances']),
      overtimeAmount: n(json['overtime_amount']),
      commissionsAmount: n(json['commissions_amount']),
      lateDeductions: n(json['late_deductions']),
      absenceDeductions: n(json['absence_deductions']),
      deductionsAmount: n(json['deductions_amount']),
      advanceInstallment: n(json['advance_installment']),
      insuranceDeduction: n(json['insurance_deduction']),
      status: json['status'] ?? 1,
    );
  }

  static const _monthNames = [
    '', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
  ];

  String get monthLabel => '${_monthNames[month]} $year';

  String get statusLabel => switch (status) {
        3 => 'مدفوع',
        2 => 'معتمد',
        _ => 'مسودة',
      };
}
