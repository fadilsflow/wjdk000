<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Sprayer\UpdateSprayerModeRequest;
use App\Http\Requests\Sprayer\UpdateSprayerStatusRequest;
use App\Services\SprayerControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class SprayerController extends Controller
{
    public function __construct(
        private readonly SprayerControlService $sprayerControlService,
    ) {}

    public function index(): View
    {
        return view('sprayer.control', $this->sprayerControlService->getControlPageData());
    }

    public function updateMode(UpdateSprayerModeRequest $request): RedirectResponse
    {
        $this->sprayerControlService->updateMode(
            $request->validated()['mode'],
            (int) $request->user()->id,
        );

        return redirect()
            ->route('sprayer.control')
            ->with('status', 'sprayer-mode-updated');
    }

    public function updateStatus(UpdateSprayerStatusRequest $request): RedirectResponse
    {
        $this->sprayerControlService->updateStatus(
            $request->validated()['status'],
            (int) $request->user()->id,
        );

        return redirect()
            ->route('sprayer.control')
            ->with('status', 'sprayer-status-updated');
    }
}
