<?php

namespace App\Http\Controllers;

use App\Services\FacebookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacebookPostController extends Controller
{
    protected FacebookService $facebookService;

    public function __construct(FacebookService $facebookService)
    {
        $this->facebookService = $facebookService;
    }

    public function showForm()
    {
        $pageInfo = $this->facebookService->getPageInfo();

        return view('facebook.post-form', compact('pageInfo'));
    }

    public function postToPage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $result = Auth::check()
            ? $this->facebookService->publishForUser(Auth::user(), $request->message)
            : $this->facebookService->postToConfiguredPage($request->message);

        if ($result['success']) {
            $message = $result['message'] ?? 'Publicación creada exitosamente';
            if (! empty($result['facebook_post_id'])) {
                $message .= ' (ID Meta: ' . $result['facebook_post_id'] . ')';
            }

            return back()->with('success', $message);
        }

        return back()->with('error', 'Error: ' . ($result['error'] ?? 'No se pudo publicar.'));
    }
}
