<?php

namespace App\Services;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InstagramService
{
    protected string $apiVersion;

    public function __construct()
    {
        $this->apiVersion = config('facebook.api_version', 'v25.0');
    }

    public function publishTaskForUser(User $user, Tarea $tarea, string $message): array
    {
        $empresaId = $tarea->campania?->suscripcion?->empresa?->id;
        [$instagramId, $accessToken, $error] = $this->resolveCredentials($user, $empresaId);

        if ($error) {
            return $error;
        }

        $media = $tarea->archivos
            ->filter(fn ($archivo) => $archivo->estado === 'aprobado')
            ->map(function ($archivo) {
                $extension = strtolower((string) $archivo->extension);

                if (in_array($extension, ['jpg', 'jpeg'], true)) {
                    return ['type' => 'image', 'url' => $this->publicMediaUrl($archivo->ruta_archivo)];
                }

                if (in_array($extension, ['png', 'gif'], true)) {
                    $jpegPath = $this->convertToJpeg($archivo->ruta_archivo, $extension);

                    return ['type' => 'image', 'url' => $jpegPath ? $this->publicMediaUrl($jpegPath) : null];
                }

                if (in_array($extension, ['mp4', 'mov'], true)) {
                    return ['type' => 'video', 'url' => $this->publicMediaUrl($archivo->ruta_archivo)];
                }

                return null;
            })
            ->filter()
            ->values();

        if ($media->isEmpty()) {
            return $this->failure('Instagram requiere al menos una imagen JPG/JPEG/PNG/GIF o un video MP4/MOV aprobado.', 422);
        }

        if ($media->contains(fn ($item) => empty($item['url']))) {
            return $this->failure('Instagram necesita acceder a los archivos mediante una URL publica HTTPS. Configura APP_URL con el dominio publico del sistema.', 422);
        }

        try {
            $format = strtolower((string) $tarea->tipo_contenido);

            if (str_contains($format, 'historia') || str_contains($format, 'story')) {
                return $this->publishStory($instagramId, $accessToken, $media->first());
            }

            if ($media->count() > 1 || str_contains($format, 'carrusel')) {
                return $this->publishCarousel($instagramId, $accessToken, $message, $media->take(10)->all());
            }

            $item = $media->first();

            if ($item['type'] === 'video' || str_contains($format, 'reel')) {
                return $this->publishReel($instagramId, $accessToken, $message, $item);
            }

            return $this->publishImage($instagramId, $accessToken, $message, $item);
        } catch (\Throwable $e) {
            Log::error('Instagram Service Exception', [
                'instagram_id' => $instagramId,
                'error' => $e->getMessage(),
            ]);

            return $this->failure($e->getMessage(), 500);
        }
    }

    private function publishImage(string $instagramId, string $accessToken, string $message, array $media): array
    {
        $container = $this->createContainer($instagramId, $accessToken, [
            'image_url' => $media['url'],
            'caption' => $message,
        ]);

        return $this->publishContainerResult($instagramId, $accessToken, $container, true);
    }

    private function publishReel(string $instagramId, string $accessToken, string $message, array $media): array
    {
        if ($media['type'] !== 'video') {
            return $this->failure('El formato Reel requiere un archivo de video MP4 o MOV aprobado.', 422);
        }

        $container = $this->createContainer($instagramId, $accessToken, [
            'media_type' => 'REELS',
            'video_url' => $media['url'],
            'caption' => $message,
            'share_to_feed' => 'true',
        ]);

        return $this->publishContainerResult($instagramId, $accessToken, $container, true);
    }

    private function publishStory(string $instagramId, string $accessToken, array $media): array
    {
        $payload = ['media_type' => 'STORIES'];
        $payload[$media['type'] === 'video' ? 'video_url' : 'image_url'] = $media['url'];
        $container = $this->createContainer($instagramId, $accessToken, $payload);

        return $this->publishContainerResult($instagramId, $accessToken, $container, true);
    }

    private function publishCarousel(string $instagramId, string $accessToken, string $message, array $media): array
    {
        if (count($media) < 2) {
            return $this->failure('Un carrusel de Instagram requiere al menos dos archivos JPG/JPEG o MP4/MOV aprobados.', 422);
        }

        $children = [];

        foreach ($media as $item) {
            $payload = ['is_carousel_item' => 'true'];

            if ($item['type'] === 'video') {
                $payload['media_type'] = 'VIDEO';
                $payload['video_url'] = $item['url'];
            } else {
                $payload['image_url'] = $item['url'];
            }

            $container = $this->createContainer($instagramId, $accessToken, $payload);

            if (! $container['success']) {
                return $container;
            }

            $ready = $this->waitUntilReady($container['container_id'], $accessToken);
            if (! $ready['success']) {
                return $ready;
            }

            $children[] = $container['container_id'];
        }

        $container = $this->createContainer($instagramId, $accessToken, [
            'media_type' => 'CAROUSEL',
            'children' => implode(',', $children),
            'caption' => $message,
        ]);

        return $this->publishContainerResult($instagramId, $accessToken, $container, true);
    }

    private function createContainer(string $instagramId, string $accessToken, array $payload): array
    {
        $response = Http::asForm()->timeout(60)->post(
            "https://graph.facebook.com/{$this->apiVersion}/{$instagramId}/media",
            [...$payload, 'access_token' => $accessToken]
        );

        $data = $response->json();

        if (! $response->successful() || empty($data['id'])) {
            return $this->apiFailure($response, 'No se pudo preparar el contenido para Instagram.');
        }

        return ['success' => true, 'container_id' => $data['id'], 'raw' => $data];
    }

    private function publishContainerResult(string $instagramId, string $accessToken, array $container, bool $wait = false): array
    {
        if (! $container['success']) {
            return $container;
        }

        if ($wait) {
            $ready = $this->waitUntilReady($container['container_id'], $accessToken);
            if (! $ready['success']) {
                return $ready;
            }
        }

        $response = Http::asForm()->timeout(60)->post(
            "https://graph.facebook.com/{$this->apiVersion}/{$instagramId}/media_publish",
            [
                'creation_id' => $container['container_id'],
                'access_token' => $accessToken,
            ]
        );

        $data = $response->json();

        if (! $response->successful() || empty($data['id'])) {
            return $this->apiFailure($response, 'Instagram preparo el contenido, pero no pudo publicarlo.');
        }

        return [
            'success' => true,
            'instagram_media_id' => $data['id'],
            'raw' => $data,
        ];
    }

    private function waitUntilReady(string $containerId, string $accessToken): array
    {
        for ($attempt = 0; $attempt < 12; $attempt++) {
            $response = Http::timeout(20)->get(
                "https://graph.facebook.com/{$this->apiVersion}/{$containerId}",
                ['fields' => 'status_code,status', 'access_token' => $accessToken]
            );

            $data = $response->json();
            $status = strtoupper((string) ($data['status_code'] ?? ''));

            if ($response->successful() && $status === 'FINISHED') {
                return ['success' => true];
            }

            if (! $response->successful() || in_array($status, ['ERROR', 'EXPIRED'], true)) {
                return $this->apiFailure($response, $data['status'] ?? 'Instagram no pudo procesar el archivo multimedia.');
            }

            if ($attempt < 11) {
                usleep(1_000_000);
            }
        }

        return $this->failure('Instagram sigue procesando el video. Intenta publicar nuevamente en unos momentos.', 408);
    }

    private function resolveCredentials(User $user, ?int $empresaId): array
    {
        $query = fn () => $user->socialAccounts()->where('provider', 'instagram');
        $account = $query()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId), fn ($q) => $q->whereNull('empresa_id'))
            ->first();

        if (! $account && $empresaId) {
            $account = $query()->whereNull('empresa_id')->first();
        }

        if (! $account) {
            return [null, null, $this->failure('El cliente no tiene una cuenta profesional de Instagram vinculada para publicar.', 422)];
        }

        if (! filled($account->provider_user_id) || ! filled($account->access_token)) {
            return [null, null, $this->failure('La cuenta de Instagram vinculada no tiene un token o identificador valido.', 422)];
        }

        return [$account->provider_user_id, $account->access_token, null];
    }

    private function publicMediaUrl(string $relativePath): ?string
    {
        if (! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        $storageUrl = Storage::disk('public')->url($relativePath);
        $url = filter_var($storageUrl, FILTER_VALIDATE_URL)
            ? $storageUrl
            : rtrim((string) config('app.url'), '/') . '/' . ltrim($storageUrl, '/');
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme !== 'https' || in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return null;
        }

        return $url;
    }

    private function convertToJpeg(string $relativePath, string $extension): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $disk = Storage::disk('public');
        $absolutePath = $disk->path($relativePath);

        if (! is_file($absolutePath)) {
            return null;
        }

        $source = $extension === 'png' ? @imagecreatefrompng($absolutePath) : @imagecreatefromgif($absolutePath);

        if (! $source) {
            return null;
        }

        $canvas = imagecreatetruecolor(imagesx($source), imagesy($source));
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));

        $targetPath = 'instagram/' . sha1($relativePath . '|' . filemtime($absolutePath)) . '.jpg';
        $temporaryPath = tempnam(sys_get_temp_dir(), 'prodovi-instagram-');
        $converted = $temporaryPath && imagejpeg($canvas, $temporaryPath, 92);

        imagedestroy($source);
        imagedestroy($canvas);

        if (! $converted) {
            if ($temporaryPath) {
                @unlink($temporaryPath);
            }
            return null;
        }

        $stored = $disk->put($targetPath, file_get_contents($temporaryPath));
        @unlink($temporaryPath);

        return $stored ? $targetPath : null;
    }

    private function apiFailure(Response $response, string $fallback): array
    {
        $data = $response->json();
        $message = $data['error']['message'] ?? $fallback;

        Log::warning('Instagram API Error', [
            'status' => $response->status(),
            'error' => $message,
        ]);

        return $this->failure($message, $data['error']['code'] ?? $response->status(), $data);
    }

    private function failure(string $message, int $code, ?array $raw = null): array
    {
        return [
            'success' => false,
            'instagram_media_id' => null,
            'error' => $message,
            'code' => $code,
            'raw' => $raw,
        ];
    }
}
