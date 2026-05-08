<?php

namespace App\Observers;

use App\Models\LeadItem;

class LeadItemObserver
{
    /**
     * Handle the LeadItem "created" event.
     */
    public function created(LeadItem $leadItem): void
    {
        $this->syncLead($leadItem);
    }

    /**
     * Handle the LeadItem "updated" event.
     */
    public function updated(LeadItem $leadItem): void
    {
        $this->syncLead($leadItem);
    }

    /**
     * Handle the LeadItem "deleted" event.
     */
    public function deleted(LeadItem $leadItem): void
    {
        $this->syncLead($leadItem);
    }

    protected function syncLead(LeadItem $leadItem)
    {
        $lead = $leadItem->lead;
        if (!$lead) return;

        $firstItem = $lead->items()->orderBy('id')->first();

        if ($firstItem) {
            $lead->update([
                'product_id' => $firstItem->product_id,
                'product_model_id' => $firstItem->product_model_id,
                'model_series_id' => $firstItem->model_series_id,
                'quantity' => $firstItem->quantity,
            ]);
        } else {
            $lead->update([
                'product_id' => null,
                'product_model_id' => null,
                'model_series_id' => null,
                'quantity' => 0,
            ]);
        }
    }

    /**
     * Handle the LeadItem "restored" event.
     */
    public function restored(LeadItem $leadItem): void
    {
        //
    }

    /**
     * Handle the LeadItem "force deleted" event.
     */
    public function forceDeleted(LeadItem $leadItem): void
    {
        //
    }
}
