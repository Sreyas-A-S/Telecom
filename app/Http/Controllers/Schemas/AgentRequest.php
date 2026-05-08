<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="AgentRequest",
 *     description="Agent request body",
 *     required={"name"}
 * )
 */
class AgentRequest
{
    /**
     * @OA\Property(
     *     title="User ID",
     *     description="ID of the associated user",
     *     format="int64",
     *     nullable=true
     * )
     *
     * @var int
     */
    public $user_id;

    /**
     * @OA\Property(
     *     title="Dealership ID",
     *     description="ID of the associated dealership",
     *     format="int64",
     *     nullable=true
     * )
     *
     * @var int
     */
    public $dealership_id;

    /**
     * @OA\Property(
     *     title="Zone ID",
     *     description="ID of the associated zone",
     *     format="int64",
     *     nullable=true
     * )
     *
     * @var int
     */
    public $zone_id;

    /**
     * @OA\Property(
     *     title="Status",
     *     description="Status of the agent (active or inactive)",
     *     example="active",
     *     enum={"active", "inactive"},
     *     nullable=true
     * )
     *
     * @var string
     */
    public $status;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name of the agent",
     *     example="John Doe",
     *     maxLength=255
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Email",
     *     description="Email of the agent",
     *     example="john.doe@example.com",
     *     maxLength=255,
     *     nullable=true
     * )
     *
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *     title="Phone Number",
     *     description="Phone number of the agent",
     *     example="+1234567890",
     *     maxLength=20,
     *     nullable=true
     * )
     *
     * @var string
     */
    public $phone_number;

    /**
     * @OA\Property(
     *     title="Employee ID",
     *     description="ID of the associated employee",
     *     format="int64",
     *     nullable=true
     * )
     *
     * @var int
     */
    public $employee_id;

    /**
     * @OA\Property(
     *     title="Is Employee",
     *     description="Indicates if the agent is also an employee",
     *     type="boolean",
     *     example=false,
     *     nullable=true
     * )
     *
     * @var bool
     */
    public $is_employee;
}