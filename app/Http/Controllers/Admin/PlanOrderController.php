<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanOrderController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'plan_ids' => ['required', 'array', 'min:1'],
            'plan_ids.*' => ['required', 'integer', 'distinct', 'exists:plan,id'],
        ]);

        $visibleIds = collect($validated['plan_ids'])->map(fn ($id) => (int) $id)->values();

        DB::transaction(function () use ($visibleIds) {
            $allIds = Plan::orderBy('orden')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->values();
            $visibleSet = $visibleIds->flip();
            $positions = $allIds
                ->keys()
                ->filter(fn ($position) => $visibleSet->has($allIds[$position]))
                ->values();

            foreach ($positions as $index => $position) {
                if ($visibleIds->has($index)) {
                    $allIds[$position] = $visibleIds[$index];
                }
            }

            foreach ($allIds as $index => $planId) {
                Plan::whereKey($planId)->update(['orden' => $index + 1]);
            }
        });

        return response()->json([
            'message' => 'Orden de planes actualizado.',
            'orders' => Plan::whereIn('id', $visibleIds->all())->pluck('orden', 'id'),
        ]);
    }
}
