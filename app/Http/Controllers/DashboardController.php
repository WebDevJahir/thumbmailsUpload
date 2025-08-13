<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->bulkRequests()->with('images');
        if ($status = $request->query('status')) {
            $query->whereHas('images', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }
        $requests = $query->latest()->get();
        return view('dashboard', compact('requests'));
    }
}
