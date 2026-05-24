<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateWhatsappSettingsRequest;
use App\Services\WhatsappSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class WhatsappController extends Controller
{
    public function __construct(
        private readonly WhatsappSettingsService $whatsappSettingsService,
    ) {}

    public function index(): View
    {
        return view('admin.whatsapp', $this->whatsappSettingsService->getSettingsPageData());
    }

    public function update(UpdateWhatsappSettingsRequest $request): RedirectResponse
    {
        $this->whatsappSettingsService->updateSettings($request->validated());

        return redirect()
            ->route('admin.whatsapp.index')
            ->with('status', 'whatsapp-settings-updated');
    }
}
