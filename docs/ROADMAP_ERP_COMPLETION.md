# خارطة طريق استكمال ERP متكامل (NEXA)

> هذا المستند مرجعي دائم داخل المستودع. أي جلسة عمل جديدة (من أي جهاز) يجب أن تبدأ بقراءته لمعرفة ما تم إنجازه وما هو التالي.
> عند إنجاز أي بند ضع علامة `[x]` وأضف تاريخ الإنجاز، والتزم بنفس الاصطلاحات الموصوفة هنا عند إضافة أي كود جديد.

> **✅ حالة الخطة (2026-07-06): Phases 0-7 مكتملة بالكامل ومُختبرة end-to-end.**
> النظام الآن ERP متكامل فعليًا: محاسبة عامة + ترحيل تلقائي من كل الموديولات + خزينة وشيكات + أصول ثابتة وإهلاك + تصنيع بتكلفة فعلية + مطابقة ETA محاسبية.
> المتبقي هو **Phase 8 فقط** (اختياري/مؤجل عمدًا): عملات متعددة، إيصال إلكتروني B2C، مطابقة بنكية تلقائية، موازنات، FIFO. لا شيء من هذا ضروري لاعتبار النظام مكتملًا لشركة/مصنع مصري نموذجي.

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
Phase 4: الخزينة والبنوك (خزائن، حسابات بنكية، سندات قبض/صرف، شيكات)         [x] 2026-07-06
Phase 5: الأصول الثابتة (سجل أصول، إهلاك)                                    [x] 2026-07-06
Phase 6: التصنيع/الإنتاج (BOM، أوامر إنتاج)                                  [x] 2026-07-06
Phase 7: استكمال ربط الفاتورة الإلكترونية بالمحاسبة                          [x] 2026-07-06
Phase 8: اختياري/مؤجل (عملات متعددة، إيصال إلكتروني B2C، مطابقة بنكية
         تلقائية، موازنات مراكز التكلفة، FIFO)                               [ ]
```

Phase 1 شرط أساسي لكل ما بعده. Phase 2 يجب أن يسبق Phase 3 حتى يكون قيد COGS صحيحًا. المراحل 4/5/6 مستقلة عن بعضها ويمكن إعادة ترتيب أولويتها حسب احتياج العمل الفعلي (مثال: شركة خدمية قد لا تحتاج Phase 6 إطلاقًا).

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

**module_keys (sort_order 50-55):** `chart_of_accounts`, `cost_centers`, `journal_entries`, `accounting_periods`, `gl_posting_rules`, `accounting_reports` — سيدر migration على نمط `2026_07_05_000013_seed_sales_admin_modules.php`.

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
بيعكسوا تكلفة COGS الحقيقية (المتوسط المرجح) مش السعر المُمرَّر من المستدعي. يعني كل الأماكن
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

## جدول تخصيص module_key / sort_order

| النطاق | sort_order | الموديولات |
|---|---|---|
| المحاسبة | 50-55 | chart_of_accounts, cost_centers, journal_entries, accounting_periods, gl_posting_rules, accounting_reports |
| الخزينة | 60-66 | cash_boxes, bank_accounts, treasury_receipts, treasury_payments, cheques, bank_reconciliation, treasury_reports |
| الأصول الثابتة | 70-73 | asset_categories, fixed_assets, asset_depreciation, asset_reports |
| التصنيع | 80-82 | bill_of_materials, production_orders, manufacturing_reports |
| إيصال إلكتروني (اختياري) | 91+ | eta_receipts |

(الفجوات 56-59, 67-69, 74-79, 83-89 محجوزة عمدًا لموديولات فرعية مستقبلية، بنفس نمط التباعد المستخدم في Sales/Purchasing/Inventory الحاليين.)

## الاصطلاحات الواجب اتباعها بدقة عند إضافة أي كود جديد

1. **تسجيل الموديول**: migration يعمل seed لجدول `admin_modules` بصيغة `updateOrInsert(['module_key' => ...], [...])` — انظر `database/migrations/2026_07_05_000013_seed_sales_admin_modules.php`.
2. **الصلاحيات**: كل route جديد يُلف بـ `Route::middleware(['auth:admin', 'admin.permission:<module_key>,<can_read|can_create|can_update|can_delete>'])`.
3. **Controllers**: تحت `app/Http/Controllers/Admin/<Area>/<Thing>Controller.php`، كل استعلام يُصفَّى بـ `com_code` عبر `Auth::guard('admin')->user()->com_code`.
4. **ترقيم المستندات**: `PREFIX-YYYY-XXXX` بإعادة تصفير سنوي، محسوبة في `store()` — انظر `SalesInvoicesController::nextInvoiceNumber()`.
5. **Sidebar**: partial جديد لكل Area تحت `resources/views/admin/includes/sidebar_<area>.blade.php`، بدون منطق صلاحيات في الـ blade (الفحص عند الـ route فقط).
6. **الترحيل المحاسبي**: أي حدث تجاري جديد يمر حصريًا عبر `JournalPostingService::post()` — ممنوع إنشاء `JournalEntry` مباشرة من أي Controller.

## التحقق بعد كل مرحلة

1. `php artisan migrate` بدون أخطاء schema.
2. زيارة الموديول الجديد كـ super admin، والتأكد من ظهوره بالقائمة الجانبية والصلاحيات.
3. Phase 3: إنشاء فاتورة بيع تجريبية والتأكد أن القيد في `journal_entries`/`journal_entry_lines` متوازن (مدين = دائن) ومطابق لميزان المراجعة.
4. Phase 2: التأكد أن `stock_balances.avg_cost` يتحدث بشكل صحيح بعد فاتورة شراء ثم فاتورة بيع.
5. تشغيل أي اختبارات Laravel موجودة (`php artisan test`) كمرجع نمط.
