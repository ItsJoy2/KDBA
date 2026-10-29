<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class CacheManagementController extends Controller
{
    private function authorizeCacheClear(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && (
                $user->hasPermission('cache.clear')
                || $user->roles()->where('slug', 'super-admin')->exists()
            ),
            403,
            'You are not authorized to manage application cache.'
        );
    }

    public function index(Request $request)
    {
        $this->authorizeCacheClear($request);

        return view('admin.system.cache');
    }

    public function clear(Request $request): JsonResponse
    {
        $this->authorizeCacheClear($request);

        try {
            Artisan::call('optimize:clear');

            Log::info('Application cache cleared.', [
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application cache cleared successfully.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Application cache clearing failed.', [
                'user_id' => $request->user()->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Cache clear failed. Please check the application logs.',
            ], 500);
        }
    }
}