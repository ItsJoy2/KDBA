<?php

namespace App\Http\Controllers;

use App\Services\VakalatnamaStickerService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VakalatnamaVerificationController extends Controller
{
    public function __construct(
        protected VakalatnamaStickerService $stickerService
    ) {
    }

    /**
     * Public verification form.
     */
    public function index(): View
    {
        return view(
            'vakalatnama.verify'
        );
    }

    /**
     * Verify an 18-digit sticker number.
     */
    public function verify(
        Request $request,
        string $uniqueNumber
    ): View {
        $request->validate([
            'uniqueNumber' => [
                'sometimes',
                'string',
                'regex:/^[0-9]{18}$/',
            ],
        ]);

        $result = $this->stickerService->verifySticker(
            $uniqueNumber
        );

        return view(
            'vakalatnama.verification-result',
            [
                'result' => $result,
                'uniqueNumber' => $uniqueNumber,
            ]
        );
    }

    /**
     * Verify using a submitted form.
     */
    public function lookup(Request $request)
    {
        $validated = $request->validate([
            'unique_number' => [
                'required',
                'string',
                'regex:/^[0-9]{18}$/',
            ],
        ]);

        return redirect()->route(
            'vakalatnama.verify.number',
            [
                'uniqueNumber' => $validated['unique_number'],
            ]
        );
    }
}
