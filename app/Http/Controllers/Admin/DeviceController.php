<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeviceConfigurationService;
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
}
