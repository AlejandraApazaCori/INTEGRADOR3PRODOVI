<?php

namespace App\Services;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FacebookService
{
    protected ?string $token;
    protected ?string $pageId;
    protected string $apiVersion;

    public function __construct()
    {
        $this->token = config('facebook.access_token');
        $this->pageId = config('facebook.page_id');
        $this->apiVersion = config('facebook.api_version', 'v25.0');
    }

    public function postToPage(string $pageId, string $pageAccessToken, string $message): array
    {
        try {
            $response = Http::asForm()
                ->timeout(20)
                ->post(
                    "https://graph.facebook.com/{$this->apiVersion}/{$pageId}/feed",
                    [
                        'message' => $message,
                        'access_token' => $pageAccessToken,
                    ]
                );

            $payload = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'facebook_post_id' => $payload['id'] ?? null,
                    'raw' => $payload,
                    'message' => 'Publicacion creada exitosamente',
                ];
            }

            Log::error('Facebook API Error', [
                'page_id' => $pageId,
                'token' => $this->maskToken($pageAccessToken),
                'status' => $response->status(),
                'error' => $payload['error']['message'] ?? 'Error desconocido',
            ]);

            return [
                'success' => false,
                'facebook_post_id' => null,
                'raw' => $payload,
                'error' => $payload['error']['message'] ?? 'Error desconocido',
                'code' => $payload['error']['code'] ?? $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::error('Facebook Service Exception', [
                'page_id' => $pageId,
                'token' => $this->maskToken($pageAccessToken),
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'facebook_post_id' => null,
                'raw' => null,
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    public function publishForUser(User $user, string $message): array
    {
        [$pageId, $pageAccessToken, $error] = $this->resolveUserPageCredentials($user);

        if ($error) {
            return $error;
        }

        return $this->postToPage($pageId, $pageAccessToken, $message);
    }

    public function publishTaskForUser(User $user, Tarea $tarea, string $message): array
    {
        [$pageId, $pageAccessToken, $error] = $this->resolveUserPageCredentials($user);

        if ($error) {
            return $error;
        }

        $imageFiles = $tarea->archivos
            ->filter(function ($archivo) {
                return $archivo->estado === 'aprobado'
                    && in_array(strtolower($archivo->extension), ['jpg', 'jpeg', 'png', 'gif'], true);
            })
            ->values();

        if ($imageFiles->isEmpty()) {
            return $this->postToPage($pageId, $pageAccessToken, $message);
        }

        if ($imageFiles->count() === 1) {
            return $this->postSingleImageToPage($pageId, $pageAccessToken, $message, $imageFiles->first()->ruta_archivo);
        }

        return $this->postImageCarouselToPage($pageId, $pageAccessToken, $message, $imageFiles->pluck('ruta_archivo')->all());
    }

    public function postToConfiguredPage(string $message): array
    {
        if (! filled($this->pageId) || ! filled($this->token)) {
            return [
                'success' => false,
                'facebook_post_id' => null,
                'error' => 'La configuracion legacy de Facebook no esta completa.',
                'code' => 422,
            ];
        }

        return $this->postToPage($this->pageId, $this->token, $message);
    }

    public function getPageInfo(): array
    {
        if (! filled($this->pageId) || ! filled($this->token)) {
            return ['error' => 'La configuracion legacy de Facebook no esta completa.'];
        }

        try {
            $response = Http::timeout(20)->get(
                "https://graph.facebook.com/{$this->apiVersion}/{$this->pageId}",
                [
                    'fields' => 'id,name,link',
                    'access_token' => $this->token,
                ]
            );

            return $response->json();
        } catch (\Throwable $e) {
            Log::error('Facebook Service Exception', [
                'page_id' => $this->pageId,
                'token' => $this->maskToken($this->token),
                'error' => $e->getMessage(),
            ]);

            return ['error' => $e->getMessage()];
        }
    }

    public function getPageAccessToken(string $pageId): ?string
    {
        if (! filled($this->token)) {
            return null;
        }

        try {
            $response = Http::timeout(20)->get(
                "https://graph.facebook.com/{$this->apiVersion}/{$pageId}",
                [
                    'fields' => 'access_token',
                    'access_token' => $this->token,
                ]
            );

            return $response->json('access_token');
        } catch (\Throwable $e) {
            Log::error('Error getting page token', [
                'page_id' => $pageId,
                'token' => $this->maskToken($this->token),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function resolveUserPageCredentials(User $user): array
    {
        $pageAccount = $user->socialAccounts()
            ->where('provider', 'facebook_page')
            ->first();

        if (! $pageAccount) {
            return [
                null,
                null,
                [
                    'success' => false,
                    'facebook_post_id' => null,
                    'error' => 'El cliente no tiene una pagina de Facebook vinculada para publicar.',
                    'code' => 422,
                ],
            ];
        }

        $pageId = $pageAccount->provider_user_id ?: ($pageAccount->metadata['page_id'] ?? null);
        $pageAccessToken = $pageAccount->access_token;

        if (! filled($pageId) || ! filled($pageAccessToken)) {
            return [
                null,
                null,
                [
                    'success' => false,
                    'facebook_post_id' => null,
                    'error' => 'La pagina de Facebook vinculada no tiene un token o identificador valido.',
                    'code' => 422,
                ],
            ];
        }

        return [$pageId, $pageAccessToken, null];
    }

    private function postSingleImageToPage(string $pageId, string $pageAccessToken, string $message, string $relativePath): array
    {
        $absolutePath = Storage::disk('public')->path($relativePath);

        if (! is_file($absolutePath)) {
            return [
                'success' => false,
                'facebook_post_id' => null,
                'error' => 'No se encontro la imagen aprobada para publicar.',
                'code' => 404,
            ];
        }

        try {
            $response = Http::timeout(60)
                ->attach('source', fopen($absolutePath, 'r'), basename($absolutePath))
                ->post(
                    "https://graph.facebook.com/{$this->apiVersion}/{$pageId}/photos",
                    [
                        'caption' => $message,
                        'access_token' => $pageAccessToken,
                    ]
                );

            $payload = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'facebook_post_id' => $payload['post_id'] ?? $payload['id'] ?? null,
                    'raw' => $payload,
                    'message' => 'Publicacion con imagen creada exitosamente',
                ];
            }

            return [
                'success' => false,
                'facebook_post_id' => null,
                'raw' => $payload,
                'error' => $payload['error']['message'] ?? 'Error desconocido al publicar la imagen.',
                'code' => $payload['error']['code'] ?? $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'facebook_post_id' => null,
                'raw' => null,
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    private function postImageCarouselToPage(string $pageId, string $pageAccessToken, string $message, array $relativePaths): array
    {
        try {
            $attachedMedia = [];

            foreach ($relativePaths as $relativePath) {
                $absolutePath = Storage::disk('public')->path($relativePath);

                if (! is_file($absolutePath)) {
                    continue;
                }

                $uploadResponse = Http::timeout(60)
                    ->attach('source', fopen($absolutePath, 'r'), basename($absolutePath))
                    ->post(
                        "https://graph.facebook.com/{$this->apiVersion}/{$pageId}/photos",
                        [
                            'published' => 'false',
                            'access_token' => $pageAccessToken,
                        ]
                    );

                $uploadPayload = $uploadResponse->json();

                if (! $uploadResponse->successful() || empty($uploadPayload['id'])) {
                    return [
                        'success' => false,
                        'facebook_post_id' => null,
                        'raw' => $uploadPayload,
                        'error' => $uploadPayload['error']['message'] ?? 'No se pudo subir una de las imagenes del carrusel.',
                        'code' => $uploadPayload['error']['code'] ?? $uploadResponse->status(),
                    ];
                }

                $attachedMedia[] = json_encode(['media_fbid' => $uploadPayload['id']]);
            }

            if (empty($attachedMedia)) {
                return [
                    'success' => false,
                    'facebook_post_id' => null,
                    'error' => 'No se encontraron imagenes validas para publicar en el carrusel.',
                    'code' => 422,
                ];
            }

            $payload = [
                'message' => $message,
                'access_token' => $pageAccessToken,
            ];

            foreach ($attachedMedia as $index => $media) {
                $payload["attached_media[{$index}]"] = $media;
            }

            $response = Http::asForm()
                ->timeout(60)
                ->post("https://graph.facebook.com/{$this->apiVersion}/{$pageId}/feed", $payload);

            $feedPayload = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'facebook_post_id' => $feedPayload['id'] ?? null,
                    'raw' => $feedPayload,
                    'message' => 'Carrusel de imagenes publicado exitosamente',
                ];
            }

            return [
                'success' => false,
                'facebook_post_id' => null,
                'raw' => $feedPayload,
                'error' => $feedPayload['error']['message'] ?? 'Error desconocido al publicar el carrusel.',
                'code' => $feedPayload['error']['code'] ?? $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'facebook_post_id' => null,
                'raw' => null,
                'error' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    private function maskToken(?string $token): ?string
    {
        if (! filled($token)) {
            return null;
        }

        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . str_repeat('*', max(strlen($token) - 8, 4)) . substr($token, -4);
    }
}
