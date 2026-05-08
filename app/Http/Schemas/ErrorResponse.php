<?php

namespace App\Http\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ErrorResponse",
 *     description="Standard error response",
 *     @OA\Xml(name="ErrorResponse")
 * )
 */
class ErrorResponse
{
    /**
     * @OA\Property(
     *     title="Error Message",
     *     description="A human-readable error message",
     *     example="Agent not found."
     * )
     *
     * @var string
     */
    public $error;
}
