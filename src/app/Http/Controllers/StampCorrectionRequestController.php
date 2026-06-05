<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $requests = StampCorrectionRequest::where('user_id', Auth::id())
            ->where('status', $status)
            ->with(['attendance', 'user'])
            ->latest()
            ->get();

        return view('attendance.requests', compact('requests', 'status'));
    }
}