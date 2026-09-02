<?php

namespace App\Services;

use App\Models\Campania;
use App\Models\Empresa;
use App\Models\SocialAccount;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaCampaignAnalyticsService
{
    private string $apiVersion;

    private string $timezone;

    private array $errors = [];

    public function __construct()
    {
        $this->apiVersion = config('facebook.api_version', 'v25.0');
        $this->timezone = config('app.timezone', 'UTC');
    }

    public function forCampaign(Campania $campania, int|string $days = 30): array
    {
        $days = $this->normalizePeriod($days);
        $accounts = $this->resolveAccounts($campania);
        $accountStamp = $accounts->map(fn (?SocialAccount $account) => $account?->updated_at?->timestamp ?? 0)->implode('-');

        return Cache::remember(
            "meta-campaign-analytics:v4:{$campania->id}:{$days}:{$accountStamp}",
            now()->addMinutes(15),
            fn () => $this->collect([
                'campaign' => ['id' => $campania->id, 'name' => $campania->nombre],
            ], $accounts->all(), $days)
        );
    }

    public function forCompany(Empresa $empresa, int|string $days = 30): array
    {
        $days = $this->normalizePeriod($days);
        $accounts = $this->resolveCompanyAccounts($empresa);
        $accountStamp = $accounts
            ->map(fn (?SocialAccount $account) => $account?->updated_at?->timestamp ?? 0)
            ->implode('-');

        return Cache::remember(
            "meta-company-analytics:v4:{$empresa->id}:{$days}:{$accountStamp}",
            now()->addMinutes(15),
            fn () => $this->collect([
                'company' => [
                    'id' => $empresa->id,
                    'name' => $empresa->nombre_empresa,
                    'type' => $empresa->tipo_empresa,
                ],
            ], $accounts->all(), $days)
        );
    }

    public function connectedProvidersForCompany(Empresa $empresa): array
    {
        $accounts = $this->companyAccountMap($empresa);

        return collect([
            ($accounts->has('facebook_page') || $accounts->has('facebook')) ? 'facebook' : null,
            $accounts->has('instagram') ? 'instagram' : null,
        ])->filter()->values()->all();
    }

    public function connectedProvidersForCampaign(Campania $campania): array
    {
        $accounts = $this->resolveAccounts($campania);

        return collect([
            $accounts->has('facebook_page') ? 'facebook' : null,
            $accounts->has('instagram') ? 'instagram' : null,
        ])->filter()->values()->all();
    }

    private function collect(array $context, array $accounts, int|string $days): array
    {
        $this->errors = [];
        $until = now($this->timezone)->endOfDay();
        $since = $days === 'all'
            ? Carbon::create(2004, 2, 4, 0, 0, 0, $this->timezone)
            : $until->copy()->subDays($days - 1)->startOfDay();
        // Meta limita varias series de Insights de cuenta a ventanas cortas. Las
        // publicaciones mantienen el periodo solicitado, pero las series se
        // consultan en una ventana segura de hasta 90 días.
        $insightsSince = $since->greaterThan($until->copy()->subDays(89)->startOfDay())
            ? $since->copy()
            : $until->copy()->subDays(89)->startOfDay();
        $labels = collect(CarbonPeriod::create($insightsSince, '1 day', $until))
            ->map(fn (Carbon $date) => $date->format('Y-m-d'))
            ->values()->all();

        $facebook = isset($accounts['facebook_page'])
            ? $this->facebook($accounts['facebook_page'], $since, $until, $labels, $insightsSince)
            : $this->emptyPlatform('facebook');
        $instagram = isset($accounts['instagram'])
            ? $this->instagram($accounts['instagram'], $since, $until, $labels, $insightsSince)
            : $this->emptyPlatform('instagram');

        return [
            ...$context,
            'period' => [
                'days' => $days,
                'since' => $since->toDateString(),
                'until' => $until->toDateString(),
                'timezone' => $this->timezone,
                'granularity' => 'day',
                'insights_since' => $insightsSince->toDateString(),
                'insights_limited' => $insightsSince->greaterThan($since),
            ],
            'generated_at' => now($this->timezone)->toIso8601String(),
            'platforms' => compact('facebook', 'instagram'),
            'summary' => $this->summary($facebook, $instagram, $labels),
            'errors' => $this->errors,
        ];
    }

    private function normalizePeriod(int|string $period): int|string
    {
        if ($period === 'all') {
            return 'all';
        }

        $days = (int) $period;

        return in_array($days, [7, 30, 90, 365, 730], true) ? $days : 30;
    }

    private function facebook(SocialAccount $account, Carbon $since, Carbon $until, array $labels, Carbon $insightsSince): array
    {
        $pageId = $account->provider_user_id ?: data_get($account->metadata, 'page_id');
        $token = $account->access_token;
        $platform = $this->emptyPlatform('facebook', true, $account);

        if (! filled($pageId) || ! filled($token)) {
            $this->addError('facebook', 'credentials', 'La página vinculada no tiene credenciales válidas.');

            return $platform;
        }

        $profile = $this->get("{$pageId}", [
            'fields' => 'id,name,followers_count,fan_count,picture.type(large)',
            'access_token' => $token,
        ], 'facebook', 'profile');

        if ($profile) {
            $platform['account'] = [
                'id' => $pageId,
                'name' => $profile['name'] ?? $account->display_name ?? $account->username,
                'username' => $account->username,
                'avatar' => data_get($profile, 'picture.data.url') ?: $account->avatar,
            ];
            $platform['totals']['followers'] = $this->number($profile['followers_count'] ?? $profile['fan_count'] ?? null);
        }

        $sinceUnix = $insightsSince->copy()->utc()->timestamp;
        $untilUnix = $until->copy()->utc()->timestamp;
        $dailyFollowers = $this->firstInsight($pageId, $token, ['page_daily_follows', 'page_fan_adds'], 'day', $sinceUnix, $untilUnix, 'facebook');
        $dailyUnfollows = $this->firstInsight($pageId, $token, ['page_daily_unfollows', 'page_fan_removes'], 'day', $sinceUnix, $untilUnix, 'facebook');
        $historicalFollowers = $this->firstInsight($pageId, $token, ['page_follows', 'page_fans'], 'day', $sinceUnix, $untilUnix, 'facebook');

        $platform['followers'] = [
            'labels' => $labels,
            'values' => $historicalFollowers
                ? $this->alignInsightValues($historicalFollowers, $labels)
                : $this->reconstructFollowers($platform['totals']['followers'], $dailyFollowers, $dailyUnfollows, $labels),
        ];

        $reach = $this->firstInsight($pageId, $token, ['page_total_media_view_unique', 'page_media_viewers', 'page_impressions_unique'], 'day', $sinceUnix, $untilUnix, 'facebook');
        $views = $this->firstInsight($pageId, $token, ['page_media_view', 'page_media_views', 'page_impressions'], 'day', $sinceUnix, $untilUnix, 'facebook');
        $clicks = $this->firstInsight($pageId, $token, ['page_total_actions'], 'day', $sinceUnix, $untilUnix, 'facebook');
        $platform['totals']['reach'] = $this->sumInsight($reach);
        $platform['totals']['views'] = $this->sumInsight($views);
        $platform['totals']['clicks'] = $this->sumInsight($clicks);

        $postsPayload = $this->facebookPublishedPosts($pageId, $token, $since->copy()->utc()->timestamp, $untilUnix);

        $facebookPosts = collect($postsPayload['data'] ?? [])->values();
        $postInsights = $this->facebookPostInsights($facebookPosts->all(), $token);
        $platform['posts'] = $facebookPosts->map(function (array $post, int $index) use ($postInsights) {
            $insights = $postInsights[$index] ?? [];
            $reactions = $this->number(data_get($post, 'reactions.summary.total_count')) ?? 0;
            $comments = $this->number(data_get($post, 'comments.summary.total_count')) ?? 0;
            $shares = $this->number(data_get($post, 'shares.count')) ?? 0;
            $reach = $this->number($insights['post_total_media_view_unique'] ?? null);
            $views = $this->number($insights['post_media_view'] ?? $insights['post_media_views'] ?? null);
            $clicks = $this->number($insights['post_clicks'] ?? null);
            if ($clicks === null && is_array($insights['post_clicks_by_type'] ?? null)) {
                $clicks = (float) collect($insights['post_clicks_by_type'])->sum();
            }

            return [
                'id' => $post['id'] ?? null,
                'platform' => 'facebook',
                'caption' => trim((string) ($post['message'] ?? 'Publicación sin texto')),
                'timestamp' => $post['created_time'] ?? null,
                'permalink' => $post['permalink_url'] ?? null,
                'thumbnail' => $post['full_picture'] ?? null,
                'type' => strtoupper((string) (data_get($post, 'attachments.data.0.media_type') ?: data_get($post, 'attachments.data.0.type') ?: 'POST')),
                'reactions' => $reactions,
                'likes' => $reactions,
                'comments' => $comments,
                'shares' => $shares,
                'saves' => null,
                'clicks' => $clicks,
                'reach' => $reach,
                'views' => $views,
                'engagement' => $reactions + $comments + $shares,
            ];
        })->filter(fn (array $post) => filled($post['id']) && filled($post['timestamp']))->values()->all();

        $platform = $this->finishPlatform($platform);
        $platform['audience_status'] = [
            'available' => false,
            'reason' => 'unsupported_by_meta',
            'has_data' => false,
        ];

        return $platform;
    }

    private function facebookPublishedPosts(string $pageId, string $token, int $since, int $until): ?array
    {
        $baseParams = [
            'since' => $since,
            'until' => $until,
            'limit' => 100,
            'access_token' => $token,
        ];
        $baseFields = 'id,message,created_time,permalink_url,full_picture,attachments.limit(1){media_type,type}';

        $payload = $this->getPaginated("{$pageId}/published_posts", [
            ...$baseParams,
            'fields' => $baseFields.',reactions.limit(0).summary(true),comments.limit(0).summary(true),shares',
        ], 'facebook', 'published_posts', false);

        if ($payload !== null) {
            return $payload;
        }

        // Los comentarios y reacciones pueden requerir permisos adicionales. En ese
        // caso conservamos las publicaciones y obtenemos sus Insights por separado.
        $payload = $this->getPaginated("{$pageId}/published_posts", [
            ...$baseParams,
            'fields' => $baseFields,
        ], 'facebook', 'published_posts_basic', false);

        if ($payload !== null) {
            return $payload;
        }

        // Compatibilidad con páginas/versiones antiguas de Graph API.
        return $this->getPaginated("{$pageId}/posts", [
            ...$baseParams,
            'fields' => $baseFields,
        ], 'facebook', 'posts');
    }

    private function facebookPostInsights(array $posts, string $token): array
    {
        if ($posts === []) {
            return [];
        }

        $results = [];
        foreach (array_chunk(array_slice($posts, 0, 200), 50) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk, $token) {
                return collect($chunk)->map(fn (array $post, int $index) => $pool
                    ->as((string) $index)
                    ->timeout(25)
                    ->get($this->graphUrl(($post['id'] ?? '').'/insights'), [
                        'metric' => 'post_total_media_view_unique,post_media_view,post_clicks,post_clicks_by_type',
                        'access_token' => $token,
                    ]))->all();
            });

            $chunkResults = collect($responses)->map(function (Response $response, int|string $index) use ($chunk) {
                if (! $response->successful()) {
                    $this->recordApiError('facebook', 'post_insights:'.($chunk[(int) $index]['id'] ?? $index), $response, false);

                    return [];
                }

                return $this->metricMap($response->json('data', []));
            })->values()->all();
            $results = array_merge($results, $chunkResults);
        }

        return $results;
    }

    private function instagram(SocialAccount $account, Carbon $since, Carbon $until, array $labels, Carbon $insightsSince): array
    {
        $instagramId = $account->provider_user_id;
        $token = $account->access_token;
        $platform = $this->emptyPlatform('instagram', true, $account);

        if (! filled($instagramId) || ! filled($token)) {
            $this->addError('instagram', 'credentials', 'La cuenta vinculada no tiene credenciales válidas.');

            return $platform;
        }

        $profile = $this->get("{$instagramId}", [
            'fields' => 'id,username,name,profile_picture_url,followers_count,media_count',
            'access_token' => $token,
        ], 'instagram', 'profile');

        if ($profile) {
            $platform['account'] = [
                'id' => $instagramId,
                'name' => $profile['name'] ?? $profile['username'] ?? $account->display_name,
                'username' => $profile['username'] ?? $account->username,
                'avatar' => $profile['profile_picture_url'] ?? $account->avatar,
            ];
            $platform['totals']['followers'] = $this->number($profile['followers_count'] ?? null);
        }

        $sinceUnix = $insightsSince->copy()->utc()->timestamp;
        $untilUnix = $until->copy()->utc()->timestamp;
        $followers = $this->insight($instagramId, $token, 'follower_count', 'day', $sinceUnix, $untilUnix, 'instagram');
        $reach = $this->insight($instagramId, $token, 'reach', 'day', $sinceUnix, $untilUnix, 'instagram');
        $views = $this->firstInsight($instagramId, $token, ['views', 'impressions'], 'day', $sinceUnix, $untilUnix, 'instagram');
        $clicks = $this->firstInsight($instagramId, $token, ['profile_links_taps', 'website_clicks'], 'day', $sinceUnix, $untilUnix, 'instagram');

        $platform['followers'] = [
            'labels' => $labels,
            'values' => $this->reconstructFollowers($platform['totals']['followers'], $followers, null, $labels),
        ];
        $platform['totals']['reach'] = $this->sumInsight($reach);
        $platform['totals']['views'] = $this->sumInsight($views);
        $platform['totals']['clicks'] = $this->sumInsight($clicks);

        $mediaPayload = $this->getPaginated("{$instagramId}/media", [
            'fields' => 'id,caption,media_type,media_product_type,timestamp,permalink,thumbnail_url,media_url,like_count,comments_count',
            'since' => $since->copy()->utc()->timestamp,
            'until' => $untilUnix,
            'limit' => 100,
            'access_token' => $token,
        ], 'instagram', 'media');
        $media = collect($mediaPayload['data'] ?? [])
            ->filter(function (array $item) use ($since, $until) {
                if (! filled($item['timestamp'] ?? null)) {
                    return false;
                }
                $publishedAt = Carbon::parse($item['timestamp'])->setTimezone($this->timezone);

                return $publishedAt->betweenIncluded($since, $until);
            })
            ->values();
        $insights = $this->instagramMediaInsights($media->all(), $token);

        $platform['posts'] = $media->map(function (array $item, int $index) use ($insights) {
            $metrics = $insights[$index] ?? [];
            $likes = $this->number($metrics['likes'] ?? $item['like_count'] ?? null) ?? 0;
            $comments = $this->number($metrics['comments'] ?? $item['comments_count'] ?? null) ?? 0;
            $shares = $this->number($metrics['shares'] ?? null);
            $saves = $this->number($metrics['saved'] ?? null);
            $clicks = $this->number($metrics['profile_activity'] ?? null);
            $reach = $this->number($metrics['reach'] ?? null);
            $views = $this->number($metrics['views'] ?? $metrics['plays'] ?? null);
            $engagement = $this->number($metrics['total_interactions'] ?? null)
                ?? ($likes + $comments + ($shares ?? 0) + ($saves ?? 0));

            return [
                'id' => $item['id'] ?? null,
                'platform' => 'instagram',
                'caption' => trim((string) ($item['caption'] ?? 'Publicación sin texto')),
                'timestamp' => $item['timestamp'] ?? null,
                'permalink' => $item['permalink'] ?? null,
                'thumbnail' => $item['thumbnail_url'] ?? $item['media_url'] ?? null,
                'type' => strtoupper((string) ($item['media_product_type'] ?? $item['media_type'] ?? 'POST')),
                'reactions' => null,
                'likes' => $likes,
                'comments' => $comments,
                'shares' => $shares,
                'saves' => $saves,
                'clicks' => $clicks,
                'reach' => $reach,
                'views' => $views,
                'engagement' => $engagement,
                'video_watch_time' => $this->number($metrics['ig_reels_video_view_total_time'] ?? null),
            ];
        })->filter(fn (array $post) => filled($post['id']) && filled($post['timestamp']))->values()->all();

        $platform = $this->finishPlatform($platform);
        $platform['audience'] = $this->instagramAudience($instagramId, $token);
        $instagramScopes = collect(data_get($account->metadata, 'granted_scopes', []));
        $platform['audience_status'] = [
            'followers' => $platform['totals']['followers'],
            'minimum_followers' => 100,
            'permission' => $instagramScopes->isEmpty() ? null : $instagramScopes->contains('instagram_manage_insights'),
            'has_data' => collect($platform['audience'])->contains(fn (array $items) => $items !== []),
        ];

        return $platform;
    }

    private function instagramMediaInsights(array $media, string $token): array
    {
        if ($media === []) {
            return [];
        }

        $results = [];
        foreach (array_chunk(array_slice($media, 0, 200), 50) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk, $token) {
                return collect($chunk)->map(function (array $item, int $index) use ($pool, $token) {
                    $product = strtoupper((string) ($item['media_product_type'] ?? $item['media_type'] ?? ''));
                    $metrics = $product === 'STORY'
                        ? 'reach,views,shares,replies'
                        : 'reach,views,total_interactions,likes,comments,shares,saved';

                    return $pool->as((string) $index)->timeout(25)->get(
                        $this->graphUrl(($item['id'] ?? '').'/insights'),
                        ['metric' => $metrics, 'access_token' => $token]
                    );
                })->all();
            });

            $chunkResults = collect($responses)->map(function (Response $response, int|string $index) use ($chunk) {
                if (! $response->successful()) {
                    $this->recordApiError('instagram', 'media_insights:'.($chunk[(int) $index]['id'] ?? $index), $response, false);

                    return [];
                }

                return $this->metricMap($response->json('data', []));
            })->values()->all();
            $results = array_merge($results, $chunkResults);
        }

        return $results;
    }

    private function instagramAudience(string $instagramId, string $token): array
    {
        $age = $this->instagramDemographic($instagramId, $token, 'age');
        $gender = $this->instagramDemographic($instagramId, $token, 'gender');

        return [
            'age_gender' => collect($age)->map(fn (array $item) => [...$item, 'name' => 'Edad '.$item['name']])
                ->concat(collect($gender)->map(fn (array $item) => [...$item, 'name' => 'Sexo '.$item['name']]))
                ->values()->all(),
            'cities' => $this->instagramDemographic($instagramId, $token, 'city'),
            'countries' => $this->instagramDemographic($instagramId, $token, 'country'),
        ];
    }

    private function instagramDemographic(string $instagramId, string $token, string $breakdown): array
    {
        $results = [];
        foreach (['follower_demographics', 'engaged_audience_demographics'] as $metric) {
            $payload = $this->get("{$instagramId}/insights", [
                'metric' => $metric,
                'period' => 'lifetime',
                'metric_type' => 'total_value',
                'breakdown' => $breakdown,
                'timeframe' => 'last_90_days',
                'access_token' => $token,
            ], 'instagram', 'audience_'.$breakdown.'_'.$metric, $metric === 'follower_demographics');
            $results = data_get($payload, 'data.0.total_value.breakdowns.0.results', []);

            if (is_array($results) && $results !== []) {
                break;
            }
        }

        return collect(is_array($results) ? $results : [])->map(function (array $result) {
            $dimensions = $result['dimension_values'] ?? [];

            return [
                'name' => $dimensions[0] ?? 'Sin identificar',
                'value' => $this->number($result['value'] ?? null) ?? 0,
            ];
        })->filter(fn (array $item) => $item['value'] > 0)->sortByDesc('value')->values()->all();
    }

    private function insight(string $objectId, string $token, string $metric, string $period, ?int $since, ?int $until, string $platform, bool $recordError = false): ?array
    {
        $params = ['metric' => $metric, 'period' => $period, 'access_token' => $token];
        if ($since) {
            $params['since'] = $since;
        }
        if ($until) {
            $params['until'] = $until;
        }

        $payload = $this->get("{$objectId}/insights", $params, $platform, 'insight_'.$metric, $recordError);

        return is_array(data_get($payload, 'data.0')) ? data_get($payload, 'data.0') : null;
    }

    private function firstInsight(string $objectId, string $token, array $metrics, string $period, ?int $since, ?int $until, string $platform): ?array
    {
        foreach ($metrics as $metric) {
            $insight = $this->insight($objectId, $token, $metric, $period, $since, $until, $platform, false);
            if ($insight && (
                ! empty($insight['values'])
                || data_get($insight, 'total_value.value') !== null
                || ! empty(data_get($insight, 'total_value.breakdowns'))
            )) {
                return $insight;
            }
        }

        return null;
    }

    private function finishPlatform(array $platform): array
    {
        $posts = collect($platform['posts']);

        foreach (['reach', 'views', 'clicks'] as $metric) {
            if ($platform['totals'][$metric] === null && $posts->contains(fn (array $post) => $post[$metric] !== null)) {
                $platform['totals'][$metric] = $posts->sum(fn (array $post) => $post[$metric] ?? 0);
            }
        }

        $platform['totals']['posts'] = $posts->count();
        $platform['totals']['engagement'] = $posts->sum('engagement');
        $platform['totals']['average_engagement'] = $posts->isNotEmpty()
            ? round($posts->avg('engagement'), 2)
            : null;
        $platform['engagement'] = [
            'reactions' => $posts->sum(fn (array $post) => $post['reactions'] ?? $post['likes'] ?? 0),
            'comments' => $posts->sum(fn (array $post) => $post['comments'] ?? 0),
            'shares' => $posts->contains(fn (array $post) => $post['shares'] !== null) ? $posts->sum('shares') : null,
            'saves' => $posts->contains(fn (array $post) => $post['saves'] !== null) ? $posts->sum('saves') : null,
            'clicks' => $posts->contains(fn (array $post) => $post['clicks'] !== null) ? $posts->sum('clicks') : $platform['totals']['clicks'],
        ];
        $platform['best_posting_times'] = $this->bestPostingTimes($platform['posts']);
        $platform['top_posts'] = $posts->sortByDesc(fn (array $post) => $this->postScore($post))->take(5)->values()->all();
        $platform['content_types'] = $posts->groupBy('type')->map(fn ($group, $type) => [
            'type' => $type,
            'posts' => $group->count(),
            'engagement' => $group->sum('engagement'),
            'average_engagement' => round($group->avg('engagement'), 2),
        ])->sortByDesc('engagement')->values()->all();

        return $platform;
    }

    private function summary(array $facebook, array $instagram, array $labels): array
    {
        $platforms = collect([$facebook, $instagram])->filter(fn (array $item) => $item['connected']);
        $posts = $platforms->flatMap(fn (array $item) => $item['posts'])->values()->all();

        return [
            'totals' => [
                'followers' => $this->nullableSum($platforms->pluck('totals.followers')->all()),
                'reach' => $this->nullableSum($platforms->pluck('totals.reach')->all()),
                'views' => $this->nullableSum($platforms->pluck('totals.views')->all()),
                'engagement' => $this->nullableSum($platforms->pluck('totals.engagement')->all()),
                'clicks' => $this->nullableSum($platforms->pluck('totals.clicks')->all()),
                'posts' => count($posts),
                'average_engagement' => $posts !== [] ? round(collect($posts)->avg('engagement'), 2) : null,
            ],
            'followers' => [
                'labels' => $labels,
                'facebook' => $facebook['followers']['values'],
                'instagram' => $instagram['followers']['values'],
            ],
            'engagement' => [
                'facebook' => $facebook['engagement'],
                'instagram' => $instagram['engagement'],
            ],
            'best_posting_times' => $this->bestPostingTimes($posts),
            'top_posts' => collect($posts)->sortByDesc(fn (array $post) => $this->postScore($post))->take(5)->values()->all(),
            'audience' => [
                'age_gender' => $this->mergeBreakdowns($facebook['audience']['age_gender'], $instagram['audience']['age_gender']),
                'cities' => $this->mergeBreakdowns($facebook['audience']['cities'], $instagram['audience']['cities']),
                'countries' => $this->mergeBreakdowns($facebook['audience']['countries'], $instagram['audience']['countries']),
            ],
            'audience_status' => [
                'facebook' => $facebook['audience_status'] ?? null,
                'instagram' => $instagram['audience_status'] ?? null,
            ],
            'content_types' => $this->mergeContentTypes($facebook['content_types'], $instagram['content_types']),
        ];
    }

    private function bestPostingTimes(array $posts): array
    {
        $prepared = collect($posts)->filter(fn (array $post) => filled($post['timestamp']))->map(function (array $post) {
            $date = Carbon::parse($post['timestamp'])->setTimezone($this->timezone);

            return [
                'day' => $date->dayOfWeekIso,
                'hour' => $date->hour,
                'score' => $this->postScore($post),
            ];
        });
        $globalAverage = $prepared->isNotEmpty() ? (float) $prepared->avg('score') : 0.0;
        $dayNames = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $slots = $prepared->groupBy(fn (array $item) => $item['day'].'-'.$item['hour'])->map(function ($group) use ($globalAverage, $dayNames) {
            $first = $group->first();
            $samples = $group->count();
            $average = (float) $group->avg('score');

            return [
                'day' => $first['day'],
                'day_name' => $dayNames[$first['day']],
                'hour' => $first['hour'],
                'label' => $dayNames[$first['day']].' '.str_pad((string) $first['hour'], 2, '0', STR_PAD_LEFT).':00',
                'samples' => $samples,
                'average_score' => round($average, 2),
                'adjusted_score' => round((($average * $samples) + ($globalAverage * 3)) / ($samples + 3), 2),
            ];
        })->values();
        $eligible = $slots->where('samples', '>=', 2)->sortByDesc('adjusted_score')->values();

        return [
            'sufficient_data' => $eligible->isNotEmpty(),
            'minimum_samples' => 2,
            'best' => $eligible->take(5)->all(),
            'slots' => $slots->all(),
        ];
    }

    private function postScore(array $post): float
    {
        $interactions = ($post['likes'] ?? $post['reactions'] ?? 0)
            + (($post['comments'] ?? 0) * 2)
            + (($post['shares'] ?? 0) * 3)
            + (($post['saves'] ?? 0) * 3)
            + ($post['clicks'] ?? 0);
        $denominator = $post['reach'] ?? $post['views'] ?? null;

        return $denominator > 0 ? round(($interactions / $denominator) * 100, 4) : (float) $interactions;
    }

    private function emptyPlatform(string $platform, bool $connected = false, ?SocialAccount $account = null): array
    {
        return [
            'platform' => $platform,
            'connected' => $connected,
            'account' => $account ? [
                'id' => $account->provider_user_id,
                'name' => $account->display_name ?? $account->username,
                'username' => $account->username,
                'avatar' => $account->avatar,
            ] : null,
            'totals' => ['followers' => null, 'reach' => null, 'views' => null, 'engagement' => null, 'clicks' => null, 'posts' => 0, 'average_engagement' => null],
            'followers' => ['labels' => [], 'values' => []],
            'engagement' => ['reactions' => null, 'comments' => null, 'shares' => null, 'saves' => null, 'clicks' => null],
            'audience' => ['age_gender' => [], 'cities' => [], 'countries' => []],
            'audience_status' => null,
            'posts' => [],
            'top_posts' => [],
            'content_types' => [],
            'best_posting_times' => ['sufficient_data' => false, 'minimum_samples' => 2, 'best' => [], 'slots' => []],
        ];
    }

    private function resolveAccounts(Campania $campania)
    {
        $campania->loadMissing(['suscripcion.empresa.socialAccounts', 'cliente.socialAccounts', 'empresas.socialAccounts']);
        $empresa = $campania->suscripcion?->empresa ?? $campania->empresas->first();
        $accounts = $empresa?->socialAccounts?->keyBy('provider') ?? collect();

        if ($accounts->isEmpty() && $campania->cliente) {
            $accounts = $campania->cliente->socialAccounts()
                ->whereNull('empresa_id')
                ->whereIn('provider', ['facebook', 'facebook_page', 'instagram'])
                ->get()->keyBy('provider');
        }

        $facebookPage = $accounts->get('facebook_page')
            ?? $this->facebookPageFromProfile($accounts->get('facebook'));

        return collect([
            'facebook_page' => $facebookPage,
            'instagram' => $accounts->get('instagram'),
        ])->filter();
    }

    private function resolveCompanyAccounts(Empresa $empresa)
    {
        $accounts = $this->companyAccountMap($empresa);
        $facebookPage = $accounts->get('facebook_page')
            ?? $this->facebookPageFromProfile($accounts->get('facebook'));

        return collect([
            'facebook_page' => $facebookPage,
            'instagram' => $accounts->get('instagram'),
        ])->filter();
    }

    private function companyAccountMap(Empresa $empresa)
    {
        $empresa->loadMissing(['socialAccounts', 'usuario.empresas']);
        $accounts = $empresa->socialAccounts->keyBy('provider');
        $firstCompanyId = $empresa->usuario?->empresas->min('id');

        if ((int) $firstCompanyId === (int) $empresa->id && $empresa->usuario) {
            $legacyAccounts = $empresa->usuario->socialAccounts()
                ->whereNull('empresa_id')
                ->get()
                ->keyBy('provider');

            $accounts = $accounts->union($legacyAccounts);
        }

        return $accounts;
    }

    private function facebookPageFromProfile(?SocialAccount $facebook): ?SocialAccount
    {
        if (! $facebook) {
            return null;
        }

        $pages = collect(data_get($facebook->metadata, 'pages', []));
        $page = $pages->first(fn ($item) => filled(data_get($item, 'id')) && filled(data_get($item, 'access_token')));
        $pageId = data_get($page, 'id') ?: data_get($facebook->metadata, 'page_id');
        $pageToken = data_get($page, 'access_token') ?: data_get($facebook->metadata, 'page_access_token');

        if (! filled($pageId) || ! filled($pageToken)) {
            return null;
        }

        $pageAccount = $facebook->replicate();
        $pageAccount->provider = 'facebook_page';
        $pageAccount->provider_user_id = (string) $pageId;
        $pageAccount->display_name = data_get($page, 'name') ?: $facebook->display_name;
        $pageAccount->access_token = $pageToken;
        $pageAccount->metadata = array_merge($facebook->metadata ?? [], [
            'page_id' => (string) $pageId,
            'page_name' => data_get($page, 'name'),
        ]);

        return $pageAccount;
    }

    private function get(string $path, array $params, string $platform, string $scope, bool $recordError = true): ?array
    {
        try {
            $response = Http::timeout(30)->retry(2, 250)->get($this->graphUrl($path), $params);
            if (! $response->successful()) {
                $this->recordApiError($platform, $scope, $response, $recordError);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            $context = [
                'platform' => $platform,
                'scope' => $scope,
                'error' => $e->getMessage(),
            ];
            $recordError
                ? Log::warning('Meta Analytics request exception.', $context)
                : Log::debug('Meta Analytics optional request unavailable.', $context);
            if ($recordError) {
                $this->addError($platform, $scope, $e->getMessage());
            }

            return null;
        }
    }

    private function getPaginated(string $path, array $params, string $platform, string $scope, bool $recordError = true): ?array
    {
        $items = [];
        $next = $this->graphUrl($path);
        $page = 0;

        try {
            while ($next && $page < 25) {
                $response = Http::timeout(30)->retry(2, 250)->get($next, $page === 0 ? $params : []);

                if (! $response->successful()) {
                    $this->recordApiError($platform, $scope, $response, $recordError);

                    return $page === 0 ? null : ['data' => $items];
                }

                $payload = $response->json();
                $items = array_merge($items, is_array($payload['data'] ?? null) ? $payload['data'] : []);
                $next = data_get($payload, 'paging.next');
                $page++;

                if ($next && parse_url($next, PHP_URL_HOST) !== 'graph.facebook.com') {
                    Log::warning('Meta Analytics pagination returned an unexpected host.', compact('platform', 'scope'));
                    $next = null;
                }
            }

            if ($next) {
                $this->addError($platform, $scope, 'Meta devolvió más de 2.500 registros. Se muestran los 2.500 más recientes.');
            }

            return ['data' => $items];
        } catch (\Throwable $e) {
            Log::warning('Meta Analytics paginated request exception.', [
                'platform' => $platform,
                'scope' => $scope,
                'error' => $e->getMessage(),
            ]);
            if ($recordError) {
                $this->addError($platform, $scope, $e->getMessage());
            }

            return $page === 0 ? null : ['data' => $items];
        }
    }

    private function recordApiError(string $platform, string $scope, Response $response, bool $recordError): void
    {
        $message = $response->json('error.message') ?? 'Meta no devolvió datos para esta consulta.';
        Log::warning('Meta Analytics API error.', [
            'platform' => $platform,
            'scope' => $scope,
            'status' => $response->status(),
            'code' => $response->json('error.code'),
            'error' => $message,
        ]);
        if ($recordError) {
            $this->addError($platform, $scope, $message);
        }
    }

    private function addError(string $platform, string $scope, string $message): void
    {
        $this->errors[] = compact('platform', 'scope', 'message');
    }

    private function graphUrl(string $path): string
    {
        return 'https://graph.facebook.com/'.$this->apiVersion.'/'.ltrim($path, '/');
    }

    private function metricMap(array $metrics): array
    {
        return collect($metrics)->mapWithKeys(function (array $metric) {
            $value = data_get($metric, 'total_value.value', data_get($metric, 'values.0.value'));

            return isset($metric['name']) ? [$metric['name'] => $value] : [];
        })->all();
    }

    private function alignInsightValues(array $insight, array $labels): array
    {
        $format = isset($labels[0]) && strlen($labels[0]) === 7 ? 'Y-m' : 'Y-m-d';
        $values = collect($insight['values'] ?? [])->mapWithKeys(function (array $item) use ($format) {
            $date = isset($item['end_time']) ? Carbon::parse($item['end_time'])->setTimezone($this->timezone)->format($format) : null;

            return $date ? [$date => $this->number($item['value'] ?? null)] : [];
        });

        return collect($labels)->map(fn (string $date) => $values->get($date))->all();
    }

    private function reconstructFollowers(?float $current, ?array $adds, ?array $removes, array $labels): array
    {
        if ($current === null) {
            return [];
        }
        if ($adds === null && $removes === null) {
            $series = array_fill(0, count($labels), null);
            if ($series !== []) {
                $series[array_key_last($series)] = $current;
            }

            return $series;
        }
        $addMap = $this->insightDateMap($adds);
        $removeMap = $this->insightDateMap($removes);
        $running = $current;
        $series = [];
        $monthly = isset($labels[0]) && strlen($labels[0]) === 7;
        foreach (array_reverse($labels) as $date) {
            $series[$date] = $running;
            $addsForPeriod = $monthly
                ? collect($addMap)->filter(fn ($value, $key) => str_starts_with($key, $date))->sum()
                : ($addMap[$date] ?? 0);
            $removesForPeriod = $monthly
                ? collect($removeMap)->filter(fn ($value, $key) => str_starts_with($key, $date))->sum()
                : ($removeMap[$date] ?? 0);
            $running -= $addsForPeriod - $removesForPeriod;
        }

        return collect($labels)->map(fn (string $date) => $series[$date] ?? null)->all();
    }

    private function insightDateMap(?array $insight): array
    {
        return collect($insight['values'] ?? [])->mapWithKeys(function (array $item) {
            $date = isset($item['end_time']) ? Carbon::parse($item['end_time'])->setTimezone($this->timezone)->format('Y-m-d') : null;

            return $date ? [$date => $this->number($item['value'] ?? null) ?? 0] : [];
        })->all();
    }

    private function sumInsight(?array $insight): ?float
    {
        if (! $insight) {
            return null;
        }
        $totalValue = $this->number(data_get($insight, 'total_value.value'));
        if ($totalValue !== null) {
            return $totalValue;
        }
        if (empty($insight['values'])) {
            return null;
        }
        $values = collect($insight['values'])->map(fn (array $item) => $this->number($item['value'] ?? null))->filter(fn ($value) => $value !== null);

        return $values->isEmpty() ? null : $values->sum();
    }

    private function mergeBreakdowns(array ...$groups): array
    {
        return collect($groups)->flatten(1)->groupBy('name')->map(fn ($items, $name) => [
            'name' => $name,
            'value' => $items->sum('value'),
        ])->sortByDesc('value')->take(10)->values()->all();
    }

    private function mergeContentTypes(array ...$groups): array
    {
        return collect($groups)->flatten(1)->groupBy('type')->map(fn ($items, $type) => [
            'type' => $type,
            'posts' => $items->sum('posts'),
            'engagement' => $items->sum('engagement'),
            'average_engagement' => $items->sum('posts') > 0 ? round($items->sum('engagement') / $items->sum('posts'), 2) : null,
        ])->sortByDesc('engagement')->values()->all();
    }

    private function nullableSum(array $values): ?float
    {
        $available = collect($values)->filter(fn ($value) => $value !== null);

        return $available->isEmpty() ? null : $available->sum();
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
