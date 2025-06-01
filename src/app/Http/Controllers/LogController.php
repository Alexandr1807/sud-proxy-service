<?php

namespace App\Http\Controllers;

use App\Models\SudLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = SudLog::orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        return response()->json($logs);
    }
}
