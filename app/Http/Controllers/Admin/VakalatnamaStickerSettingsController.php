<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vakalatnama\UpdateVakalatnamaStickerSettingsRequest;
use App\Services\VakalatnamaStickerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VakalatnamaStickerSettingsController extends Controller
{
    public function __construct(
        protected VakalatnamaStickerService $stickerService
    ) {
    }

    /**
     * Display sticker settings.
     */
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('vakalatnama.settings')
            || $request->user()->hasPermission('settings.manage'),
            403
        );

        $settings = $this->stickerService->getSettings();

        return view(
            'admin.vakalatnama-stickers.settings',
            compact('settings')
        );
    }

    /**
     * Update sticker settings.
     */
    public function update(
        UpdateVakalatnamaStickerSettingsRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $this->stickerService->updateSettings(
            actor: $request->user(),
            price: (float) $validated['price'],
            enabled: (bool) $validated['enabled']
        );

        return redirect()
            ->route('admin.vakalatnama.settings')
            ->with(
                'success',
                'Vakalatnama sticker settings updated successfully.'
            );
    }
}