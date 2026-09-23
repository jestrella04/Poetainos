<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Saves a writing together with its cover, categories and tags, and enforces
 * how many writings an author may publish per day.
 */
class WritingPublisher
{
    public const MAX_TAGS = 10;

    public const MAX_TAG_LENGTH = 40;

    private const DEFAULT_DAILY_POST_LIMIT = 3;

    private const COVER_WIDTH = 1280;

    private const COVER_HEIGHT = 720;

    public function __construct(private ImageStorage $images) {}

    /**
     * @throws ValidationException when the author already published today's maximum.
     */
    public function ensureBelowDailyPostLimit(User $author): void
    {
        $postsToday = $author->writings()->where('created_at', '>=', Carbon::today())->count();
        $dailyPostLimit = getSiteConfig('writings.daily_post_limit') ?? self::DEFAULT_DAILY_POST_LIMIT;

        if ($postsToday >= $dailyPostLimit) {
            throw ValidationException::withMessages([
                'title' => __('You have reached your maximum number of posts for today. Please try again tomorrow.'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data  Validated form input: title, text, link, main_category, categories, tags.
     */
    public function create(User $author, array $data, ?UploadedFile $cover): Writing
    {
        $writing = new Writing;
        $writing->author()->associate($author);

        return $this->save($writing, $data, $cover);
    }

    /**
     * @param  array<string, mixed>  $data  Validated form input: title, text, link, main_category, categories, tags.
     */
    public function update(Writing $writing, array $data, ?UploadedFile $cover): Writing
    {
        return $this->save($writing, $data, $cover);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function save(Writing $writing, array $data, ?UploadedFile $upload): Writing
    {
        $currentCover = $writing->extra_info['cover'] ?? '';
        $cover = $upload !== null && $upload->isValid()
            ? $this->images->storeUpload($upload, 'covers', self::COVER_WIDTH, self::COVER_HEIGHT)
            : $currentCover;

        DB::transaction(function () use ($writing, $data, $cover): void {
            $writing->title = $data['title'];

            if ($writing->exists === false) {
                $writing->slug = slugify($writing->getTable(), $writing->title);
            }

            $writing->text = $data['text'];
            $writing->extra_info = [
                ...($writing->extra_info ?? []),
                'link' => $data['link'] ?? '',
                'cover' => $cover,
            ];
            $writing->save();

            $writing->categories()->sync([$data['main_category'], ...(array) $data['categories']]);
            $writing->tags()->sync($this->resolveTagIds((array) ($data['tags'] ?? [])));
        });

        if ($cover !== $currentCover) {
            $this->images->delete($currentCover);
        }

        return $writing;
    }

    /**
     * The ids of the given tag names, creating the tags that don't exist yet.
     *
     * @param  array<int, mixed>  $names
     * @return array<int, int>
     */
    private function resolveTagIds(array $names): array
    {
        return collect($names)
            ->map(fn (mixed $name): string => trim((string) preg_replace('/\s+/', ' ', (string) $name)))
            ->unique()
            ->map(fn (string $name): int => Tag::firstOrCreate(['name' => $name], ['slug' => slugify('tags', $name)])->id)
            ->values()
            ->all();
    }
}
