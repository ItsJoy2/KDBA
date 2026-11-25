<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVakalatnamaStickerRequest;
use App\Http\Requests\UpdateVakalatnamaStickerSettingsRequest;
use App\Models\SystemSetting;
use App\Models\VakalatnamaStickerOrder;
use App\Services\VakalatnamaStickerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VakalatnamaStickerController extends Controller
{
    public function __construct(
        protected VakalatnamaStickerService $stickerService
    ) {}

    /**
     * List sticker orders.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless(
            $user->hasPermission('vakalatnama-sticker-view'),
            403
        );

        $query = VakalatnamaStickerOrder::query()
            ->with([
                'user:id,full_name,membership_number',
                'creator:id,full_name',
            ])
            ->withCount('stickers')
            ->latest();

        if (!$user->hasPermission('vakalatnama-sticker-view-all')) {
            $query->where('user_id', $user->id);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view(
            'admin.vakalatnama-stickers.index',
            compact('orders')
        );
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        $user = request()->user();

        abort_unless(
            $user->hasPermission('vakalatnama-sticker-create')
            || $user->hasPermission('vakalatnama-sticker-create-own'),
            403
        );

        return view('admin.vakalatnama-stickers.create');
    }

    /**
     * Create sticker order.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless(
            $user->hasPermission('vakalatnama-sticker-create')
            || $user->hasPermission('vakalatnama-sticker-create-own'),
            403
        );

        $validated = $request->validate([
            'member_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:500',
            ],
        ]);

        $order = $this->stickerService->createOrder(
            $user,
            (int) $validated['member_id'],
            (int) $validated['quantity']
        );

        return redirect()
            ->route('admin.vakalatnama-stickers.index')
            ->with(
                'success',
                "Sticker order {$order->order_number} created successfully."
            );
}

    /**
     * Display sticker settings.
     */
    public function settings(): View
    {
        abort_unless(
            request()->user()->hasPermission(
                'vakalatnama-sticker-settings'
            ),
            403
        );

        $price = SystemSetting::getValue(
            'vakalatnama_sticker_price',
            '100.00'
        );

        $enabled = SystemSetting::getValue(
            'vakalatnama_sticker_enabled',
            true
        );

        return view(
            'admin.vakalatnama-stickers.settings',  compact('price', 'enabled')
        );
    }

    /**
     * Update sticker settings.
     */
    public function updateSettings(
        UpdateVakalatnamaStickerSettingsRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            SystemSetting::setValue(
                'vakalatnama_sticker_price',
                number_format(
                    (float) $validated['price'],
                    2,
                    '.',
                    ''
                ),
                'text',
                'vakalatnama_sticker'
            );

            SystemSetting::setValue(
                'vakalatnama_sticker_enabled',
                $validated['enabled'] ? '1' : '0',
                'boolean',
                'vakalatnama_sticker'
            );
        });

        return back()->with(
            'success',
            'Vakalatnama sticker settings updated successfully.'
        );
    }
}
