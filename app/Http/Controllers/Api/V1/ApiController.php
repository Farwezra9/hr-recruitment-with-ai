<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    /** ?per_page=15 (maks 100) */
    protected function perPage(Request $request): int
    {
        return min(100, max(1, (int) $request->query('per_page', 15)));
    }
}
