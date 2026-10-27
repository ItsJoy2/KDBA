<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LawyerAssistantController extends Controller
{
    private const MEMBER_TYPES = [
        'lawyer_assistant',
        'apprentice',
    ];

    /**
     * Display assistants and apprentices.
     */

    public function index(Request $request): View
    {
        $actor = $request->user();

        abort_unless($actor, 403, 'Please login first.');

        $query = User::query()
            ->whereIn('member_status', self::MEMBER_TYPES)
            ->with('lawyer:id,full_name,membership_number');

        if (! $this->canManageAllUsers($actor)) {
            $query->where('lawyer_id', $actor->id);
        }

        $assistants = $query
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.assistants.index', compact('assistants'));
    }


    /**
     * Display one assistant or apprentice.
     */
    public function show(Request $request, User $assistant): View
    {
        $actor = $request->user();

        abort_unless(
            in_array($assistant->member_status, self::MEMBER_TYPES, true),
            404
        );

        $isStaff = $this->canManageAllUsers($actor);

        $isLawyer = $actor->member_status === ['member', 'life_member'];

        $isStaff = $this->canManageAllUsers($actor);

        $isOwnMember = (int) $assistant->lawyer_id === (int) $actor->id;

        $isSelf = (int) $assistant->id === (int) $actor->id;

        abort_unless($isStaff || $isOwnMember || $isSelf, 403);

        $assistant->load('lawyer:id,full_name,membership_number');

        $disk = Storage::disk('local');

        // Profile photo
        $photoUrl = null;

        if ($assistant->photo && $disk->exists($assistant->photo)) {
            $photoUrl = 'data:' . $disk->mimeType($assistant->photo)
                . ';base64,' . base64_encode(
                    $disk->get($assistant->photo)
                );
        }

        // NID images
        $nidImages = [];

        foreach (['front', 'back'] as $side) {
            $path = data_get($assistant->nid_images, $side);

            $nidImages[$side] = $path && $disk->exists($path)
                ? 'data:' . $disk->mimeType($path)
                    . ';base64,' . base64_encode(
                        $disk->get($path)
                    )
                : null;
        }

        return view('admin.assistants.show', compact(
            'assistant',
            'photoUrl',
            'nidImages'
        ));
    }

    /**
     * Staff access policy.
     */
    private function canManageAllUsers(User $actor): bool
    {
        return $actor->roles()
            ->whereIn('slug', [
                'super-admin',
                'system',
                'general-secretary',
                'office-staff',
            ])
            ->exists();
    }
}
