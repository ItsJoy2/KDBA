<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VakalatnamaSticker;
use App\Services\VakalatnamaStickerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VakalatnamaPrintController extends Controller
{
    public function __construct(
        protected VakalatnamaStickerService $stickerService
    ) {
    }

    /**
     * Print a sticker for the first time.
     *
     * This operation consumes the first-print authorization
     * before the browser receives the print view.
     */
    public function print(
        Request $request,
        VakalatnamaSticker $sticker
    ): View {
        abort_unless(
            $request->user()->hasPermission('vakalatnama.print'),
            403
        );

        $claimedSticker = $this->stickerService->claimPrint(
            actor: $request->user(),
            stickerId: $sticker->id,
            isReprint: false
        );

        $claimedSticker = $this->stickerService->getStickerForPrinting(
            actor: $request->user(),
            stickerId: $claimedSticker->id
        );

        $claimedSticker->load([
            'user',
            'order',
        ]);

        // This view is for authorized printing only.
        // Do not reuse this view in member-facing order pages.
        return view(
            'admin.vakalatnama-stickers.print',
            [
                'sticker' => $claimedSticker,
                'isReprint' => false,
            ]
        );
    }

    /**
     * Reprint a sticker.
     *
     * Requires the dedicated reprint permission.
     */
    public function reprint(
        Request $request,
        VakalatnamaSticker $sticker
    ): View {
        abort_unless(
            $request->user()->hasPermission('vakalatnama.reprint'),
            403
        );

        $claimedSticker = $this->stickerService->claimPrint(
            actor: $request->user(),
            stickerId: $sticker->id,
            isReprint: true,
            remarks: $request->input('remarks')
        );

        $claimedSticker = $this->stickerService->getStickerForPrinting(
            actor: $request->user(),
            stickerId: $claimedSticker->id
        );

        $claimedSticker->load([
            'user',
            'order',
        ]);

        return view(
            'admin.vakalatnama-stickers.print',
            [
                'sticker' => $claimedSticker,
                'isReprint' => true,
            ]
        );
    }

    /**
     * Show print history for a sticker.
     */
    public function history(
        Request $request,
        VakalatnamaSticker $sticker
    ): View {
        abort_unless(
            $request->user()->hasPermission('vakalatnama.view'),
            403
        );

        $sticker->load([
            'user',
            'order',
            'printLogs.printer',
        ]);

        return view(
            'admin.vakalatnama-stickers.print-history',
            compact('sticker')
        );
    }

    /**
     * Return to the sticker order after printing.
     */
    public function back(
        VakalatnamaSticker $sticker
    ): RedirectResponse {
        return redirect()->route(
            'admin.vakalatnama.show',
            $sticker->order_id
        );
    }
}