<?php

namespace App\Services;

use App\Models\Tarea;
use App\Models\User;

class SocialPublicationService
{
    public function __construct(
        private readonly FacebookService $facebookService,
        private readonly InstagramService $instagramService,
    ) {}

    public function publish(User $user, Tarea $tarea, string $message, array $platforms): array
    {
        $platforms = array_values(array_unique(array_intersect($platforms, ['facebook', 'instagram'])));
        $results = [];

        foreach ($platforms as $platform) {
            $results[$platform] = $platform === 'facebook'
                ? $this->facebookService->publishTaskForUser($user, $tarea, $message)
                : $this->instagramService->publishTaskForUser($user, $tarea, $message);
        }

        $successful = array_keys(array_filter($results, fn ($result) => $result['success'] ?? false));
        $failed = array_keys(array_filter($results, fn ($result) => ! ($result['success'] ?? false)));
        $errors = [];

        foreach ($failed as $platform) {
            $errors[] = ucfirst($platform) . ': ' . ($results[$platform]['error'] ?? 'Error desconocido');
        }

        return [
            'success' => count($successful) === count($platforms) && $platforms !== [],
            'partial' => $successful !== [] && $failed !== [],
            'successful_platforms' => $successful,
            'failed_platforms' => $failed,
            'facebook_post_id' => $results['facebook']['facebook_post_id'] ?? null,
            'instagram_media_id' => $results['instagram']['instagram_media_id'] ?? null,
            'error' => $errors ? implode(' | ', $errors) : null,
            'results' => $results,
        ];
    }
}
