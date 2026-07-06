<?php
namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\{FixedAsset, AssetCategory, AssetTransfer, Branche};
use App\Services\Accounting\JournalPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};

class FixedAssetsController extends Controller
{
    private function comCode(): int { return (int) Auth::guard('admin')->user()->com_code; }

    private function nextAssetNumber(): string
    {
        $last = FixedAsset::where('com_code', $this->comCode())->whereYear('created_at', now()->year)->max('asset_number');
        $num  = $last ? ((int) substr($last, -4)) + 1 : 1;
        return 'FA-'.now()->year.'-'.str_pad($num, 4, '0', STR_PAD_LEFT);
    }

    public function index(Request $request)
    {
        $query = FixedAsset::with('category')->where('com_code', $this->comCode());
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status'))      $query->where('status', $request->status);
        $data       = $query->orderByDesc('purchase_date')->paginate(20);
        $categories = AssetCategory::where('com_code', $this->comCode())->where('is_active', true)->get();
        return view('admin.assets.fixed_assets.index', compact('data', 'categories'));
    }

    public function create()
    {
        $categories = AssetCategory::where('com_code', $this->comCode())->where('is_active', true)->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        $nextNumber = $this->nextAssetNumber();
        return view('admin.assets.fixed_assets.create', compact('categories', 'branches', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id'       => 'required|exists:asset_categories,id',
            'name'              => 'required|string|max:200',
            'purchase_date'     => 'required|date',
            'purchase_cost'     => 'required|numeric|min:0.01',
            'useful_life_years' => 'required|integer|min:1',
        ]);

        $category = AssetCategory::where('com_code', $this->comCode())->findOrFail($request->category_id);
        $salvage  = (float) ($request->salvage_value ?? 0);
        $cost     = (float) $request->purchase_cost;

        FixedAsset::create([
            'com_code'           => $this->comCode(),
            'asset_number'       => $this->nextAssetNumber(),
            'category_id'        => $category->id,
            'name'               => $request->name,
            'description'        => $request->description,
            'branch_id'          => $request->branch_id ?: null,
            'location'           => $request->location,
            'purchase_date'      => $request->purchase_date,
            'purchase_cost'      => $cost,
            'useful_life_years'  => $request->useful_life_years,
            'salvage_value'      => $salvage,
            'depreciation_method'=> 'straight_line',
            'accumulated_depreciation' => 0,
            'book_value'         => $cost,
            'status'             => 'active',
            'created_by'         => Auth::guard('admin')->id(),
        ]);

        return redirect()->route('fixed_assets.index')->with('success', 'تم تسجيل الأصل بنجاح');
    }

    public function show($id)
    {
        $asset = FixedAsset::with(['category', 'branch', 'depreciationEntries', 'transfers'])
            ->where('com_code', $this->comCode())->findOrFail($id);
        return view('admin.assets.fixed_assets.show', compact('asset'));
    }

    public function edit($id)
    {
        $asset      = FixedAsset::where('com_code', $this->comCode())->findOrFail($id);
        $categories = AssetCategory::where('com_code', $this->comCode())->where('is_active', true)->get();
        $branches   = Branche::where('com_code', $this->comCode())->get();
        return view('admin.assets.fixed_assets.edit', compact('asset', 'categories', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $asset = FixedAsset::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['name' => 'required|string|max:200']);

        $asset->update([
            'name'        => $request->name,
            'description' => $request->description,
            'branch_id'   => $request->branch_id ?: null,
            'location'    => $request->location,
        ]);

        return redirect()->route('fixed_assets.show', $id)->with('success', 'تم تعديل بيانات الأصل');
    }

    public function dispose(Request $request, $id)
    {
        $asset = FixedAsset::where('com_code', $this->comCode())->findOrFail($id);
        if ($asset->status === 'disposed') {
            return back()->with('error', 'تم التخلص من هذا الأصل بالفعل');
        }
        $request->validate([
            'disposal_date'   => 'required|date',
            'disposal_amount' => 'required|numeric|min:0',
            'proceeds_account_id' => 'required_if:disposal_amount,>,0|nullable|exists:chart_of_accounts,id',
        ]);

        DB::transaction(function () use ($request, $asset) {
            $disposalAmount = (float) $request->disposal_amount;
            $gainLoss = $disposalAmount - $asset->book_value;

            $asset->update([
                'status'          => 'disposed',
                'disposal_date'   => $request->disposal_date,
                'disposal_amount' => $disposalAmount,
                'disposal_notes'  => $request->disposal_notes,
            ]);

            $category = $asset->category;
            if ($category->asset_gl_account_id && $category->accum_depreciation_gl_account_id) {
                // إغلاق حساب التكلفة ومجمع الإهلاك بالكامل، وتسجيل عائد التخلص والفارق كربح/خسارة
                $lines = [
                    ['account_id' => $category->accum_depreciation_gl_account_id, 'debit' => $asset->accumulated_depreciation, 'credit' => 0],
                    ['account_id' => $category->asset_gl_account_id, 'debit' => 0, 'credit' => $asset->purchase_cost],
                ];
                if ($disposalAmount > 0) {
                    $lines[] = ['account_id' => $request->proceeds_account_id, 'debit' => $disposalAmount, 'credit' => 0];
                }
                if (abs($gainLoss) > 0.0001) {
                    $lines[] = $gainLoss > 0
                        ? ['role' => 'GAIN_LOSS_ON_DISPOSAL', 'debit' => 0, 'credit' => $gainLoss]
                        : ['role' => 'GAIN_LOSS_ON_DISPOSAL', 'debit' => abs($gainLoss), 'credit' => 0];
                }

                JournalPostingService::post('asset_disposal', $asset->com_code, $lines, [
                    'source_module' => 'asset_disposal',
                    'source_id'     => $asset->id,
                    'entry_date'    => $request->disposal_date,
                    'reference'     => $asset->asset_number,
                    'description'   => 'التخلص من الأصل '.$asset->asset_number.' - '.$asset->name,
                    'created_by'    => Auth::guard('admin')->id(),
                ]);
            }
        });

        return back()->with('success', 'تم تسجيل التخلص من الأصل');
    }

    public function transfer(Request $request, $id)
    {
        $asset = FixedAsset::where('com_code', $this->comCode())->findOrFail($id);
        $request->validate(['to_branch_id' => 'required|exists:branches,id', 'transfer_date' => 'required|date']);

        DB::transaction(function () use ($request, $asset) {
            AssetTransfer::create([
                'fixed_asset_id' => $asset->id,
                'from_branch_id' => $asset->branch_id,
                'to_branch_id'   => $request->to_branch_id,
                'transfer_date'  => $request->transfer_date,
                'notes'          => $request->notes,
                'created_by'     => Auth::guard('admin')->id(),
            ]);
            $asset->update(['branch_id' => $request->to_branch_id]);
        });

        return back()->with('success', 'تم تسجيل نقل الأصل');
    }
}
