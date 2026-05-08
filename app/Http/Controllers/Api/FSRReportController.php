<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FSRReport;
use App\Models\Part;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Models\FSRPayment;
use Throwable;

/**
 * @OA\Tag(
 *     name="FSR Reports",
 *     description="API Endpoints for FSR Reports"
 * )
 */
class FSRReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/fsr-reports",
     *     summary="Get list of FSR Reports",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task_id",
     *         in="query",
     *         description="Filter by task ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="lead_id",
     *         in="query",
     *         description="Filter by lead ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FSRReport")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = FSRReport::with(['task.entry.client', 'task.lead', 'submittedBy', 'partQuotations.part', 'paymentHistory.collectedBy']);

        if ($request->filled('task_id')) {
            $query->where('task_id', $request->task_id);
        }

        if ($request->filled('lead_id')) {
            $query->whereHas('task', function ($q) use ($request) {
                $q->where('lead_id', $request->lead_id);
            });
        }

        $fsrReports = $query->latest()->paginate(10);

        $fsrReports->getCollection()->transform(function ($report) {
            $report->part_quotations = $report->partQuotations;
            $report->payment_history = $report->paymentHistory;
            return $report;
        });

        return response()->json($fsrReports);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/fsr-reports",
     *     summary="Create a new FSR Report",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="task_id", type="integer", description="ID of the associated task"),
     *                 @OA\Property(property="on_site_assessment", type="string", nullable=true),
     *                 @OA\Property(property="analysis_of_cause", type="string", nullable=true),
     *                 @OA\Property(property="actions_taken", type="string", nullable=true),
     *                 @OA\Property(property="payment_amount", type="number", format="float", nullable=true, description="Payment amount collected"),
     *                 @OA\Property(property="payment_mode", type="string", enum={"cash", "online", "cheque", "other"}, nullable=true),
     *                 @OA\Property(property="payment_remarks", type="string", nullable=true),
     *                 @OA\Property(property="collected_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(
     *                     property="part_quotations",
     *                     type="string",
     *                     description="JSON-encoded string of part quotations. Format: [{'part_id': 1, 'quoted_quantity': 2}]"
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     nullable=true,
     *                     description="Array of images to upload"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="FSR Report created successfully.",
     *         @OA\JsonContent(ref="#/components/schemas/FSRReport")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error."
     *     )
     * )
     */
    public function store(Request $request)
    {
        // Fallback: some clients (or swagger) send multipart/form-data with real PUT requests.
        // PHP does not populate $_POST/$_FILES for PUT multipart bodies by default. If we receive
        // an empty payload on a PUT with a multipart content-type, try to parse the raw body
        // and merge text fields into the request so the controller can work normally.
        if ($request->method() === 'PUT' && empty($request->all())) {
            $contentType = $request->header('content-type', '');
            if (stripos($contentType, 'multipart/form-data') !== false) {
                $raw = @file_get_contents('php://input');
                if ($raw !== false && strlen($raw) > 0) {
                    // Attempt to extract boundary
                    if (preg_match('/boundary=(.*)$/', $contentType, $matches)) {
                        $boundary = trim($matches[1], '"');
                        $parts = preg_split('/-+' . preg_quote($boundary, '/') . '/', $raw);
                        $parsed = [];
                        foreach ($parts as $part) {
                            if (empty(trim($part))) continue;
                            // Separate headers and body
                            if (strpos($part, "\r\n\r\n") === false) continue;
                            list($rawHeaders, $body) = preg_split('//', $part, 2);
                            $body = trim($body, "\r\n-- ");
                            if (preg_match('/name="([^ ]+)"/', $rawHeaders, $nameMatch)) {
                                $name = $nameMatch[1];
                                // Skip file parts (they have filename in the headers)
                                if (stripos($rawHeaders, 'filename=') === false) {
                                    // For repeated fields like name[], append into array
                                    if (substr($name, -2) === '[]') {
                                        $key = substr($name, 0, -2);
                                        if (!isset($parsed[$key])) $parsed[$key] = [];
                                        $parsed[$key][] = $body;
                                    } else {
                                        $parsed[$name] = $body;
                                    }
                                }
                            }
                        }
                        if (!empty($parsed)) {
                            try {
                                $request->merge($parsed);
                                Log::info('FSRReportController.update merged parsed multipart PUT fields', ['parsed' => $parsed]);
                            } catch (Throwable $e) {
                                // ignore merge failures
                            }
                        }
                    }
                }
            }
        }

        // Handle part_quotations if it's a JSON string
        $partQuotations = $request->input('part_quotations', []);
        if (is_string($partQuotations)) {
            $decoded = json_decode($partQuotations, true);
            $partQuotations = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }
        $request->merge(['part_quotations' => is_array($partQuotations) ? $partQuotations : []]);

        $rules = [
            'task_id' => 'required|exists:tasks,id',
            'on_site_assessment' => 'nullable|string',
            'analysis_of_cause' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|required_with:payment_amount|string|in:cash,online,cheque,other',
            'payment_remarks' => 'nullable|string',
            'collected_at' => 'nullable|date',
            'part_quotations' => 'nullable|array',
            'part_quotations.*.part_id' => 'nullable|exists:parts,id',
            'part_quotations.*.quoted_quantity' => 'nullable|integer|min:1',
            'images' => 'nullable', // Allow single or array
        ];

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request) {
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                if (!is_array($images)) {
                    $images = [$images];
                }
                foreach ($images as $key => $image) {
                    // Diagnostic logging for each uploaded image to help debug failed uploads
                    try {
                        $isUploadedFile = $image instanceof \Symfony\Component\HttpFoundation\File\UploadedFile;
                        $path = $isUploadedFile ? $image->getPathname() : null;
                        $exists = $path ? file_exists($path) : false;
                        $size = $path && $exists ? filesize($path) : null;
                        $finfoMime = null;
                        if ($exists) {
                            try {
                                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                                $finfoMime = $finfo->file($path);
                            } catch (\Throwable $__e) {
                                $finfoMime = null;
                            }
                        }
                        Log::debug('FSRReportController: validating image', [
                            'key' => $key,
                            'is_uploadedfile_instance' => $isUploadedFile,
                            'path' => $path,
                            'exists' => $exists,
                            'size' => $size,
                            'detected_mime' => $finfoMime,
                        ]);
                    } catch (\Throwable $__e) {
                        // ignore logging failures
                    }
                    $imageValidator = Validator::make(['image' => $image], [
                        'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                    ]);
                    if ($imageValidator->fails()) {
                        // Add the original validation message but include diagnostics in the log
                        Log::warning('FSRReportController: image validation failed', [
                            'key' => $key,
                            'messages' => $imageValidator->errors()->get('image'),
                        ]);
                        $validator->errors()->add('images.' . $key, $imageValidator->errors()->first('image'));
                    }
                }
            }
        });

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        }

        $fsrReport = DB::transaction(function () use ($request) {
            $imagePaths = [];
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                if (!is_array($images)) {
                    $images = [$images];
                }
                foreach ($images as $image) {
                    $fullPath = storePublicFile($image, 'fsr_reports_images');
                    $imagePaths[] = str_replace('storage/', '', $fullPath);
                }
            }

            $fsrReport = FSRReport::create([
                'task_id' => $request->task_id,
                'on_site_assessment' => $request->on_site_assessment,
                'analysis_of_cause' => $request->analysis_of_cause,
                'actions_taken' => $request->actions_taken,
                'submitted_by_user_id' => Auth::id(),
                'payment_status' => 'pending',
                'images' => $imagePaths,
            ]);

            if ($request->filled('payment_amount') && $request->payment_amount > 0) {
                $fsrReport->paymentHistory()->create([
                    'amount' => $request->payment_amount,
                    'payment_mode' => $request->payment_mode,
                    'remarks' => $request->payment_remarks,
                    'collected_by_user_id' => Auth::id(),
                    'collected_at' => $request->collected_at ?? now(),
                ]);
            }

            $this->syncPaymentStatus($fsrReport);

            if ($request->has('part_quotations')) {
                $partQuotations = $request->part_quotations;
                if (!is_array(reset($partQuotations))) {
                    $partQuotations = [$partQuotations];
                }
                foreach ($partQuotations as $quotationData) {
                    if (isset($quotationData['part_id']) && isset($quotationData['quoted_quantity']) && $quotationData['quoted_quantity'] > 0) {
                        $part = Part::find($quotationData['part_id'], ['*']);
                        if ($part) {
                            $fsrReport->partQuotations()->create([
                                'part_id' => $quotationData['part_id'],
                                'quoted_quantity' => $quotationData['quoted_quantity'],
                                'quoted_unit_price' => $part->unit_price,
                                'status' => 'pending',
                            ]);
                        }
                    }
                }
            }
            return $fsrReport;
        });

        $fsrReport->load(['partQuotations.part', 'paymentHistory.collectedBy', 'submittedBy', 'task.entry.client', 'task.lead']);
        $fsrReport->part_quotations = $fsrReport->partQuotations;
        $fsrReport->payment_history = $fsrReport->paymentHistory;

        return response()->json(['message' => 'FSR Report created successfully.', 'fsrReport' => $fsrReport], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/fsr-reports/{fsrReport}",
     *     summary="Get a specific FSR Report",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrReport",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Report to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FSRReport")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FSR Report not found"
     *     )
     * )
     */
    public function show($id)
    {
        $fsrReport = FSRReport::findOrFail($id);
        $fsrReport->load(['partQuotations.part', 'partQuotations.approver', 'submittedBy', 'task.entry.client', 'task.lead', 'paymentHistory.collectedBy']);
        $fsrReport->part_quotations = $fsrReport->partQuotations;
        $fsrReport->payment_history = $fsrReport->paymentHistory;
        return response()->json($fsrReport);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *     path="/fsr-reports/{id}",
     *     summary="Update an FSR Report",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Report to update",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *
     *                 @OA\Property(property="on_site_assessment", type="string", nullable=true),
     *                 @OA\Property(property="analysis_of_cause", type="string", nullable=true),
     *                 @OA\Property(property="actions_taken", type="string", nullable=true),
     *                 @OA\Property(property="payment_amount", type="number", format="float", nullable=true, description="Payment amount collected"),
     *                 @OA\Property(property="payment_mode", type="string", enum={"cash", "online", "cheque", "other"}, nullable=true),
     *                 @OA\Property(property="payment_remarks", type="string", nullable=true),
     *                 @OA\Property(property="collected_at", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, nullable=true),
     *                 @OA\Property(
     *                     property="part_quotations",
     *                     type="string",
     *                     description="JSON-encoded string of part quotations. Format: [{'part_id': 1, 'quoted_quantity': 2}]"
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     nullable=true,
     *                     description="Array of new images to upload"
     *                 ),
     *                 @OA\Property(
     *                     property="existing_images",
     *                     type="string",
     *                     description="JSON-encoded string of existing image paths to keep"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FSR Report updated successfully.",
     *         @OA\JsonContent(ref="#/components/schemas/FSRReport")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FSR Report not found."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error."
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $fsrReport = FSRReport::findOrFail($id);

        // Normalize JSON fields
        $request->merge([
            'part_quotations' => json_decode($request->input('part_quotations', '[]'), true) ?: [],
            'existing_images' => json_decode($request->input('existing_images', '[]'), true) ?: [],
        ]);

        // Convert base64 images to UploadedFile instances
        $processedImages = [];
        if ($request->has('images')) {
            $images = $request->input('images');
            if (!is_array($images)) {
                $images = [$images];
            }
            foreach ($images as $image) {
                if (is_string($image) && str_starts_with($image, 'data:')) {
                    $uploadedFile = base64ToFile($image);
                    if ($uploadedFile) {
                        $processedImages[] = $uploadedFile;
                    } else {
                        Log::warning('Failed to convert base64 image to UploadedFile.');
                    }
                } elseif ($image instanceof UploadedFile) {
                    $processedImages[] = $image;
                }
            }
        }
        $request->files->set('images', $processedImages);
        $request->offsetUnset('images');

        // Validation rules
        $rules = [
            'on_site_assessment' => 'nullable|string',
            'analysis_of_cause' => 'nullable|string',
            'actions_taken' => 'nullable|string',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_mode' => 'nullable|required_with:payment_amount|string|in:cash,online,cheque,other',
            'payment_remarks' => 'nullable|string',
            'collected_at' => 'nullable|date',
            'status' => 'nullable|string|in:pending,approved,rejected',
            'part_quotations' => 'nullable|array',
            'part_quotations.*.part_id' => 'required_with:part_quotations.*.quoted_quantity|exists:parts,id',
            'part_quotations.*.quoted_quantity' => 'required_with:part_quotations.*.part_id|integer|min:1',
            'images' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::transaction(function () use ($request, $fsrReport) {
            $currentImages = $fsrReport->images ?? [];
            $newImages = [];

            // Save new uploaded images
            if ($request->hasFile('images')) {
                $images = $request->file('images');
                if (!is_array($images)) {
                    $images = [$images];
                }
                foreach ($images as $image) {
                    $fullPath = storePublicFile($image, 'fsr_reports_images');
                    $newImages[] = str_replace('storage/', '', $fullPath);
                }
            }

            // If existing_images not sent, keep all current ones
            $existingImages = $request->input('existing_images', $currentImages);

            // Merge and ensure unique entries
            $finalImages = array_values(array_unique(array_merge($existingImages, $newImages)));

            // Delete only removed images
            foreach ($currentImages as $imagePath) {
                if (!in_array($imagePath, $finalImages)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            // Update FSR report fields
            $fsrReport->update([
                'on_site_assessment' => $request->on_site_assessment,
                'analysis_of_cause' => $request->analysis_of_cause,
                'actions_taken' => $request->actions_taken,
                'status' => $request->status,
                'images' => $finalImages,
            ]);

            if ($request->filled('payment_amount') && $request->payment_amount > 0) {
                $fsrReport->paymentHistory()->create([
                    'amount' => $request->payment_amount,
                    'payment_mode' => $request->payment_mode,
                    'remarks' => $request->payment_remarks,
                    'collected_by_user_id' => Auth::id(),
                    'collected_at' => $request->collected_at ?? now(),
                ]);
            }

            $this->syncPaymentStatus($fsrReport);

            // Update part quotations
            $fsrReport->partQuotations()->delete();
            foreach ($request->part_quotations as $q) {
                if (isset($q['part_id'], $q['quoted_quantity']) && $q['quoted_quantity'] > 0) {
                    $part = Part::find($q['part_id'], ['*']);
                    if ($part) {
                        $fsrReport->partQuotations()->create([
                            'part_id' => $q['part_id'],
                            'quoted_quantity' => $q['quoted_quantity'],
                            'quoted_unit_price' => $part->unit_price,
                            'status' => 'pending',
                        ]);
                    }
                }
            }
        });

        $fsrReport->refresh()->load(
            'partQuotations.part',
            'partQuotations.approver',
            'submittedBy',
            'task.entry.client',
            'task.lead',
            'paymentHistory.collectedBy'
        );
        $fsrReport->part_quotations = $fsrReport->partQuotations;
        $fsrReport->payment_history = $fsrReport->paymentHistory;

        return response()->json([
            'message' => 'FSR Report updated successfully.',
            'fsrReport' => $fsrReport,
        ]);
    }


    /**
     * Handle the update from a POST request to work around form method limitations.
     */
    public function updateFromPost(Request $request, $id)
    {
        return $this->update($request, $id);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/fsr-reports/{fsrReport}",
     *     summary="Delete an FSR Report",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrReport",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Report to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FSR Report deleted successfully."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="FSR Report not found"
     *     )
     * )
     */
    public function destroy($id)
    {
        $fsrReport = FSRReport::findOrFail($id);
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

    private function syncPaymentStatus(FSRReport $fsrReport)
    {
        $totalCollected = $fsrReport->paymentHistory()->sum('amount');
        $servicePrice = $fsrReport->task->lead_id ? ($fsrReport->task->lead->lead_value ?? 0) : ($fsrReport->task->entry->price ?? 0);

        if ($totalCollected >= $servicePrice && $servicePrice > 0) {
            $fsrReport->update(['payment_status' => 'paid']);
        } else {
            $fsrReport->update(['payment_status' => 'pending']);
        }
    }

    /**
     * Delete an image from the FSR report.
     */
    public function deleteImage($id, $imageIndex)
    {
        $fsrReport = FSRReport::findOrFail($id);
        $images = $fsrReport->images;

        if (isset($images[$imageIndex])) {
            $imagePathToDelete = $images[$imageIndex];

            // Delete from storage if it exists
            Storage::disk('public')->delete($imagePathToDelete);

            // Also delete the physical duplicate from public/storage
            $physicalPath = public_path('storage/' . $imagePathToDelete);
            if (File::exists($physicalPath)) {
                File::delete($physicalPath);
            }

            // Remove from array and re-index
            array_splice($images, $imageIndex, 1);
            $fsrReport->images = $images;
            $fsrReport->save();

            return response()->json(['message' => 'Image deleted successfully.'], 200);
        }

        return response()->json(['message' => 'Image not found.'], 404);
    }

    /**
     * @OA\Get(
     *     path="/fsr-payments",
     *     summary="List all FSR payments",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsr_report_id",
     *         in="query",
     *         description="Filter by FSR Report ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/FSRPayment"))
     *     )
     * )
     */
    public function getPayments(Request $request)
    {
        $query = FSRPayment::with('collectedBy');

        if ($request->filled('fsr_report_id')) {
            $query->where('fsr_report_id', $request->fsr_report_id);
        }

        return response()->json($query->latest()->get());
    }

    /**
     * @OA\Post(
     *     path="/fsr-payments",
     *     summary="Record a new payment installment",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="fsr_report_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=500.00),
     *             @OA\Property(property="payment_mode", type="string", enum={"cash", "online", "cheque", "other"}, example="cash"),
     *             @OA\Property(property="remarks", type="string", nullable=true, example="Second installment"),
     *             @OA\Property(property="collected_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment recorded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", ref="#/components/schemas/FSRPayment"),
     *             @OA\Property(property="payment_status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function storePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fsr_report_id' => 'required|exists:fsr_reports,id',
            'amount' => 'required|numeric|min:0',
            'payment_mode' => 'required|string|in:cash,online,cheque,other',
            'remarks' => 'nullable|string',
            'collected_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fsrReport = FSRReport::findOrFail($request->fsr_report_id);

        $payment = DB::transaction(function () use ($request, $fsrReport) {
            $payment = $fsrReport->paymentHistory()->create([
                'amount' => $request->amount,
                'payment_mode' => $request->payment_mode,
                'remarks' => $request->remarks,
                'collected_by_user_id' => Auth::id(),
                'collected_at' => $request->collected_at ?? now(),
            ]);

            $this->syncPaymentStatus($fsrReport);

            return $payment;
        });

        return response()->json([
            'message' => 'Payment recorded successfully.',
            'payment' => $payment->load('collectedBy'),
            'payment_status' => $fsrReport->refresh()->payment_status
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/fsr-payments/{payment}",
     *     summary="Get payment details",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="ID of the FSRPayment record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/FSRPayment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function showPayment(FSRPayment $payment)
    {
        return response()->json($payment->load('collectedBy', 'fsrReport'));
    }

    /**
     * @OA\Put(
     *     path="/fsr-payments/{payment}",
     *     summary="Update a payment installment",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="ID of the FSRPayment record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float", example=600.00),
     *             @OA\Property(property="payment_mode", type="string", enum={"cash", "online", "cheque", "other"}, example="online"),
     *             @OA\Property(property="remarks", type="string", nullable=true, example="Updated remarks"),
     *             @OA\Property(property="collected_at", type="string", format="date-time", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="payment", ref="#/components/schemas/FSRPayment"),
     *             @OA\Property(property="payment_status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updatePayment(Request $request, FSRPayment $payment)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_mode' => 'sometimes|required|string|in:cash,online,cheque,other',
            'remarks' => 'nullable|string',
            'collected_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fsrReport = $payment->fsrReport;

        DB::transaction(function () use ($request, $payment, $fsrReport) {
            $payment->update($request->only(['amount', 'payment_mode', 'remarks', 'collected_at']));

            $this->syncPaymentStatus($fsrReport);
        });

        return response()->json([
            'message' => 'Payment updated successfully.',
            'payment' => $payment->load('collectedBy'),
            'payment_status' => $fsrReport->refresh()->payment_status
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/fsr-payments/{payment}",
     *     summary="Delete a payment installment",
     *     tags={"FSR Reports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="ID of the FSRPayment record",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function deletePayment(FSRPayment $payment)
    {
        $fsrReport = $payment->fsrReport;

        DB::transaction(function () use ($payment, $fsrReport) {
            $payment->delete();

            $this->syncPaymentStatus($fsrReport);
        });

        return response()->json([
            'message' => 'Payment deleted successfully.',
            'total_collected' => $fsrReport->paymentHistory()->sum('amount'),
            'payment_status' => $fsrReport->refresh()->payment_status
        ], 200);
    }
}
