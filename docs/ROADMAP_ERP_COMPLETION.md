# خارطة طريق استكمال ERP متكامل (NEXA)

> هذا المستند مرجعي دائم داخل المستودع. أي جلسة عمل جديدة (من أي جهاز) يجب أن تبدأ بقراءته لمعرفة ما تم إنجازه وما هو التالي.
> عند إنجاز أي بند ضع علامة `[x]` وأضف تاريخ الإنجاز، والتزم بنفس الاصطلاحات الموصوفة هنا عند إضافة أي كود جديد.

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

- [ ] **المحاسبة العامة** — لا يوجد دليل حسابات ولا قيود يومية ولا ترحيل تلقائي ولا تقارير مالية.
- [ ] **الخزينة والبنوك** — لا يوجد خزائن/حسابات بنكية/سندات قبض وصرف/شيكات (رغم أن SalesPayment/PurchasePayment فيهم حقول شبه-سند بدون ترحيل فعلي).
- [ ] **الأصول الثابتة** — لا يوجد سجل أصول ولا إهلاك.
- [ ] **التصنيع/الإنتاج** — لا يوجد BOM ولا أوامر إنتاج (لكن `items` جاهز بأنواع raw_material/semi_finished).
- [ ] **تكلفة المخزون** — `stock_balances` بدون avg_cost/total_value، فلا يوجد COGS حقيقي.
- [ ] **ربط الفاتورة الإلكترونية بالمحاسبة** — التكامل الحالي يغيّر flags فقط، بدون قيد محاسبي فعلي.

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
         خدمة الترحيل JournalPostingService، تقارير مالية)                  [ ]
Phase 2: ترقية تكلفة المخزون (avg_cost/total_value على StockBalance،
         تعديل StockService::adjustStock لإرجاع تكلفة COGS)                 [ ]
Phase 3: ربط الترحيل التلقائي بالموديولات القائمة
         (فواتير بيع/شراء، رواتب، مرتجعات)                                  [ ]
Phase 4: الخزينة والبنوك (خزائن، حسابات بنكية، سندات قبض/صرف، شيكات)         [ ]
Phase 5: الأصول الثابتة (سجل أصول، إهلاك)                                    [ ]
Phase 6: التصنيع/الإنتاج (BOM، أوامر إنتاج)                                  [ ]
Phase 7: استكمال ربط الفاتورة الإلكترونية بالمحاسبة                          [ ]
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

---

### Phase 2 — ترقية تكلفة المخزون

- إضافة أعمدة: `stock_balances.avg_cost`, `stock_balances.total_value`؛ `items.costing_method` (enum `weighted_average`), `items.avg_cost` (نسخة مجمّعة للتقارير).
- `stock_movements`: إضافة `total_cost` (nullable).
- تعديل `app/Services/StockService.php::adjustStock()`:
  - عند الإضافة (+): إعادة حساب المتوسط المرجح.
  - عند الخصم (−): استخدام `avg_cost` الحالي كتكلفة COGS بدل التكلفة المرسلة من المستدعي، وإرجاعها للمستدعي.
  - **تغيير كاسر لتوقيع الدالة** — كل الأماكن المستخدمة لها (مثل `SalesInvoicesController::store`) تحتاج تعديل سطر واحد لقراءة الشكل الجديد.

---

### Phase 3 — ربط الترحيل التلقائي

داخل `DB::transaction` الموجودة فعلاً بكل Controller:
- `SalesInvoicesController::store()` → قيد (AR_CONTROL مدين / SALES_REVENUE دائن / VAT_OUTPUT دائن) + قيد COGS (COGS مدين / INVENTORY دائن). `cancel()`/`update()` يعكسوا القيد عبر `reverse()`.
- `PurchaseInvoicesController` المكافئ → (INVENTORY أو EXPENSE حسب نوع الصنف / VAT_INPUT / AP_CONTROL).
- `PayrollController::approve()`/`unapprove()` → (SALARY_EXPENSE مدين / SALARY_PAYABLE دائن) — استحقاق فقط، الصرف الفعلي في Phase 4.
- مرتجعات البيع/الشراء → نفس النمط بعكس الاتجاه.
- كل hook يستدعي `alreadyPosted()` أولاً لمنع الترحيل المزدوج.

---

### Phase 4 — الخزينة والبنوك

**جداول:** `cash_boxes`, `bank_accounts`, `treasury_vouchers` (voucher_number RV/PV-YYYY-XXXX, voucher_type[receipt|payment], payment_method[cash|bank|cheque], party_type/party_id, linked_type/linked_id, cheque_id, status[draft|posted|cancelled]), `cheques` (direction[received|issued], status[under_collection|collected|bounced|cancelled]), `bank_reconciliations` + `bank_reconciliation_lines`.

**Service:** `app/Services/Treasury/TreasuryService.php` — `createVoucher()`, `recordCheque()`, `collectCheque()`, `bounceCheque()` (شيك تحت التحصيل يترحل على حساب وسيط "شيكات تحت التحصيل" وليس البنك مباشرة).

**Controllers:** `app/Http/Controllers/Admin/Treasury/` — `CashBoxesController`, `BankAccountsController`, `ReceiptVouchersController`, `PaymentVouchersController`, `ChequesController`, `BankReconciliationController`, `TreasuryReportsController`.

**module_keys (sort_order 60-66)**, **Sidebar:** `sidebar_treasury.blade.php`.

ربط: إضافة `treasury_voucher_id` (nullable FK) إلى `sales_payments`/`purchase_payments` دون تعديل السجلات القديمة.

---

### Phase 5 — الأصول الثابتة

**جداول:** `asset_categories` (asset/accum_depreciation/depreciation_expense GL accounts), `fixed_assets` (asset_number FA-YYYY-XXXX, purchase_cost, useful_life_years, salvage_value, accumulated_depreciation, book_value, status[active|disposed|transferred|fully_depreciated], source_purchase_invoice_id nullable), `asset_depreciation_entries` (unique per fixed_asset+year+month لمنع تكرار التشغيل), `asset_transfers`.

**Service:** `app/Services/Assets/DepreciationService.php` — `monthlyAmount()` (قسط ثابت)، `runMonthly()` (ترحيل مجمّع لكل فئة، idempotent).

**Controllers:** `app/Http/Controllers/Admin/Assets/` — `AssetCategoriesController`, `FixedAssetsController` (+ `dispose`, `transfer`), `DepreciationRunController`, `AssetReportsController`.

**module_keys (sort_order 70-73)**, **Sidebar:** `sidebar_assets.blade.php`.

---

### Phase 6 — التصنيع/الإنتاج

**جداول:** `bill_of_materials` + `bill_of_material_lines` (component_item_id من raw_material/semi_finished الموجودة بالفعل)، `production_orders` (order_number PRO-YYYY-XXXX, source/target warehouse, material/labor/overhead/total cost, status[draft|in_progress|completed|cancelled])، `production_order_materials`، `production_receipts`.

**Service:** `app/Services/Manufacturing/ProductionService.php` — `createFromBom()`, `issueMaterials()` (خصم مخزون بتكلفة المتوسط المرجح + قيد WIP/Inventory)، `receiveFinishedGoods()` (إضافة مخزون بتكلفة الإنتاج الفعلية + قيد Inventory-FG/WIP)، `complete()`.

**Controllers:** `app/Http/Controllers/Admin/Manufacturing/` — `BillOfMaterialsController`, `ProductionOrdersController`, `ManufacturingReportsController`.

**module_keys (sort_order 80-82)**, **Sidebar:** `sidebar_manufacturing.blade.php`.

---

### Phase 7 — استكمال ربط الفاتورة الإلكترونية

- إضافة `sales_invoice_id`/`purchase_invoice_id` (nullable FK) إلى `eta_invoices`.
- `TaxController::postInvoice()`/`postBulk()` يتحقق أن الفاتورة المرتبطة لها قيد محاسبي فعلي (`JournalPostingService::alreadyPosted`) قبل السماح بعلامة "مطابقة ETA".
- إيصال إلكتروني B2C (اختياري) → Phase 8.

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
