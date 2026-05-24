<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PublicSummaryService;
use Illuminate\View\View;

final class LandingController extends Controller
{
    public function __construct(
        private readonly PublicSummaryService $publicSummaryService,
    ) {}

    public function index(): View
    {
        return view('landing', $this->publicSummaryService->getPublicSummaryData());
    }
}
