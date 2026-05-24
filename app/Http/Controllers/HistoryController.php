<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\History\HistoryFilterRequest;
use App\Services\HistoryService;
use Illuminate\View\View;

final class HistoryController extends Controller
{
    public function __construct(
        private readonly HistoryService $historyService,
    ) {}

    public function sensor(HistoryFilterRequest $request): View
    {
        return view('history.sensor', $this->historyService->getSensorHistoryData($request->validated()));
    }

    public function spray(HistoryFilterRequest $request): View
    {
        return view('history.spray', $this->historyService->getSprayHistoryData($request->validated()));
    }

    public function notification(HistoryFilterRequest $request): View
    {
        return view('history.notification', $this->historyService->getNotificationHistoryData($request->validated()));
    }
}
