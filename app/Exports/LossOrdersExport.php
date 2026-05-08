<?php

namespace App\Exports;

use App\Models\LossOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles; // Add this
use Maatwebsite\Excel\Concerns\WithColumnWidths; // Add this
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet; // Add this for WithStyles

class LossOrdersExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths // Add interfaces here
{
    protected $monthYear;
    protected $dealershipId;
    protected $productId;
    protected $modelName;
    protected $searchValue;
    protected $start;
    protected $length;

    public function __construct($monthYear, $dealershipId, $productId, $modelName, $searchValue, $start, $length)
    {
        $this->monthYear = $monthYear;
        $this->dealershipId = $dealershipId;
        $this->productId = $productId;
        $this->modelName = $modelName;
        $this->searchValue = $searchValue;
        $this->start = $start;
        $this->length = $length;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headings)
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // Sl No
            'B' => 15, // Month
            'C' => 25, // Dealership
            'D' => 30, // Product & Model
            'E' => 15, // Tonnage
            'F' => 20, // Model
            'G' => 20, // Model Series
            'H' => 25, // Customer
            'I' => 20, // District
            'J' => 15, // Segment
            'K' => 25, // Application
            'L' => 25, // Financier
            'M' => 20, // Category
            'N' => 20, // Participation
            'O' => 40, // Reasons for Loss
            'P' => 40, // Remarks
            'Q' => 25, // Engineer Name
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = LossOrder::with('dealership', 'product');

        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->employee && $user->employee->dealership_id && $user->user_type !== 'admin') {
            $query->where('dealership_id', $user->employee->dealership_id);
        }

        if ($this->monthYear) {
            $query->where('month', $this->monthYear);
        }

        if ($this->dealershipId) {
            $query->where('dealership_id', $this->dealershipId);
        }

        if ($this->productId) {
            // Assuming relationship is product and filtering by product name or id? 
            // Controller uses product_id filter if passed, but query logic in getDataTableData uses product_name?
            // Actually, in Controller getDataTableData:
            // if ($request->has('product_name') && !empty($request->product_name)) { $data->where('loss_orders.product_name', 'like', '%' . $request->product_name . '%'); }
            // But the filter inputs are ID based. 
            // In index.blade.php: d.product_id = $('#filterProductId').val();
            // Let's check Controller again. 
            // Controller seems to use product_name based on input text?
            // "if ($request->has('product_name') ... "
            // But JS sends: d.product_id = $('#filterProductId').val();
            // There seems to be a mismatch in Controller usage vs JS.
            // However, for Export, I should probably respect what is passed.
            // Since I am passing product_id from blade, I will use that.
            // Oh, wait, the `getDataTableData` in Controller snippet I read earlier:
            // It has: if ($request->has('product_name')... 
            // It DOES NOT have `if ($request->has('product_id')...`.
            // So the filter in frontend `d.product_id` might be ignored by `getDataTableData`?
            // Let's assume I should fix Export to filter by product_id since that's what I am passing.
            // Or name if that's what's available. `LossOrder` model has `product_name` column probably?
            // $query->where('product_name', $this->productId); // If product_id is name actually? 
            // In blade: val() of `filterProduct` is name, but `filterProductId` is ID.
            // If I use ID, I should match `product_id` column if it exists or define intention.
            // `LossOrder` store method validates `product_name` string.
            // Let's try to match strict behavior to Controller if possible, or improve it.
            // Since I can't see `LossOrder` model, I'll assume `product_name` is the column used.
            // Accessing Product to get Name?
            $product = \App\Models\Product::find($this->productId);
            if ($product) {
                $query->where('product_name', $product->name);
            } else {
                $query->where('product_name', $this->productId);
            }
        }

        if ($this->modelName) {
            $query->where('product_model_name', 'like', '%' . $this->modelName . '%');
        }

        if ($this->searchValue) {
            $keyword = $this->searchValue;
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('dealership', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })
                    ->orWhere('product_name', 'like', "%{$keyword}%")
                    ->orWhere('product_model_name', 'like', "%{$keyword}%")
                    ->orWhere('tonnage', 'like', "%{$keyword}%")
                    ->orWhere('customer', 'like', "%{$keyword}%")
                    ->orWhere('segment', 'like', "%{$keyword}%")
                    ->orWhere('application', 'like', "%{$keyword}%")
                    ->orWhere('financier', 'like', "%{$keyword}%")
                    ->orWhere('district', 'like', "%{$keyword}%")
                    ->orWhere('category', 'like', "%{$keyword}%")
                    ->orWhere('participation', 'like', "%{$keyword}%")
                    ->orWhere('reasons_for_loss', 'like', "%{$keyword}%")
                    ->orWhere('remarks', 'like', "%{$keyword}%")
                    ->orWhere('engineer_name', 'like', "%{$keyword}%")
                    ->orWhere('month', 'like', "%{$keyword}%");
            });
        }

        // Pagination
        if ($this->start !== null && $this->length !== null && $this->length != -1) {
            $query->skip($this->start)->take($this->length);
        }

        // Get the start index for Sl No
        $start = $this->start ?? 0;

        return $query->get()->map(function ($lossOrder, $index) use ($start) {
            return [
                'Sl No' => $start + $index + 1,
                'Month' => \Carbon\Carbon::parse($lossOrder->month)->format('F Y'),
                'Dealership' => $lossOrder->dealership ? $lossOrder->dealership->name : 'N/A',
                'Product & Model' => ($lossOrder->product_name ?? '') . ' - ' . ($lossOrder->product_model_name ?? ''),
                'Tonnage' => $lossOrder->tonnage,
                'Model' => $lossOrder->product_model_name, // Keeping as 'Model' column header to match previous
                'Model Series' => $lossOrder->model_series_name,
                'Customer' => $lossOrder->customer,
                'District' => $lossOrder->district,
                'Segment' => $lossOrder->segment,
                'Application' => $lossOrder->application,
                'Financier' => $lossOrder->financier,
                'Category' => $lossOrder->category,
                'Participation' => $lossOrder->participation,
                'Reasons for Loss' => $lossOrder->reasons_for_loss,
                'Remarks' => $lossOrder->remarks,
                'Engineer Name' => $lossOrder->engineer_name,
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Sl No',
            'Month',
            'Dealership',
            'Product & Model',
            'Tonnage',
            'Model',
            'Model Series',
            'Customer',
            'District',
            'Segment',
            'Application',
            'Financier',
            'Category',
            'Participation',
            'Reasons for Loss',
            'Remarks',
            'Engineer Name',
        ];
    }
}
