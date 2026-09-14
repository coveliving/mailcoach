<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncCommunityPlusMembersJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncCommunityPlusMembersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subscribers' => ['required', 'array'],
            'subscribers.*.email' => ['required', 'email'],
            'subscribers.*.first_name' => ['nullable', 'string', 'max:255'],
            'subscribers.*.last_name' => ['nullable', 'string', 'max:255'],
        ]);

        SyncCommunityPlusMembersJob::dispatch($data['subscribers'])->onConnection('mailcoach-redis');

        return response()->json(['accepted' => count($data['subscribers'])], 202);
    }
}
