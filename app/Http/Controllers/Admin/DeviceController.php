<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDeviceRequest;
use App\Http\Requests\Admin\UpdateDeviceRequest;
use App\Http\Requests\Admin\UpdateThresholdRequest;
use App\Models\Device;
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

    public function store(StoreDeviceRequest $request): RedirectResponse
    {
        $this->deviceConfigurationService->createDevice($request->validated());

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'device-created');
    }

    public function update(UpdateDeviceRequest $request, Device $device): RedirectResponse
    {
        $this->deviceConfigurationService->updateDevice($device, $request->validated());

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'device-updated');
    }

    public function updateThreshold(UpdateThresholdRequest $request): RedirectResponse
    {
        $this->deviceConfigurationService->updateThreshold($request->validated());

        return redirect()
            ->route('admin.devices.index')
            ->with('status', 'threshold-updated');
    }
}
