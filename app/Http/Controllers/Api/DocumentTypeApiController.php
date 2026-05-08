<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Document Types",
 *     description="API Endpoints for managing document types"
 * )
 */
class DocumentTypeApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/document-types",
     *     operationId="getDocumentTypes",
     *     summary="Get all document types",
     *     description="Retrieve a list of all available document types",
     *     tags={"Document Types"},
     *     security={{"bearerAuth": {}}}, 
     *     @OA\Response(
     *         response=200,
     *         description="List of document types",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/DocumentType")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $documentTypes = DocumentType::all();
        return response()->json($documentTypes);
    }
}
