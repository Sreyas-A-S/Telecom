<?php

namespace App\Http\Controllers;

use App\Models\FSRReport;
use App\Models\FSRPartQuotation;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Models\FSRPayment;
use App\Models\Service;

class FSRReportController extends Controller
{
    /**
     * Store a newly created FSRReport in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'on_site_assessment' => 'nullable|string',
            'analysis_of_cause' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'payment_status' => 'nullable|string|in:paid',
            'part_quotations' => 'nullable|array',
            'part_quotations.*.part_id' => 'nullable|exists:parts,id',
            'part_quotations.*.quoted_quantity' => 'nullable|integer|min:1',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        DB::transaction(function () use ($request) {
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = $image->hashName();

                    // Ensure public storage directory exists
                    if (!File::exists(public_path('storage/fsr_reports_images'))) {
                        File::makeDirectory(public_path('storage/fsr_reports_images'), 0777, true, true);
                    }

                    // 1. Store to storage 'public' disk (storage/app/public/fsr_reports_images)
                    $path = Storage::disk('public')->putFileAs('fsr_reports_images', $image, $filename);

                    // 2. Exact duplicate at public/storage/fsr_reports_images
                    File::copy(storage_path('app/public/' . $path), public_path('storage/' . $path));

                    $imagePaths[] = $path;
                }
            }

            $fsrReport = FSRReport::create([
                'task_id' => $request->task_id,
                'on_site_assessment' => $request->on_site_assessment,
                'analysis_of_cause' => $request->analysis_of_cause,
                'actions_taken' => $request->actions_taken,
                'submitted_by_user_id' => Auth::id(),
                'payment_status' => $request->has('payment_status') ? 'paid' : 'pending',
                'images' => $imagePaths,
            ]);

            if ($request->has('part_quotations')) {
                foreach ($request->part_quotations as $quotationData) {
                    // Only process if both part_id and quoted_quantity are present and valid
                    if (isset($quotationData['part_id']) && isset($quotationData['quoted_quantity']) && $quotationData['quoted_quantity'] > 0) {
                        $part = Part::find($quotationData['part_id']);
                        if ($part) {
                            $fsrReport->partQuotations()->create([
                                'part_id' => $quotationData['part_id'],
                                'quoted_quantity' => $quotationData['quoted_quantity'],
                                'quoted_unit_price' => $part->unit_price, // Use price from Part model
                                'status' => 'pending', // Initial status
                            ]);
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'FSR Report created successfully.'], 201);
    }

    public function edit(FSRReport $fsrReport)
    {
        // Load the standard partQuotations relation and nested part so the edit view receives the expected data
        $fsrReport->load('partQuotations.part', 'submittedBy', 'task.entry.client', 'task.lead.client', 'paymentHistory.collectedBy');
        $task = $fsrReport->task;
        $task->load('entry.client.lead.leadSource', 'entry.client.lead.leadCategory', 'entry.client.lead.product', 'entry.client.lead.productModel', 'entry.client.lead.modelSeries', 'entry.product', 'entry.productModel', 'entry.modelSeries', 'lead.leadSource', 'lead.leadCategory', 'lead.product', 'lead.productModel', 'lead.modelSeries', 'lead.client', 'lead.items.product', 'lead.items.productModel');
        $client = $task->entry->client ?? $task->lead->client ?? null;
        $lead = $task->lead;
        return view('tasks.fsr.edit', compact('fsrReport', 'task', 'client', 'lead'));
    }

    /**
     * Update the specified FSRReport in storage.
     */
    public function update(Request $request, FSRReport $fsrReport)
    {
        $request->validate([
            'on_site_assessment' => 'nullable|string',
            'analysis_of_cause' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'part_quotations' => 'nullable|array',
            'part_quotations.*.part_id' => 'nullable|exists:parts,id',
            'part_quotations.*.quoted_quantity' => 'nullable|integer|min:1',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        DB::transaction(function () use ($request, $fsrReport) {
            $imagePaths = $fsrReport->images ?? [];

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $filename = $image->hashName();

                    // Ensure public storage directory exists
                    if (!File::exists(public_path('storage/fsr_reports_images'))) {
                        File::makeDirectory(public_path('storage/fsr_reports_images'), 0777, true, true);
                    }

                    // 1. Store to storage 'public' disk
                    $path = Storage::disk('public')->putFileAs('fsr_reports_images', $image, $filename);

                    // 2. Exact duplicate at public/storage
                    File::copy(storage_path('app/public/' . $path), public_path('storage/' . $path));

                    $imagePaths[] = $path;
                }
            }

            $fsrReport->update([
                'on_site_assessment' => $request->on_site_assessment,
                'analysis_of_cause' => $request->analysis_of_cause,
                'actions_taken' => $request->actions_taken,
                'images' => $imagePaths,
            ]);

            // Handle part quotations
            if ($request->has('part_quotations')) {
                $fsrReport->partQuotations()->delete();
                foreach ($request->part_quotations as $quotationData) {
                    if (isset($quotationData['part_id']) && isset($quotationData['quoted_quantity']) && $quotationData['quoted_quantity'] > 0) {
                        $part = Part::find($quotationData['part_id']);
                        if ($part) {
                            $fsrReport->partQuotations()->create([
                                'part_id' => $part->id,
                                'quoted_quantity' => $quotationData['quoted_quantity'],
                                'quoted_unit_price' => $part->unit_price,
                                'status' => 'pending',
                            ]);
                        }
                    }
                }
            } else {
                $fsrReport->partQuotations()->delete();
            }
        });

        return response()->json(['message' => 'FSR Report updated successfully.'], 200);
    }

    /**
     * Display the specified FSRReport.
     */
    public function show(FSRReport $fsrReport)
    {
        $fsrReport->load('partQuotations.part', 'partQuotations.approver', 'submittedBy', 'task.entry.client', 'task.lead.client', 'paymentHistory.collectedBy');
        $task = $fsrReport->task;
        $task->load('entry.client.lead.leadSource', 'entry.client.lead.leadCategory', 'entry.client.lead.product', 'entry.client.lead.productModel', 'entry.client.lead.modelSeries', 'entry.product', 'entry.productModel', 'entry.modelSeries', 'lead.leadSource', 'lead.leadCategory', 'lead.product', 'lead.productModel', 'lead.modelSeries', 'lead.client', 'lead.items.product', 'lead.items.productModel');
        $client = $task->entry->client ?? $task->lead->client ?? null;
        $lead = $task->lead;
        return view('tasks.fsr.show', compact('fsrReport', 'task', 'client', 'lead'));
    }

    public function getDetails(FSRReport $fsrReport)
    {
        $fsrReport->load([
            'task.assignedEmployee.user',
            'submittedBy',
            'partQuotations.part',
            'partQuotations.approver'
        ]);

        return response()->json($fsrReport);
    }

    /**
     * Remove the specified FSRReport from storage.
     */
    public function destroy(FSRReport $fsrReport)
    {
        DB::transaction(function () use ($fsrReport) {
            // Delete associated images from storage
            if ($fsrReport->images) {
                foreach ($fsrReport->images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                    // Also delete the physical duplicate from public/storage
                    $physicalPath = public_path('storage/' . $imagePath);
                    if (File::exists($physicalPath)) {
                        File::delete($physicalPath);
                    }
                }
            }
            $fsrReport->partQuotations()->delete();
            $fsrReport->delete();
        });

        return response()->json(['message' => 'FSR Report deleted successfully.'], 200);
    }

    public function deleteImage(FSRReport $fsrReport, $encodedImagePath)
    {
        $images = $fsrReport->images;
        $imagePathToDelete = urldecode($encodedImagePath);

        $key = array_search($imagePathToDelete, $images);

        if ($key !== false) {
            // Delete from storage if it exists
            Storage::disk('public')->delete($imagePathToDelete);

            // Also delete the physical duplicate from public/storage
            $physicalPath = public_path('storage/' . $imagePathToDelete);
            if (File::exists($physicalPath)) {
                File::delete($physicalPath);
            }

            // Remove from array and re-index
            array_splice($images, $key, 1);
            $fsrReport->images = $images;
            $fsrReport->save();

            return response()->json(['message' => 'Image deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Image not found in FSR report.'], 404);
    }

    public function showImage(FSRReport $fsrReport, $imageIndex)
    {
        $images = $fsrReport->images;

        if (!isset($images[$imageIndex])) {
            abort(404, 'Image not found in FSR report.');
        }

        $imagePath = $images[$imageIndex];

        if (!Storage::disk('public')->exists($imagePath)) {
            abort(404, 'Image file not found in storage.');
        }

        return Storage::disk('public')->response($imagePath);
    }

    /**
     * Add a payment installment to the FSR report.
     */
    public function addPayment(Request $request, FSRReport $fsrReport)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string|in:cash,online,cheque,other',
            'remarks' => 'nullable|string',
            'collected_at' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $fsrReport) {
            $fsrReport->paymentHistory()->create([
                'amount' => $request->amount,
                'payment_mode' => $request->payment_mode,
                'remarks' => $request->remarks,
                'collected_by_user_id' => Auth::id(),
                'collected_at' => $request->collected_at ?? now(),
            ]);

            // Calculate total collected
            $totalCollected = $fsrReport->paymentHistory()->sum('amount');

            // Get collectable amount (Service price or Lead Value)
            $servicePrice = $fsrReport->task->lead_id ? ($fsrReport->task->lead->lead_value ?? 0) : ($fsrReport->task->entry->price ?? 0);

            if ($totalCollected >= $servicePrice && $servicePrice > 0) {
                $fsrReport->update(['payment_status' => 'paid']);
            } else {
                $fsrReport->update(['payment_status' => 'pending']);
            }
        });

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'total_collected' => $fsrReport->paymentHistory()->sum('amount'),
            'payment_status' => $fsrReport->refresh()->payment_status
        ]);
    }

    /**
     * Remove a payment entry.
     */
    public function deletePayment(FSRPayment $payment)
    {
        $fsrReport = $payment->fsrReport;

        DB::transaction(function () use ($payment, $fsrReport) {
            $payment->delete();

            // Re-calculate status
            $totalCollected = $fsrReport->paymentHistory()->sum('amount');
            $servicePrice = $fsrReport->task->lead_id ? ($fsrReport->task->lead->lead_value ?? 0) : ($fsrReport->task->entry->price ?? 0);

            if ($totalCollected >= $servicePrice && $servicePrice > 0) {
                $fsrReport->update(['payment_status' => 'paid']);
            } else {
                $fsrReport->update(['payment_status' => 'pending']);
            }
        });

        return response()->json(['message' => 'Payment record deleted successfully.']);
    }
}
