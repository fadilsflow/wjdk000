<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        return view('dashboard', $this->dashboardService->getDashboardData());
    }

    public function latest(): JsonResponse
    {
        return response()->json($this->dashboardService->getDashboardData());
    }
}
