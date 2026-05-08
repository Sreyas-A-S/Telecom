<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * This file contains explicit OpenAPI component schemas when model scanning fails.
 */

/**
 * @OA\Schema(
 *     schema="Agent",
 *     type="object",
 *     title="Agent",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="user_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="dealership_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="zone_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true),
 *     @OA\Property(property="phone_number", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"active","inactive"}),
 *     @OA\Property(property="employee_id", type="integer", format="int64", nullable=true),
 *     @OA\Property(property="is_employee", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="employee", type="object", nullable=true),
 *     @OA\Property(property="type", type="string"),
 *     @OA\Property(property="display_name", type="string")
 * )
 */
class SwaggerSchemas
{
    // This class is only a holder for annotations and is not intended to be instantiated.
}

/**
 * @OA\Schema(
 *     schema="Employee",
 *     type="object",
 *     title="Employee",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true),
 *     @OA\Property(property="mobile", type="string", nullable=true),
 *     @OA\Property(property="employee_id", type="string", nullable=true),
 *     @OA\Property(property="is_broker", type="boolean"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class SwaggerEmployeeSchema {}

/**
 * @OA\Schema(
 *     schema="FSRPartQuotation",
 *     type="object",
 *     title="FSR Part Quotation",
 *     @OA\Property(property="part_id", type="integer", format="int64"),
 *     @OA\Property(property="quoted_quantity", type="integer"),
 * )
 */
class SwaggerFSRPartQuotationSchema {}
