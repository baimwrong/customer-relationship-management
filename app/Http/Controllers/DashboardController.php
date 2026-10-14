<?php

namespace App\Http\Controllers;

use App\Services\CrmAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, CrmAnalyticsService $analytics): View
    {
        $user = $request->user();
        $metrics = $analytics->getDashboardMetrics($user);

        return view('dashboard.index', [
            'metrics' => $metrics,
            'user' => $user,
        ]);
    }
}
