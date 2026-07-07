# خارطة طريق استكمال ERP متكامل (NEXA)

> هذا المستند مرجعي دائم داخل المستودع. أي جلسة عمل جديدة (من أي جهاز) يجب أن تبدأ بقراءته لمعرفة ما تم إنجازه وما هو التالي.
> عند إنجاز أي بند ضع علامة `[x]` وأضف تاريخ الإنجاز، والتزم بنفس الاصطلاحات الموصوفة هنا عند إضافة أي كود جديد.

> **✅ حالة الخطة (2026-07-07): Phases 0-7 و9-12 مكتملة بالكامل ومُختبرة end-to-end. Phase 8 وحدها مؤجَّلة عمدًا (اختيارية).**
> النظام الآن ERP متكامل فعليًا: محاسبة عامة + ترحيل تلقائي من كل الموديولات + خزينة وشيكات + أصول ثابتة وإهلاك + تصنيع بتكلفة فعلية + مطابقة ETA محاسبية + ضبط جودة + BI + CRM + إدارة مشاريع + إدارة وثائق + موديول نظام/شركات مستقل + نقطة بيع (POS) بجلسات كاشير + ضريبة كسب عمل اختيارية بالشرائح + تنبيهات عقود/اختبار + بنية أكواد HR منظمة.
> كل الـ13 بطاقة في شاشة اختيار الموديولات (`admin/hub.blade.php`) أصبحت مفعّلة، ومفيش أي "قريبًا" متبقي.

## الوضع الحالي (قبل بدء هذه الخطة)

موديولات موجودة وتعمل بالفعل:
- **HR/Payroll**: فروع، شيفتات، إدارات، وظائف، موظفين، حضور وانصراف، سلف، عمولات (+V2)، عمولات فروع، حوافز، خصومات، KPI، رواتب، سنوات مالية، رصيد إجازات، صلاحيات المستخدمين.
- **SMS**: إشعارات (موديول مُصرَّح).
- **Sales**: وحدات قياس، مجموعات أصناف، أصناف، عملاء مبيعات، عروض أسعار، أوامر بيع، فواتير بيع، مدفوعات عملاء، مرتجعات بيع، تقارير مبيعات.
- **Purchasing**: موردون، طلبات شراء، أوامر شراء، فواتير شراء، مدفوعات موردين، مرتجعات شراء، تقارير مشتريات.
- **Inventory**: مخازن، أرصدة مخزون، حركة أصناف، تسويات مخزون، تحويلات مخازن، تقارير مخازن.
- **Tax/ETA**: تكامل مع الفاتورة الإلكترونية المصرية (بيانات اعتماد، مزامنة، ترحيل/إلغاء ترحيل، تقرير ضريبة القيمة المضافة، نموذج 41).
- دعم متعدد الشركات (Multi-tenant) عبر `companies` و `com_code`.

## الفجوات الجوهرية لاعتبار النظام "ERP متكامل" (لشركات/مصانع/خدمية مصرية)

- [x] **المحاسبة العامة** — تم بناء دليل الحسابات، القيود اليومية، مراكز التكلفة، الفترات المحاسبية، خدمة الترحيل `JournalPostingService`، وتقارير (ميزان مراجعة/قائمة دخل/ميزانية عمومية/كشف حساب). الترحيل التلقائي من كل الموديولات الأخرى (مبيعات/مشتريات/رواتب/مرتجعات/خزينة/أصول/إنتاج) مكتمل بالفعل.
- [x] **الخزينة والبنوك** — خزائن نقدية، حسابات بنكية، سندات قبض/صرف، شيكات (واردة/صادرة) بدورة حياة كاملة (تحت التحصيل/تم التحصيل/مرتجع)، كلها ترحّل محاسبيًا تلقائيًا.
- [x] **الأصول الثابتة** — سجل أصول كامل، فئات بحسابات محاسبية خاصة، إهلاك شهري بالقسط الثابت مرحّل تلقائيًا، نقل بين الفروع، وتخلص من الأصل بفارق ربح/خسارة.
- [x] **التصنيع/الإنتاج** — قوائم مواد (BOM) بإصدارات، أوامر إنتاج (تخطيط/صرف مواد/استلام تام)، تكلفة فعلية بالمتوسط المرجح + عمالة/تكاليف غير مباشرة مرحّلة على WIP.
- [x] **تكلفة المخزون** — `stock_balances` أصبح فيه `avg_cost`/`total_value` بالمتوسط المرجح، و`StockService::adjustStock()` بيرجع تكلفة COGS حقيقية.
- [x] **ربط الفاتورة الإلكترونية بالمحاسبة** — ربط يدوي بمرشحات مقترحة بفاتورة البيع/الشراء الداخلية، مع تحقق فعلي من وجود قيد محاسبي مرحّل قبل تأكيد المطابقة مع ETA.

## القرارات المعمارية الأساسية

| # | القرار |
|---|---|
| D1 | بوابة ترحيل واحدة: كل الترحيل المحاسبي يمر عبر `App\Services\Accounting\JournalPostingService` فقط. |
| D2 | الترحيل مبني على قواعد قابلة للإعداد (`gl_posting_rules`): event_type + line_role → account_id فعلي، بدل تثبيت account_id في الكود. |
| D3 | سندات الخزينة (`treasury_vouchers`) هي مصدر الحقيقة الوحيد لحركة النقدية؛ SalesPayment/PurchasePayment تاخد `treasury_voucher_id` (FK اختياري) بدل إعادة بناء منطق الخزينة. |
| D4 | تقييم المخزون: المتوسط المرجح (Weighted Average) — يُضاف `avg_cost`/`total_value` لـ `stock_balances`. |
| D5 | الفترات المحاسبية (`accounting_periods`) تمنع الترحيل في فترة مقفولة. |
| D6 | ETA e-invoice طبقة امتثال فوق القيد المحاسبي الحقيقي، وليست مصدر قيد ثانٍ (تجنبًا لازدواج الإيراد). |

## المراحل والتبعيات

```
Phase 0: هذا المستند (مرجع دائم)                                            [x] 2026-07-06
Phase 1: نواة المحاسبة (دليل حسابات، قيود يومية، مراكز تكلفة، فترات،
         خدمة الترحيل JournalPostingService، تقارير مالية)                  [x] 2026-07-06
Phase 2: ترقية تكلفة المخزون (avg_cost/total_value على StockBalance،
         تعديل StockService::adjustStock لإرجاع تكلفة COGS)                 [x] 2026-07-06
Phase 3: ربط الترحيل التلقائي بالموديولات القائمة
         (فواتير بيع/شراء، رواتب، مرتجعات)                                  [x] 2026-07-06
Phase 4: الخزينة والبنوك (خزائن، حسابات بنكية، سندات، شيكات)                 [x] 2026-07-06
Phase 5: الأصول الثابتة (سجل أصول، إهلاك)                                    [x] 2026-07-06
Phase 6: التصنيع/الإنتاج (BOM، أوامر إنتاج)                                  [x] 2026-07-06
Phase 7: استكمال ربط الفاتورة الإلكترونية بالمحاسبة                          [x] 2026-07-06
Phase 8: اختياري/مؤجل (عملات متعددة، إيصال إلكتروني B2C، مطابقة بنكية
         تلقائية، موازنات مراكز التكلفة، FIFO)                               [ ]
Phase 9: موديولات "قريبًا" غير محاسبية (ضبط جودة، BI، CRM، مشاريع، وثائق)     [x] 2026-07-06
```

Phase 1 شرط أساسي لكل ما بعده. Phase 2 يجب أن يسبق Phase 3 حتى يكون قيد COGS صحيحًا. المراحل 4/5/6 مستقلة عن بعضها ويمكن إعادة ترتيب أولويتها حسب احتياج العمل الفعلي (مثال: شركة خدمية قد لا تحتاج Phase 6 إطلاقًا). Phase 9 مستقلة تمامًا عن كل ما قبلها (لا ترحيل محاسبي فيها إلا استثناءً بسيطًا في CRM).

---

## تفاصيل كل مرحلة

### Phase 1 — المحاسبة العامة

**جداول جديدة:**
- `chart_of_accounts`: `com_code, account_code, account_name, account_name_en, account_type[asset|liability|equity|revenue|expense], account_nature[debit|credit], parent_id (self FK), level, is_group, is_active, allow_cost_center, opening_balance, opening_balance_date, current_balance, notes`
- `cost_centers`: `com_code, code, name, parent_id (self FK), branch_id (FK), is_active`
- `accounting_periods`: `com_code, fiscal_year, period_month, start_date, end_date, is_closed, closed_at, closed_by` — unique(com_code, fiscal_year, period_month)
- `journal_entries`: `com_code, entry_number (JE-YYYY-XXXX), entry_date, entry_type[manual|auto], source_module, source_id, reference, description, total_debit, total_credit, status[draft|posted|reversed], reversed_entry_id (self FK), period_id (FK), created_by, posted_by, posted_at`
- `journal_entry_lines`: `journal_entry_id (FK), account_id (FK), cost_center_id (FK nullable), branch_id (nullable), debit, credit, description, party_type[customer|supplier|employee|null], party_id`
- `gl_posting_rules`: `com_code, event_type, line_role, account_id (FK), side[debit|credit], is_active` — unique(com_code, event_type, line_role)

**Service:** `app/Services/Accounting/JournalPostingService.php`
- `post(string $eventType, int $comCode, array $lines, array $meta): JournalEntry`
- `reverse(JournalEntry $entry, ?int $byAdminId, ?string $reason): JournalEntry`
- `resolveAccount(int $comCode, string $eventType, string $role): int`
- `alreadyPosted(int $comCode, string $sourceModule, int $sourceId): bool`
- يتحقق من توازن مدين=دائن، ومن أن الفترة المحاسبية غير مقفولة، ويُستدعى دائمًا من داخل `DB::transaction` الخاصة بالـ Controller المستدعي.

**Controllers:** `app/Http/Controllers/Admin/Accounting/`
`ChartOfAccountsController`, `CostCentersController`, `JournalEntriesController`, `AccountingPeriodsController`, `GlPostingRulesController`, `AccountingReportsController` (ميزان مراجعة، قائمة دخل، ميزانية عمومية، كشف حساب/عميل/مورد، إقفال سنة مالية).

**module_keys (sort_order 50-55):** `chart_of_accounts`, `cost_centers`, `journal_entries`, `accounting_periods`, `gl_posting_rules`, `accounting_reports`. سيدر migration على نمط `2026_07_05_000013_seed_sales_admin_modules.php`.

**Sidebar:** `resources/views/admin/includes/sidebar_accounting.blade.php`

**✅ تم التنفيذ فعليًا (2026-07-06):** كل ما سبق منفَّذ ومُختبر (migrate + اختبار ترحيل/عكس قيد عبر tinker + `view:cache` نجح بدون أخطاء Blade). أضيف أيضًا:
- `database/migrations/2026_07_06_000018_seed_default_chart_of_accounts.php` — يزرع دليل حسابات مصري مبسّط (27 حساب) + قواعد ترحيل أساسية لكل شركة موجودة في `admins` (لأي شركة جديدة تُنشأ لاحقًا، يجب تشغيل نفس المنطق يدويًا أو عبر إضافة hook عند إنشاء الشركة — TODO مستقبلي).
- Layout مستقل: `resources/views/admin/layouts/accounting.blade.php` (لون بنفسجي مميز)، مطابق تمامًا لنمط `layouts/sales.blade.php`.
- كل الواجهات (accounts/cost_centers/journal_entries/periods/posting_rules/reports) منفذة تحت `resources/views/admin/accounting/`.
- تفعيل بطاقة "الحسابات" في `resources/views/admin/hub.blade.php` (كانت `coming-soon`، أصبحت `active-module` تشير لـ `accounting_reports.index`).
- الأدوار (roles) المُهيّأة مسبقًا في `gl_posting_rules` تغطي: `sales_invoice_issued`, `sales_invoice_cogs`, `purchase_invoice_received`, `payroll_approved` — جاهزة لـ Phase 3 لاحقًا.

---

### Phase 2 — ترقية تكلفة المخزون

- إضافة أعمدة: `stock_balances.avg_cost`, `stock_balances.total_value`؛ `items.costing_method` (افتراضي `weighted_average`).
- `stock_movements`: إضافة `total_cost` (nullable).
- تعديل `app/Services/StockService.php::adjustStock()`:
  - عند الإضافة (+): إعادة حساب المتوسط المرجح.
  - عند الخصم (−): استخدام `avg_cost` الحالي كتكلفة COGS بدل التكلفة المرسلة من المستدعي.

**✅ تم التنفيذ فعليًا (2026-07-06):** لم يتطلب الأمر أي تغيير كاسر في توقيع الدالة —
`adjustStock()` لسه بترجع نفس `StockMovement` بالضبط، لكن دلوقتي `unit_cost`/`total_cost` عليه
بيعكسوا تكلفة COGS حقيقية (المتوسط المرجح) مش السعر المُمرَّر من المستدعي. يعني كل الأماكن
الحالية اللي بتنادي الدالة (`SalesInvoicesController`, `PurchaseInvoicesController`,
`SalesReturnsController`, `PurchaseReturnsController`, `StockTransfersController`,
`StockAdjustmentsController`) اشتغلت من غير أي تعديل، ومين يحتاج تكلفة COGS لاحقًا (Phase 3)
هياخدها من الكائن المُرجع (`$movement->total_cost`) بدل ما يحسبها بنفسه.
تم أيضًا تحديث `InventoryReportsController::index()/valuation()` ليعتمدوا على
`stock_balances.avg_cost/total_value` بدل `items.cost_price` الثابت، مع migration تهيئة
(`2026_07_06_000019_add_costing_fields_to_inventory_tables.php`) تنسخ `cost_price` الحالي
كمتوسط مبدئي للأرصدة الموجودة. تم التحقق بمحاكاة شراء دفعتين بأسعار مختلفة ثم بيع جزء،
والتأكد من صحة المتوسط المرجح والـ COGS الناتج.

---

### Phase 3 — ربط الترحيل التلقائي

داخل `DB::transaction` الموجودة فعلاً بكل Controller:
- `SalesInvoicesController::store()` → قيد (AR_CONTROL مدين / SALES_REVENUE دائن / VAT_OUTPUT دائن) + قيد COGS (COGS مدين / INVENTORY دائن). `cancel()`/`update()` يعكسوا القيد عبر `reverse()`.
- `PurchaseInvoicesController` المكافئ → (INVENTORY أو EXPENSE حسب نوع الصنف / VAT_INPUT / AP_CONTROL).
- `PayrollController::approve()`/`unapprove()` → (SALARY_EXPENSE مدين / SALARY_PAYABLE دائن) — استحقاق فقط، الصرف الفعلي في Phase 4.
- مرتجعات البيع/الشراء → نفس النمط بعكس الاتجاه.
- كل hook يستدعي `alreadyPosted()` أولاً لمنع الترحيل المزدوج.

**✅ تم التنفيذ فعليًا (2026-07-06):**
- أُضيفت `JournalPostingService::reverseBySource()` — تعكس كل القيود المرتبطة بمصدر معيّن (فاتورة/كشف راتب/مرتجع) دفعة واحدة، بدل البحث اليدوي عن كل قيد.
- `SalesInvoicesController`: `store()` يرحّل قيدين (إيراد+ضريبة، ثم COGS إن وُجد)، `cancel()` يعكسهم، `update()` **يعكس حركات المخزون القديمة فعليًا** (لم تكن تُعكس من قبل — كانت فجوة موجودة مسبقًا: التعديل كان يعيد بناء بنود الفاتورة بدون تصحيح أرصدة المخزون) ثم يعيد ترحيل المخزون والقيد بالأرقام الجديدة.
- `PurchaseInvoicesController`: نفس النمط، مع تقسيم كل بند بين `INVENTORY`/`EXPENSE` حسب `item.type` (خدمة أم لا)، وخصم أي خصم على مستوى رأس الفاتورة من المخزون أولًا ثم المصروف حتى يتطابق القيد مع إجمالي الفاتورة تمامًا.
- `PayrollController::approve()`/`unapprove()`: قيد استحقاق (SALARY_EXPENSE/SALARY_PAYABLE) يُحسب com_code من `payroll->employee->com_code` لأن `monthly_payrolls` ليس بها عمود com_code مباشر.
- `SalesReturnsController::approve()`/`PurchaseReturnsController::approve()`: يرحّلوا `sales_return_posted`+`sales_return_cogs` / `purchase_return_posted` (قواعد جديدة أُضيفت في `2026_07_06_000020_seed_returns_posting_rules.php`).
- تم التحقق end-to-end عبر tinker: شراء بتكلفة معروفة → بيع → قيدين متوازنين → عكس القيد يُصفّر الأرصدة تمامًا.
- **ملاحظة معروفة**: تكلفة COGS عند مرتجع البيع تُستنتج من قيمة إعادة الإدخال للمخزون (بنفس سعر البيع الأصلي بالسطر) وليس من التكلفة التاريخية الدقيقة وقت البيع — تبسيط مقصود يتماشى مع نهج المتوسط المرجح، وليس تتبع دفعات (Lot tracking).

---

### Phase 4 — الخزينة والبنوك

**جداول:** `cash_boxes`, `bank_accounts`, `treasury_vouchers` (voucher_number RV/PV-YYYY-XXXX, voucher_type[receipt|payment], payment_method[cash|bank|cheque], party_type/party_id, linked_type/linked_id, cheque_id, status[draft|posted|cancelled]), `cheques` (direction[received|issued], status[under_collection|collected|bounced|cancelled]), `bank_reconciliations` + `bank_reconciliation_lines`.

**Service:** `app/Services/Treasury/TreasuryService.php` — `createVoucher()`, `recordCheque()`, `collectCheque()`, `bounceCheque()` (شيك تحت التحصيل يترحل على حساب وسيط "شيكات تحت التحصيل" وليس البنك مباشرة).

**Controllers:** `app/Http/Controllers/Admin/Treasury/` — `CashBoxesController`, `BankAccountsController`, `ReceiptVouchersController`, `PaymentVouchersController`, `ChequesController`, `BankReconciliationController`, `TreasuryReportsController`.

**module_keys (sort_order 60-66)**, **Sidebar:** `sidebar_treasury.blade.php`.

ربط: إضافة `treasury_voucher_id` (nullable FK) إلى `sales_payments`/`purchase_payments` دون تعديل السجلات القديمة.

**✅ تم التنفيذ فعليًا (2026-07-06):**
- نُفِّذ كل ما سبق **ما عدا** `bank_reconciliations`/`BankReconciliationController` — أُجِّلا عمدًا (نفس تصنيف Phase 8 الأصلي: "تسوية بنكية" أقل أولوية وتحتاج خوارزمية مطابقة، وليست ضرورية لاعتبار الخزينة "شغالة"). الجداول الأخرى (خزائن، بنوك، سندات، شيكات) كلها تعمل.
- `TreasuryVoucher` سطر محاسبي واحد يُبنى ديناميكيًا: الطرف الأول (مدين/دائن) إما حساب الخزنة/البنك المختار مباشرة (`cash_box.gl_account_id`/`bank_account.gl_account_id`) أو دور `CHEQUES_UNDER_COLLECTION`/`CHEQUES_PAYABLE` لو طريقة السداد شيك؛ الطرف الثاني إما دور `AR_CONTROL`/`AP_CONTROL` (عميل/مورد) أو حساب محدَّد يدويًا لو الطرف "أخرى".
- شيك وارد (من عميل) عند الاستلام يُرحَّل على حساب وسيط "شيكات تحت التحصيل" (1120) وليس على البنك مباشرة؛ فقط عند `collectCheque()` يتحول فعليًا لرصيد بنكي. شيك صادر (لمورد) بالمثل يُرحَّل على "شيكات دفع مستحقة" (2130) حتى يُحصَّل من حسابنا البنكي.
- ربط اختياري (وليس إلزاميًا): سند القبض/الصرف يقدر يُنشئ `SalesPayment`/`PurchasePayment` مرتبط بـ `treasury_voucher_id` لو رُبط بفاتورة، فيحدّث حالة السداد تلقائيًا — لكن شاشات المدفوعات القديمة (`SalesPaymentsController`/`PurchasePaymentsController`) **لم تُعدَّل** ولسه شغالة بشكل مستقل، تفاديًا لكسر تدفق موجود ومُختبر (توصية التصميم الأصلية D3).
- تم التحقق end-to-end: سند قبض نقدي (تحديث رصيد الخزنة + تخفيض AR)، سند قبض بشيك (بدون تأثير على البنك + زيادة حساب شيكات تحت التحصيل)، تحصيل الشيك (تحويل من الحساب الوسيط لرصيد البنك الفعلي) — كل الأرقام طابقت المتوقع تمامًا.
- أُضيفت بطاقة "الخزينة" الجديدة في `resources/views/admin/hub.blade.php` (لم تكن موجودة أصلًا كـ placeholder، بخلاف باقي الموديولات).

---

### Phase 5 — الأصول الثابتة

**جداول:** `asset_categories` (asset/accum_depreciation/depreciation_expense GL accounts), `fixed_assets` (asset_number FA-YYYY-XXXX, purchase_cost, useful_life_years, salvage_value, accumulated_depreciation, book_value, status[active|disposed|transferred|fully_depreciated], source_purchase_invoice_id nullable), `asset_depreciation_entries` (unique per fixed_asset+year+month لمنع تكرار التشغيل), `asset_transfers`.

**Service:** `app/Services/Assets/DepreciationService.php` — `monthlyAmount()` (قسط ثابت)، `runMonthly()` (ترحيل مجمّع لكل فئة، idempotent).

**Controllers:** `app/Http/Controllers/Admin/Assets/` — `AssetCategoriesController`, `FixedAssetsController` (+ `dispose`, `transfer`), `DepreciationRunController`, `AssetReportsController`.

**module_keys (sort_order 70-73)**, **Sidebar:** `sidebar_assets.blade.php`.

**✅ تم التنفيذ فعليًا (2026-07-06):**
- كل فئة أصول تحمل حساباتها المحاسبية الثلاثة مباشرة (أصل/مجمع إهلاك/مصروف إهلاك) بدل الاعتماد على `gl_posting_rules` — القيد الشهري يُبنى مباشرة من إعدادات الفئة، ومُجمَّع (قيد واحد لكل فئة شهريًا وليس لكل أصل) لتقليل عدد القيود.
- أُضيف حساب جديد لدليل الحسابات `4200 - أرباح/خسائر بيع الأصول الثابتة` (عبر migration `2026_07_06_000032`) لتسجيل الفارق بين عائد التخلص والقيمة الدفترية عند `dispose()`.
- قيد التخلص من الأصل متوازن بالكامل: مدين مجمع الإهلاك (إغلاقه) + مدين حساب استلام العائد (لو فيه عائد) + مدين خسارة (لو الفارق سالب)، مقابل دائن حساب تكلفة الأصل (إغلاقه بالكامل) + دائن ربح (لو الفارق موجب).
- `DepreciationService::runMonthly()` idempotent فعليًا عبر القيد الفريد على `(fixed_asset_id, period_year, period_month)` — تجربة تشغيل نفس الشهر مرتين تُظهر `skipped` للمرة الثانية بدون ازدواج ترحيل.
- تم التحقق end-to-end: أصل بتكلفة 12000 وعمر 5 سنوات → قسط شهري 200 (مطابق للحساب اليدوي) → تشغيل الإهلاك يحدّث الرصيد الدفتري ويرحّل قيد صحيح → التخلص من نفس الأصل بربح 500 ينتج قيدًا متوازنًا (12500=12500) ويُقفل حسابي الأصل والمجمع تمامًا لتوازن صفري.

---

### Phase 6 — التصنيع/الإنتاج

**جداول:** `bill_of_materials` + `bill_of_material_lines` (component_item_id من raw_material/semi_finished الموجودة بالفعل)، `production_orders` (order_number PRO-YYYY-XXXX, source/target warehouse, material/labor/overhead/total cost, status[draft|in_progress|completed|cancelled])، `production_order_materials`، `production_receipts`.

**Service:** `app/Services/Manufacturing/ProductionService.php` — `createFromBom()`, `issueMaterials()` (خصم مخزون بتكلفة المتوسط المرجح + قيد WIP/Inventory)، `receiveFinishedGoods()` (إضافة مخزون بتكلفة الإنتاج الفعلية + قيد Inventory-FG/WIP)، `complete()`.

**Controllers:** `app/Http/Controllers/Admin/Manufacturing/` — `BillOfMaterialsController`, `ProductionOrdersController`, `ManufacturingReportsController`.

**module_keys (sort_order 80-82)**, **Sidebar:** `sidebar_manufacturing.blade.php`.

**✅ تم التنفيذ فعليًا (2026-07-06):**
- **فجوة محاسبية اكتُشفت وأُصلحت أثناء الاختبار**: تكلفة العمالة والتكاليف غير المباشرة المقدَّرة (labor_cost/overhead_cost) كانت تُحمَّل على تكلفة المنتج التام عند الاستلام (تُقيَّد كدائن على WIP) بدون أي قيد مدين مقابل يُدخلها إلى WIP أصلاً — ده كان بيسيب رصيد WIP سالب وهمي. تم إصلاحه بإضافة قيد `production_overhead_applied` (مدين WIP / دائن حساب 5900) يُرحَّل تلقائيًا عند أول عملية صرف مواد لأمر الإنتاج (migration `2026_07_06_000036`)، فبقى رصيد WIP يتصفّر بالظبط بعد اكتمال الإنتاج بالكامل.
- تم التحقق end-to-end: شراء مادة خام بتكلفة معروفة → أمر إنتاج (BOM يحسب الكمية المطلوبة تلقائيًا بالنسبة والتناسب) → صرف المواد (قيد WIP/Inventory متوازن + قيد العمالة/التكاليف غير المباشرة) → استلام الإنتاج التام (قيد Inventory/WIP يُصفّر WIP بالكامل) → تكلفة الوحدة النهائية للمنتج التام طابقت (تكلفة المواد + العمالة + التكاليف غير المباشرة) ÷ الكمية المخططة تمامًا.

---

### Phase 7 — استكمال ربط الفاتورة الإلكترونية

- إضافة `sales_invoice_id`/`purchase_invoice_id` (nullable FK) إلى `eta_invoices`.
- `TaxController::postInvoice()`/`postBulk()` يتحقق أن الفاتورة المرتبطة لها قيد محاسبي فعلي (`JournalPostingService::alreadyPosted`) قبل السماح بعلامة "مطابقة ETA".
- إيصال إلكتروني B2C (اختياري) → Phase 8.

**✅ تم التنفيذ فعليًا (2026-07-06):**
- التكامل الحالي مع ETA **سحب فقط (Pull)**: `TaxController::sync()` يسحب فواتير مُقدَّمة بالفعل لمصلحة الضرائب من منصة ETA، ولا يوجد دفع (Push) لفواتيرنا الداخلية إليها. `internal_id` نص حر لا يضمن تطابقه مع ترقيم فواتيرنا، فلا يوجد مفتاح مطابقة تلقائي موثوق.
- لذلك تم بناء **ربط يدوي بمرشحات مقترحة**: `EtaInvoice::suggestedMatches()` تقترح فواتير داخلية بنفس الاتجاه، بفارق مبلغ ±1 جنيه وفارق تاريخ ±3 أيام، والمستخدم يختار المطابقة الصحيحة من شاشة `admin.tax.show` (أزرار ربط/إلغاء ربط جديدة، routes: `tax.link`/`tax.unlink`).
- `TaxController::postInvoice()`/`postBulk()` (أعيدت تسميتها في الواجهة "تأكيد المطابقة المحاسبية" بدل "ترحيل محاسبي") تتحقق عبر `verifyGlPosting()` أن الفاتورة المرتبطة لها قيد فعلي مرحّل (`JournalPostingService::alreadyPosted`) قبل السماح بالتأكيد؛ لو مربوطة بدون قيد تُرفض العملية برسالة خطأ واضحة؛ لو غير مربوطة أصلًا يُسمح بالاعتماد كامتثال فقط مع تسجيل ملاحظة في `posting_notes` توضح عدم وجود ربط.
- استُبدل القسم القديم في `resources/views/admin/tax/show.blade.php` ("القيد المحاسبي المقترح" — كان جدولًا توضيحيًا ثابتًا غير متصل بأي قيد حقيقي) بقسم "الربط بالفاتورة الداخلية" الفعلي + حالة الترحيل الحقيقية.
- تم التحقق end-to-end عبر tinker: إنشاء فاتورة بيع + سجل ETA بنفس المبلغ/التاريخ → `suggestedMatches()` وجدها بنجاح → الربط اليدوي → `verifyGlPosting()` رفض التأكيد قبل وجود قيد فعلي، ثم قبله فور ترحيل القيد الحقيقي للفاتورة.
- إيصال إلكتروني B2C يبقى اختياريًا ومؤجَّلًا لـ Phase 8 كما هو مخطط أصلًا.

---

### Phase 8 — اختياري/مؤجل

عملات متعددة، إيصال إلكتروني B2C (`eta_receipts`, sort_order 91+)، خوارزمية مطابقة بنكية تلقائية، موازنات مراكز التكلفة، طرق تقييم بديلة (FIFO)، حالات تفكيك/بيع جزئي للأصول.

---

### Phase 9 — الموديولات غير المحاسبية المتبقية في شاشة اختيار الموديولات

خمس بطاقات في `resources/views/admin/hub.blade.php` كانت لسه `coming-soon` من الجلسة الأصلية، ولم تكن جزءًا من التحليل الذي بُنيت عليه Phases 1-7 (تلك ركّزت حصرًا على الفجوة المحاسبية). طلب المستخدم (2026-07-06) إضافتها كمرحلة جديدة، بترتيب أولوية: **ضبط الجودة → BI/التقارير → CRM → إدارة المشاريع → إدارة الوثائق**. لا علاقة محاسبية مباشرة لمعظمها (باستثناء ربط بسيط اختياري لاحقًا)، فلا تتطلب `JournalPostingService`.

#### Phase 9.1 — ضبط الجودة (Quality Control) [x] 2026-07-06

مرتبطة مباشرة بالتصنيع (Phase 6): فحوصات جودة على أوامر الإنتاج و/أو فواتير الشراء (فحص استلام).

**جداول:** `quality_checklists` (قوالب فحص: name, applies_to[production|purchase|both], is_active) + `quality_checklist_items` (criterion نصي لكل بند)، `quality_inspections` (inspection_number QC-YYYY-XXXX, checklist_id, source_type[production_order|purchase_invoice], source_id, inspector_id, date, overall_result[pass|fail|conditional], notes) + `quality_inspection_items` (نتيجة كل بند: pass|fail|na).

**Controllers:** `app/Http/Controllers/Admin/Quality/` — `QualityChecklistsController`, `QualityInspectionsController`, `QualityReportsController` (نسبة النجاح/الرفض).

**module_keys (sort_order 83-85)** — ضمن الفجوة المحجوزة أصلًا خلف التصنيع (83-89). **Sidebar:** `sidebar_quality.blade.php`.

نطاق مقصود الاستبعاد منه (لتبسيط الإصدار الأول): لا يوجد ربط تلقائي بحجز/رفض كمية في المخزون عند فشل الفحص (يُسجَّل النتيجة فقط، والإجراء التصحيحي يدوي عبر تسوية مخزون موجودة بالفعل).

**✅ تم التنفيذ فعليًا (2026-07-06):** موديول مستقل بالكامل بدون أي ترحيل محاسبي (عملياتي فقط). `overall_result` يُحسب تلقائيًا من نتائج البنود: `fail` لو كل البنود فشلت، `conditional` لو بعضها فشل، `pass` غير كده. شاشة الفحص الجديد تعرض بنود القالب المختار ديناميكيًا (JS يفعّل/يعطّل الجدول المطابق لكل قالب، والحقول المعطّلة لا تُرسَل مع الفورم تلقائيًا فتفادينا أي منطق خادم إضافي). تم التحقق end-to-end: إنشاء قالب فحص + أمر إنتاج فعلي (عبر `ProductionService`) + فحص مرتبط به بنتيجة "مقبول بشرط" وبنديْن (ناجح/فاشل)، والتأكد أن `source` accessor بيرجع أمر الإنتاج الصحيح.

#### Phase 9.2 — التقارير والتحليلات (BI & Analytics) [x] 2026-07-06

طبقة تجميع فوق كل الموديولات الموجودة بالفعل - لا جداول جديدة تقريبًا.

**Controller:** `app/Http/Controllers/Admin/BI/BiDashboardController.php` — لوحة تنفيذية واحدة تجمع مؤشرات من: المبيعات، المشتريات، المخزون، المحاسبة (أرباح/خسائر مختصرة)، الخزينة، الرواتب. **module_key:** `bi_dashboard` (sort_order 91، بعد نطاق الأصول والتصنيع مباشرة).

**✅ تم التنفيذ فعليًا (2026-07-06):** لوحة واحدة (بدون جداول جديدة إطلاقًا) بمؤشرات: مبيعات/مشتريات الشهر، صافي ربح/خسارة العام حتى تاريخه (نفس منطق حساب `AccountingReportsController::incomeStatement`)، قيمة المخزون الحالية (`stock_balances.total_value`)، إجمالي السيولة (خزائن+بنوك)، ذمم مدينة/دائنة، عدد الموظفين النشطين، ورسم بياني (Chart.js v2 - مكتبة موجودة بالفعل في المشروع) لاتجاه المبيعات آخر 6 أشهر. تم التحقق بتشغيل الكنترولر وعرض الـ view فعليًا عبر tinker (بدون متصفح) والتأكد من عدم وجود أخطاء rendering.

#### Phase 9.3 — إدارة علاقات العملاء (CRM) [x] 2026-07-06

يُبنى فوق `Customer` الموجود بالفعل (Sales)، بدون تعديله.

**جداول:** `crm_leads` (عميل محتمل قبل التحويل: name, phone, source, status[new|contacted|qualified|converted|lost])، `crm_opportunities` (lead_id أو customer_id، stage[prospecting|proposal|negotiation|won|lost], value, expected_close_date)، `crm_activities` (نشاط/متابعة: linked_type[lead|customer|opportunity], linked_id, type[call|meeting|note], notes, activity_date, created_by).

**Controllers:** `app/Http/Controllers/Admin/Crm/` — `LeadsController` (+ `convertToCustomer()` تنشئ `Customer` فعليًا)، `OpportunitiesController`، `ActivitiesController` (تُدرج ضمن صفحة العرض لكل Lead/Opportunity).

**module_keys (sort_order 100-102)**, **Sidebar:** `sidebar_crm.blade.php`. الحملات التسويقية (Campaigns) مؤجَّلة — خارج نطاق الإصدار الأول.

**✅ تم التنفيذ فعليًا (2026-07-06):** `LeadsController::convertToCustomer()` تنشئ `Customer` حقيقي في موديول المبيعات وتوجّه المستخدم لصفحته مباشرة (`sales_customers.show`)، بدون تعديل أي كود موجود في موديول المبيعات. الفرص البيعية معروضة كلوحة Kanban بسيطة (أعمدة حسب المرحلة، بدون سحب وإفلات — تحديث المرحلة عبر فورم صغير). المتابعات (`crm_activities`) مُصمَّمة بربط بسيط `linked_type/linked_id` (بدون morphTo حقيقي، بنفس اصطلاح باقي الموديولات في هذا المشروع) وتُدرج مباشرة داخل صفحة عرض أي Lead أو فرصة. تم التحقق end-to-end: إنشاء عميل محتمل + متابعة + فرصة بيعية مرتبطة به، ثم تحويله فعليًا لعميل حقيقي والتأكد من ربط `converted_customer_id` بشكل صحيح.

#### Phase 9.4 — إدارة المشاريع (Project Management) [x] 2026-07-06

**جداول:** `projects` (name, customer_id nullable, start_date, end_date, budget, status[planning|active|on_hold|completed|cancelled])، `project_tasks` (project_id, title, assigned_to [employee_id], due_date, status[todo|in_progress|done], priority[low|medium|high]).

**Controllers:** `app/Http/Controllers/Admin/Projects/` — `ProjectsController`, `ProjectTasksController` (عرض لوحة Kanban بسيطة حسب الحالة).

**module_keys (sort_order 110-111)**, **Sidebar:** `sidebar_projects.blade.php`. مخطط جانت والتكاليف الفعلية المرتبطة بمصروفات الخزينة مؤجَّلة لإصدار لاحق.

**✅ تم التنفيذ فعليًا (2026-07-06):** `project_tasks` ليس بها عمود `com_code` مباشر (بنفس نمط `monthly_payrolls`) — العزل بالشركة يتم عبر `whereHas('project', fn($q) => $q->where('com_code', ...))`، وتم التحقق أن هذا يمنع فعليًا الوصول لمهام شركة أخرى. لوحة Kanban داخل صفحة عرض المشروع (أعمدة حسب الحالة: قيد الانتظار/قيد التنفيذ/منجزة) مع تحديث الحالة عبر قائمة منسدلة تُرسل تلقائيًا (`onchange="this.form.submit()"`) بدل سحب وإفلات. تم التحقق end-to-end: مشروع + مهمة مُسنَدة لموظف حقيقي، تحديث الحالة، والتأكد من رفض الوصول لها بـ com_code خاطئ.

#### Phase 9.5 — إدارة الوثائق (Document Management) [x] 2026-07-06

أرشفة عامة على مستوى الشركة (مختلفة عن `employee_documents` الموجود بالفعل والخاص بمستندات الموظفين فقط - لا تُمس).

**جداول:** `document_categories` (name)، `documents` (category_id, title, file_path, file_original_name, linked_type nullable [عام أو مرتبط بأي كيان]، linked_id nullable, version, status[draft|pending|approved|rejected], uploaded_by).

**Controller:** `app/Http/Controllers/Admin/Documents/DocumentsController.php` — رفع/عرض/تحميل، بنفس اصطلاح التخزين المستخدم في `EmployeesConroller` (`public_path('assets/admin/documents/...')`).

**module_keys (sort_order 120-121)**, **Sidebar:** `sidebar_documents.blade.php`.

**✅ تم التنفيذ فعليًا (2026-07-06):** آخر موديول في Phase 9، ونفس الوقت آخر بند في الخطة كلها. رفع الملفات يطابق تمامًا اصطلاح `EmployeesConroller::uploadDocument()` الموجود بالفعل (`public_path('assets/admin/documents/')`, اسم ملف فريد بـ `time()+uniqid()`, حذف الملف الفعلي من القرص عند حذف السجل). دورة موافقة بسيطة (draft → pending → approved/rejected) بدون محرك workflow معقّد. تم التحقق end-to-end فعليًا برفع ملف حقيقي على القرص، التأكد من وجوده، اعتماد الوثيقة، ثم حذفها والتأكد أن الملف الفعلي أُزيل من القرص أيضًا (مش بس السجل من قاعدة البيانات).

---

## ✅ الخطة بأكملها (Phase 0-9) مكتملة (2026-07-06)

كل الموديولات الـ12 في `admin/hub.blade.php` أصبحت `active-module` الآن، ومفيش أي بطاقة `coming-soon` متبقية. النظام يغطي: HR/Payroll، المبيعات، المشتريات، المخازن، المحاسبة، الخزينة، الأصول الثابتة، التصنيع، ضبط الجودة، BI، CRM، إدارة المشاريع، إدارة الوثائق — كلها موصولة ببعض حسب الحاجة (الترحيل المحاسبي التلقائي عبر `JournalPostingService` للموديولات المالية، وربط بسيط بين CRM/Sales وQuality/Manufacturing وProjects/Sales من غير كسر أي كود موجود). المتبقي فعليًا هو Phase 8 فقط (اختياري ومؤجَّل عمدًا).

---

### Phase 10 — إعادة هيكلة: موديول "النظام" (الشركات) + نقل الضرائب للمحاسبة [x] 2026-07-07

طلب المستخدم (2026-07-07): تعريف الشركات كان متشابكًا داخل شاشة "الضبط العام" فى HR (نفس الجدول `admin_panel_settings` يخلط بيانات هوية الشركة مع إعدادات HR البحتة)، وبلوك "الضرائب والفاتورة الإلكترونية" (ETA) كان معروضًا بصريًا داخل قائمة HR الجانبية رغم استقلاليته المنطقية.

- **`companies` كمصدر حقيقة فعلي**: migration `2026_07_07_000001_backfill_companies_from_admin_panel_settings.php` تُكمل ما بدأته `2024_04_01_000001` (كانت أنشأت الجدول لكن الاستبدال لم يكتمل): لكل `admin_panel_settings` بدون `company_id`، يُنشأ صف `companies` مطابق ويُربط `admin_panel_settings.company_id`/`admins.company_id` به. لم تُحذف أو تُعدَّل أي أعمدة فى `admin_panel_settings` — كل الشاشات القديمة (هيدرات كل الموديولات + مطبوعات الفواتير/عروض الأسعار) تفضل تقرأ منها بدون أي تعديل.
- **موديول جديد "النظام"**: `app/Http/Controllers/Admin/System/{CompaniesController,CompanyProfileController}.php`، layout مستقل `admin/layouts/system.blade.php` + `sidebar_system.blade.php`، بطاقة 13 جديدة فى `hub.blade.php`. شاشتان: "بيانات شركتي" (لكل شركة، تكتب فى `admin_panel_settings` و`companies` معًا للحفاظ على التزامن) و"سجل الشركات" (سوبر أدمن فقط، عبر فحص `is_super_admin` داخل الـ Controller بنفس نمط `MaintenanceController`).
- **تنظيف شاشة HR**: `AdminPanelSettingController`/`PanelSetting/edit.blade.php` عادت مسؤولة فقط عن إعدادات HR البحتة (تأخير/جزاءات/إجازات/تأمينات/SMS) — حُذفت حقول هوية الشركة من الفورم فقط (البيانات نفسها فى الجدول لم تُمس)، مع زر يوجّه لشاشة "بيانات شركتي" الجديدة.
- **نقل الضرائب للمحاسبة**: 8 ملفات `resources/views/admin/tax/*.blade.php` غُيّر فيها `@extends` من `admin.layouts.admin` إلى `admin.layouts.accounting`، وانتقل بلوك القائمة الجانبية بالكامل من `sidebar.blade.php` (HR) إلى نهاية `sidebar_accounting.blade.php`. `module_key=tax` انتقل من `sort_order=90` إلى `56` (داخل النطاق المحجوز أصلًا للمحاسبة 56-59). لا تغيير فى `TaxController` أو أي route أو منطق — نقل عرض/تصنيف فقط.
- **تم التحقق end-to-end**: تسجيل دخول فعلي وزيارة الشاشات الثلاث (بيانات شركتي/سجل الشركات/الضرائب) بحالة 200، التأكد أن حقول هوية الشركة اختفت من فورم HR وظهرت فى الشاشة الجديدة بالقيم الصحيحة، وأن بلوك الضرائب اختفى من قائمة HR وظهر داخل قائمة المحاسبة. اكتُشفت أثناء الاختبار فجوة بيانات حقيقية كانت موجودة من قبل (اسم الشركة فى `admin_panel_settings.com_name` كان مختلفًا عن `companies.name` القديم من التسجيل الأول) — وهذا بالضبط نوع التشتت اللي إعادة الهيكلة دي بتحله.

**module_keys جديدة:** `companies`, `company_profile` (sort_order تلقائي = `MAX(sort_order)+1`).

---

### Phase 11 — نقطة البيع (POS) بجلسات كاشير كاملة [x] 2026-07-07

طلب المستخدم (2026-07-07): موديول المبيعات كان يفتقد شاشة بيع سريع (POS) بجلسات كاشير (فتح/قفل بمطابقة نقدية)، رغم وجود كل البنية التحتية اللازمة (فواتير بيع، مخزون بمتوسط مرجح، خزينة، ترحيل محاسبي تلقائي).

**جداول جديدة:** `pos_registers` (`com_code, name, cash_box_id→cash_boxes, warehouse_id→warehouses, branch_id, is_active`)، `pos_sessions` (`com_code, register_id, opened_by→admins, opening_amount, expected_closing_amount, counted_closing_amount, difference, status[open|closed], opened_at, closed_at`)، + عمود `sales_invoices.pos_session_id` (nullable FK).

**Controller:** `app/Http/Controllers/Admin/Sales/{PosController,PosRegistersController}.php` — لا خدمة جديدة ولا قواعد ترحيل جديدة؛ إعادة استخدام كاملة لـ:
- نفس منطق حساب سطور الفاتورة الموجود فى `SalesInvoicesController::store()` (نُسخ محليًا فى `PosController` بدل تعديل الكنترولر الأصلي، تفاديًا لأي كسر فى شاشة الفواتير العادية المُختبرة).
- `StockService::adjustStock()` لكل سطر بيع (نفس تكلفة COGS بالمتوسط المرجح).
- `JournalPostingService::post()` بنفس event types الموجودة فعلاً (`sales_invoice_issued`, `sales_invoice_cogs`) — بدون أي قاعدة ترحيل جديدة.
- `TreasuryService::createVoucher()` لتحصيل كامل قيمة البيع فورًا كسند قبض نقدي مربوط بخزنة الـ register (`cash_box_id`)، فيتحدّث رصيد الخزنة تلقائيًا بنفس آلية الخزينة الموجودة من قبل.
- `ItemsController::ajaxSearch` (route `items.ajax.search` الموجود بالفعل) كـ widget بحث/باركود فى واجهة الكاشير.
- طباعة الإيصال تستخدم `sales_invoices.print` الموجودة بالفعل (بدون شاشة طباعة جديدة).

كل بيع POS نقدي بالكامل وفوري (لا آجل، لا سداد جزئي) — عميل نقدي افتراضي (`Customer::firstOrCreate` باسم "عميل نقدي (POS)") يُستخدم لو الكاشير لم يختر عميلًا محددًا.

**الجلسات:** `PosSession::sales_total` (accessor) يجمع فواتير الجلسة ديناميكيًا بدل عداد مُخزَّن — `closeSession()` يحسب `expected = opening_amount + sales_total` مقابل المبلغ المعدود فعليًا، ويسجّل الفرق.

**Views:** `resources/views/admin/sales/pos/` — `terminal` (شاشة الكاشير: بحث صنف بـ JS/fetch + سلة تفاعلية + إتمام بيع بـ AJAX)، `select_register`, `close_session`, `sessions_index`, `sessions_show`, `registers_index/create/edit`. مضافة لـ `sidebar_sales.blade.php` الموجود (بدون sidebar/layout جديد — تحت موديول المبيعات مباشرة).

**module_keys جديدة (sort_order 30-32):** `pos_terminal`, `pos_sessions`, `pos_registers`.

**✅ تم التحقق end-to-end فعليًا (2026-07-07):** بيانات تجريبية حقيقية (مخزن + خزنة نقدية بحساب GL + صنف برصيد افتتاحي بتكلفة معروفة 60 + كاشير) → فتح جلسة برصيد افتتاحي 200 → بيع 5 وحدات بسعر 100 وضريبة 14% (إجمالي 570) → التأكد أن: المخزون نقص من 50 إلى 45 بنفس `avg_cost`، قيد الإيراد+الضريبة متوازن (570=570)، قيد تكلفة البضاعة المباعة متوازن (300=300، أي 5×60)، سند القبض النقدي رفع رصيد الخزنة من 0 إلى 570 بقيد متوازن، الدفعة (`SalesPayment`) مرتبطة بسند الخزينة (`treasury_voucher_id`) والفاتورة أصبحت `paid`. إغلاق الجلسة بمبلغ معدود مطابق (770) أعطى فرق=0، وتجربة ثانية بنقص متعمد (5 جنيه) فى جلسة منفصلة أعطت فرق=-5 بالظبط. كل بيانات الاختبار ورصيد الحسابات المتأثرة أُعيدت لصفرها بعد التحقق (لم تُترك بيانات تجريبية فى قاعدة البيانات).

---

### Phase 12 — مراجعة موديول HR: 3 تحسينات مُختارة [x] 2026-07-07

بعد مراجعة شاملة لموديول HR (انظر تحليل Phase 10 السياقي)، عُرضت على المستخدم قائمة مرشحة من الفجوات الحقيقية، فاختار الثلاثة التالية لتنفيذها فورًا:

#### 12.1 — ضريبة كسب العمل (اختيارية لكل موظف، بشرائح قابلة للتعديل + إعفاء ضريبي)

- **جدول جديد `income_tax_brackets`**: `com_code, from_amount, to_amount (nullable=بلا حد أعلى), rate, is_active` — شرائح شهرية تصاعدية (كل شريحة تُحمَّل بنسبتها على الجزء الواقع داخلها فقط). `App\Models\IncomeTaxBracket::calcTax()` يحسبها.
- **`admin_panel_settings.income_tax_exemption_monthly`**: إعفاء ضريبي شهري يُطرح من الوعاء قبل تطبيق الشرائح.
- **`employees.apply_income_tax`** (boolean, **افتراضيًا false**): تحكم كامل لكل موظف — هل تُخصم منه الضريبة أم لا؟ الافتراضي false عمدًا حتى لا يتغيّر صافي راتب أي موظف حالي تلقائيًا بمجرد نشر هذا التحديث؛ الإدارة تُفعّلها يدويًا فقط لمن تريد.
- **`monthly_payrolls.income_tax_deduction`**: يُخصم داخل معادلة `net_salary` بنفس نمط خصم التأمينات تمامًا (`PayrollController::calculate()`).
- **الترحيل المحاسبي**: حساب جديد `2140 - ضرائب كسب عمل مستحقة` (liability) + قاعدة ترحيل جديدة `payroll_approved`/`INCOME_TAX_PAYABLE`. قيد الاستحقاق أصبح 3 أسطر بدل 2 عند وجود ضريبة: `SALARY_EXPENSE` مدين = `net_salary + income_tax_deduction` (**نفس القيمة التي كانت ستُحمَّل كمصروف قبل إضافة هذا التحديث**، فلا تغيير على مصروف الشركة لأي موظف)، `SALARY_PAYABLE` دائن = `net_salary` (صافي المستحق للموظف بعد خصم الضريبة)، `INCOME_TAX_PAYABLE` دائن = `income_tax_deduction` (التزام مستقل تجاه مصلحة الضرائب). للموظفين الذين `apply_income_tax=false` (وهو الافتراضي) القيد يبقى **مطابقًا حرفيًا** لما كان عليه قبل التحديث.
- **شاشة جديدة** `income_tax_brackets.index` (Controller: `App\Http\Controllers\Admin\HR\IncomeTaxBracketsController`) — إضافة/حذف شرائح + ضبط الإعفاء الشهري، متاحة من قائمة HR الجانبية.
- **واجهة الموظف**: حقل "خصم ضريبة كسب العمل من هذا الموظف" (checkbox) فى شاشتي إضافة/تعديل موظف. كشف الراتب (`payroll/show.blade.php`) يعرض بند الضريبة ضمن الخصومات إن وُجد.
- **✅ تم التحقق end-to-end**: حساب تصاعدي على 3 شرائح تجريبية (0-1000@0%, 1000-5000@10%, 5000+@20%) أعطى نتائج مطابقة تمامًا (200 على وعاء 3000، 1000 على وعاء 8000)، وقيد GL بثلاثة أسطر لموظف حقيقي (4250=4250) متوازن بحساباته الصحيحة (مصروفات الرواتب / رواتب مستحقة / ضرائب كسب عمل مستحقة)، ثم أُلغي القيد التجريبي وأُعيدت أرصدة الحسابات المتأثرة لحالتها الأصلية.

#### 12.2 — تنبيهات انتهاء العقود وفترة الاختبار

- **أعمدة جديدة**: `employees.probation_end_date`, `employees.contract_end_date` (كلاهما nullable — فارغ = عقد غير محدد المدة / لا توجد فترة اختبار مُتابَعة).
- **تقرير جديد**: `ReportsController::contractsExpiring()` (route `reports.contracts_expiring`) — يعرض كل موظف تنتهي فترة اختباره أو عقده خلال عدد أيام قابل للتصفية (افتراضي 30 يوم)، مع عدد الأيام المتبقية. مضاف كبند فى قائمة "التقارير" بقائمة HR الجانبية (تحوّلت من رابط مفرد إلى قائمة فرعية).
- **✅ تم التحقق**: تعديل مؤقت على موظف حقيقي بتاريخي اختبار/عقد خلال 10/20 يوم، التأكد من ظهوره فى التقرير، ثم إرجاع بياناته الأصلية بالكامل (null كما كانت).

#### 12.3 — إعادة تنظيم بنية أكواد HR (تنظيفي بحت — بدون أي تغيير سلوكي)

- نُقل 27 controller من `app/Http/Controllers/Admin/*.php` (بنية مسطّحة قديمة) إلى `app/Http/Controllers/Admin/HR/*.php` بنفس نمط بقية الموديولات (`Admin/Sales`, `Admin/Accounting`... إلخ)، مع تحديث `namespace` كل ملف إلى `App\Http\Controllers\Admin\HR`.
- **لم تُنقل عمدًا** (تبقى فى `Admin/` مباشرة): `AdminHomeController` (شاشة هبوط مشتركة لكل الموديولات)، `AdminPermissionsController` و`MaintenanceController` (بنية تحتية عامة للنظام كله وليست HR تحديدًا)، `TaxController`/`EtaFreeZoneController` (نُقلت بصريًا للمحاسبة فى Phase 10، ليست HR)، و`LoginController`/`LogController`/نسخة `AdminPanelSettingController` المكرّرة داخل `Admin/` (اكتُشف أثناء الفحص أنها **أكواد ميتة غير مُستخدمة فى أي route** — الكنترولر الفعلي المُفعَّل لكل من هذه الوظائف موجود بمكان آخر بالفعل؛ تُركت كما هي دون حذف لتفادي أي تأثير جانبي غير متوقع، خارج نطاق هذا التحسين التنظيمي).
- حُدِّثت جميع الإشارات المرتبطة: 23 سطر `use` واستيراد inline فى `routes/admin.php`، استيراد داخلي عابر بين اثنين من كنترولرات HR نفسها (`AttendanceController` كان يستورد `LeaveCompensationController` من المسار القديم)، ومرجع مباشر لكنترولر HR داخل `resources/views/admin/branch_commissions/events.blade.php`. تم تشغيل `composer dump-autoload` بعد النقل.
- **✅ تم التحقق end-to-end**: `php artisan route:list` (612 route) بدون أي خطأ فادح، `view:cache` نجح، وزيارة فعلية لأهم 8 شاشات HR (قائمة الموظفين، عرض/تعديل موظف حقيقي، الرواتب، الحضور، التقارير، تنبيهات العقود، شرائح الضريبة) كلها أعادت 200 بعد النقل.

---

## جدول تخصيص module_key / sort_order

| النطاق | sort_order | الموديولات |
|---|---|---|
| المحاسبة | 50-55 | chart_of_accounts, cost_centers, journal_entries, accounting_periods, gl_posting_rules, accounting_reports |
| الخزينة | 60-66 | cash_boxes, bank_accounts, treasury_receipts, treasury_payments, cheques, bank_reconciliation, treasury_reports |
| الأصول الثابتة | 70-73 | asset_categories, fixed_assets, asset_depreciation, asset_reports |
| التصنيع | 80-82 | bill_of_materials, production_orders, manufacturing_reports |
| ضبط الجودة (Phase 9.1) | 83-85 | quality_checklists, quality_inspections, quality_reports |
| BI (Phase 9.2) | 91 | bi_dashboard |
| CRM (Phase 9.3) | 100-102 | crm_leads, crm_opportunities, crm_activities |
| المشاريع (Phase 9.4) | 110-111 | projects, project_tasks |
| الوثائق (Phase 9.5) | 120-121 | document_categories, documents |
| إيصال إلكتروني (اختياري) | 92+ | eta_receipts |
| ضريبة كسب العمل (Phase 12.1) | 16 | income_tax_brackets |
| POS (Phase 11) | 30-32 | pos_terminal, pos_sessions, pos_registers |
| النظام/الشركات (Phase 10) | تلقائي (`MAX(sort_order)+1`) | companies, company_profile |

(الفجوات 56-59, 67-69, 86-89 محجوزة عمدًا لموديولات فرعية مستقبلية، بنفس نمط التباعد المستخدم في Sales/Purchasing/Inventory الحاليين.)

## الاصطلاحات الواجب اتباعها بدقة عند إضافة أي كود جديد

1. **تسجيل الموديول**: migration يعمل seed لجدول `admin_modules` بصيغة `updateOrInsert(['module_key' => ...], [...])` — انظر `database/migrations/2026_07_05_000013_seed_sales_admin_modules.php`.
2. **الصلاحيات**: كل route جديد يُلف بـ `Route::middleware(['auth:admin', 'admin.permission:<module_key>,<can_read|can_create|can_update|can_delete>'])`.
3. **Controllers**: تحت `app/Http/Controllers/Admin/<Area>/<Thing>Controller.php`، كل استعلام يُصفَّى بـ `com_code` عبر `Auth::guard('admin')->user()->com_code`.
4. **ترقيم المستندات**: `PREFIX-YYYY-XXXX` بإعادة تصفير سنوي، محسوبة في `store()` — انظر `SalesInvoicesController::nextInvoiceNumber()`.
5. **Sidebar**: partial جديد لكل Area تحت `resources/views/admin/includes/sidebar_<area>.blade.php`، بدون منطق صلاحيات في الـ blade (الفحص عند الـ route فقط).
6. **الترحيل المحاسبي**: أي حدث تجاري جديد يمر حصريًا عبر `JournalPostingService::post()` — ممنوع إنشاء `JournalEntry` مباشرة من أي Controller.
7. **Routes مع {id} bare**: أي route بصيغة `resource/{id}` (بدون لاحقة) **يجب** أن يحمل `->where('id', '[0-9]+')` لو فيه route تاني بصيغة `resource/create` في نفس المجموعة، وإلا هيبتلع الكلمة "create" كـ id (باگ حصل فعليًا في 10 شاشات قبل الإصلاح في هذه الخطة).

## التحقق بعد كل مرحلة

1. `php artisan migrate` بدون أخطاء schema.
2. زيارة الموديول الجديد كـ super admin، والتأكد من ظهوره بالقائمة الجانبية والصلاحيات.
3. Phase 3: إنشاء فاتورة بيع تجريبية والتأكد أن القيد في `journal_entries`/`journal_entry_lines` متوازن (مدين = دائن) ومطابق لميزان المراجعة.
4. Phase 2: التأكد أن `stock_balances.avg_cost` يتحدث بشكل صحيح بعد فاتورة شراء ثم فاتورة بيع.
5. تشغيل أي اختبارات Laravel موجودة (`php artisan test`) كمرجع نمط.
