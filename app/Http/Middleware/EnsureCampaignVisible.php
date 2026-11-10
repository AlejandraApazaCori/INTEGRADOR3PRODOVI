<?php

namespace App\Http\Middleware;

use App\Models\Campania;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCampaignVisible
{
    public function handle(Request $request, Closure $next): Response
    {
        $campaign = $request->route('campania');
        if ($campaign !== null) {
            $user = $request->user();
            abort_unless($user, 403);

            $query = Campania::query()
                ->whereKey($campaign instanceof Campania ? $campaign->getKey() : $campaign)
                ->visibleToStaff($user);
            abort_unless($query->exists(), 404);
        }

        return $next($request);
    }
}
