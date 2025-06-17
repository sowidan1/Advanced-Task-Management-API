<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Task API",
 *     description="Task management API"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Task",
 *     required={"title", "due_date", "priority", "status"},
 *     @OA\Property(property="title", type="string", example="Buy groceries"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2025-06-30"),
 *     @OA\Property(property="priority", type="string", example="high"),
 *     @OA\Property(property="status", type="string", example="pending")
 * )
 */
class SwaggerController {}
