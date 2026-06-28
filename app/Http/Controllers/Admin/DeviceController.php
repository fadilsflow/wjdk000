<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateThresholdRequest;
use App\Services\DeviceConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class DeviceController extends Controller
{
    public function __construct(
        private readonly DeviceConfigurationService $deviceConfigurationService,
    ) {}

    public function index(): View
    {
        return view('admin.devices.index', $this->deviceConfigurationService->getIndexData());
    }

    public function updateThreshold(UpdateThresholdRequest $request): RedirectResponse
    {
        $this->deviceConfigurationService->updateThreshold($request->validated());

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'threshold-updated');
    }
}
