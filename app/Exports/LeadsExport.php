<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon; // Import Carbon for date formatting

class LeadsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Lead::with(['agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'dealership', 'items.product', 'items.productModel']);

        // Apply filters similar to LeadController's index method
        if (isset($this->filters['followup_filter']) && $this->filters['followup_filter'] === 'today') {
            $query->whereHas('followups', function ($q) {
                $q->whereDate('next_follow_up_date', today());
            });
        }

        if (isset($this->filters['status']) && !empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['has_followup']) && !empty($this->filters['has_followup'])) {
            if ($this->filters['has_followup'] === 'no') {
                $query->whereHas('followups');
            } elseif ($this->filters['has_followup'] === 'yes') {
                $query->whereDoesntHave('followups');
            }
        }

        if (isset($this->filters['lead_category_id']) && !empty($this->filters['lead_category_id'])) {
            $query->where('lead_category_id', $this->filters['lead_category_id']);
        }

        if (isset($this->filters['lead_source_id']) && !empty($this->filters['lead_source_id'])) {
            $query->where('lead_source_id', $this->filters['lead_source_id']);
        }

        if (isset($this->filters['dealership_id']) && !empty($this->filters['dealership_id'])) {
            $query->where('dealership_id', $this->filters['dealership_id']);
        }

        if (isset($this->filters['from_date']) && !empty($this->filters['from_date'])) {
            $query->whereHas('followups', function ($q) {
                $q->whereDate('next_follow_up_date', '>=', $this->filters['from_date']);
            });
        }

        if (isset($this->filters['to_date']) && !empty($this->filters['to_date'])) {
            $query->whereHas('followups', function ($q) {
                $q->whereDate('next_follow_up_date', '<=', $this->filters['to_date']);
            });
        }

        if (isset($this->filters['employee_assignment_status']) && !empty($this->filters['employee_assignment_status'])) {
            if ($this->filters['employee_assignment_status'] === 'assigned') {
                $query->whereNotNull('employee_id');
            } elseif ($this->filters['employee_assignment_status'] === 'unassigned') {
                $query->whereNull('employee_id');
            }
        }

        // Apply global search filter if present
        if (isset($this->filters['search_value']) && !empty($this->filters['search_value'])) {
            $keyword = $this->filters['search_value'];
            $query->where(function ($q) use ($keyword) {
                // Search 'name' column (salutation, name, email, phone_number)
                $q->orWhere('salutation', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%");

                // Search 'agent_data' (polymorphic agent and leadSource)
                $q->orWhereHasMorph('agent', [\App\Models\Employee::class], function ($morphQuery) use ($keyword) {
                    $morphQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('mobile', 'like', "%{$keyword}%");
                });
                $q->orWhereHasMorph('agent', [\App\Models\Agent::class], function ($morphQuery) use ($keyword) {
                    $morphQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone_number', 'like', "%{$keyword}%");
                });
                $q->orWhereHas('leadSource', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                });

                // Search 'product' (product name and lead_value)
                $q->orWhereHas('product', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                })->orWhere('lead_value', 'like', "%{$keyword}%");

                // Search 'leadCategory.name' and 'dealership.name'
                $q->orWhereHas('leadCategory', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                })->orWhereHas('dealership', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                });

                // Search 'status'
                $q->orWhere('status', 'like', "%{$keyword}%");
            });
        }

        // Apply dealership filter if user is not admin and has a dealership_id (from LeadController)
        if (isset($this->filters['user_dealership_id']) && $this->filters['user_dealership_id'] !== null) {
            $query->where('dealership_id', $this->filters['user_dealership_id']);
        }

        // Apply pagination limit if provided
        if (isset($this->filters['start']) && isset($this->filters['length'])) {
            $query->skip($this->filters['start'])->take($this->filters['length']);
        }

        return $query->get()->map(function ($lead) {
            $latestFollowup = $lead->followups->sortByDesc('created_at')->first();
            
            $productNames = [];
            $productModels = [];
            
            if ($lead->items && $lead->items->count() > 0) {
                foreach ($lead->items as $item) {
                    if ($item->product) {
                        $productNames[] = $item->product->name . ($item->quantity > 1 ? " (x{$item->quantity})" : "");
                    }
                    if ($item->productModel) {
                        $productModels[] = $item->productModel->name;
                    }
                }
            } else {
                if ($lead->product) {
                    $productNames[] = $lead->product->name . ($lead->quantity > 1 ? " (x{$lead->quantity})" : "");
                }
                if ($lead->productModel) {
                    $productModels[] = $lead->productModel->name;
                }
            }

            return [
                'ID' => $lead->id,
                'Salutation' => $lead->salutation,
                'Name' => $lead->name,
                'Email' => $lead->email,
                'Phone Number' => $lead->phone_number,
                'Agent Name' => $lead->agent ? $lead->agent->name : 'N/A',
                'Lead Source' => $lead->leadSource ? $lead->leadSource->name : 'N/A',
                'Lead Category' => $lead->leadCategory ? $lead->leadCategory->name : 'N/A',
                'Lead Value' => $lead->lead_value,
                'Product' => count($productNames) > 0 ? implode(', ', $productNames) : 'N/A',
                'Product Model' => count($productModels) > 0 ? implode(', ', $productModels) : 'N/A',
                'Dealership' => $lead->dealership ? $lead->dealership->name : 'N/A',
                'Allow Follow Up' => $lead->allow_follow_up ? 'Yes' : 'No',
                'Status' => $lead->status,
                'Chance of Success (%)' => $lead->chance_of_success,
                'Latest Follow Up Date' => $latestFollowup ? Carbon::parse($latestFollowup->next_follow_up_date)->format('Y-m-d H:i:s') : 'N/A',
                'Enquiry Received Date' => Carbon::parse($lead->created_at)->format('Y-m-d H:i:s'),
                'Location' => $lead->location,
                'Quantity' => $lead->quantity,
                'Financier' => $lead->financier,
                'Type' => $lead->type,
                'Login Status' => $lead->login_status,
                'Stage' => $lead->stage,
                'Remarks' => $lead->remarks,
                'Current Status' => $lead->current_status,
                'Created At' => Carbon::parse($lead->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Salutation',
            'Name',
            'Email',
            'Phone Number',
            'Agent Name',
            'Lead Source',
            'Lead Category',
            'Lead Value',
            'Product',
            'Product Model',
            'Dealership',
            'Allow Follow Up',
            'Status',
            'Chance of Success (%)',
            'Latest Follow Up Date',
            'Enquiry Received Date',
            'Location',
            'Quantity',
            'Financier',
            'Type',
            'Login Status',
            'Stage',
            'Remarks',
            'Current Status',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID
            'B' => 15, // Salutation
            'C' => 25, // Name
            'D' => 30, // Email
            'E' => 20, // Phone Number
            'F' => 25, // Agent Name
            'G' => 20, // Lead Source
            'H' => 20, // Lead Category
            'I' => 15, // Lead Value
            'J' => 20, // Product
            'K' => 20, // Product Model
            'L' => 25, // Dealership
            'M' => 15, // Allow Follow Up
            'N' => 15, // Status
            'O' => 20, // Chance of Success (%)
            'P' => 25, // Latest Follow Up Date
            'Q' => 25, // Enquiry Received Date
            'R' => 20, // Location
            'S' => 10, // Quantity
            'T' => 20, // Financier
            'U' => 20, // Type
            'V' => 20, // Login Status
            'W' => 20, // Stage
            'X' => 30, // Remarks
            'Y' => 20, // Current Status
            'Z' => 25, // Created At
        ];
    }
}
