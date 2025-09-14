<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Get all reports endpoint']);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['message' => "Get report {$id} endpoint"]);
    }
}
