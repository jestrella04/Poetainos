<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DailySelection;
use App\Models\Like;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingPublished;
use App\Services\ImageStorage;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WritingsController extends Controller
{
    private const DEFAULT_DAILY_POST_LIMIT = 3;

    private const HOME_AUTHORS_SHOWN = 4;

    private const HOME_TAGS_SHOWN = 6;

    private const LIKERS_SHOWN = 5;

    private const RELATED_SHOWN = 5;

    private const MAX_TAGS = 10;

    private const MAX_TAG_LENGTH = 40;

    private const COVER_WIDTH = 1280;

    private const COVER_HEIGHT = 720;

    /**
     * Display a listing of the resource.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function index(): Response|Paginator
    {
        $routeName = request()->route()?->getName();
        $isAwardsListing = $routeName === 'writings.awards';
        $isHome = $routeName === 'home';
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $writings = Writing::visibleTo($this->getBlockedUsers())
            ->withListingRelations()
            ->sorted($sort);

        if ($isAwardsListing === true) {
            $writings->whereNotNull('home_posted_at');
        }

        return $this->writingsIndex(
            $writings,
            $sort,
            [
                'title' => $isAwardsListing ? getPageTitle([__('Golden Flowers')]) : getPageTitle([]),
                'canonical' => $isAwardsListing ? route('writings.awards') : route('home'),
            ],
            [
                'isHome' => $isHome,
                'pickOfTheDay' => $isHome ? DailySelection::current()?->visibleWriting($this->getBlockedUsers()) : null,
                'authors' => $isHome ? User::select(
                    'id',
                    'username',
                    'name',
                    'karma',
                    'extra_info->avatar AS avatar',
                )->withCount('writings')
                    ->ranked()
                    ->take(self::HOME_AUTHORS_SHOWN)
                    ->get() : null,
                'tags' => $isHome ? Tag::withCount('writings')
                    ->orderByDesc('writings_count')
                    ->has('writings')
                    ->take(self::HOME_TAGS_SHOWN)
                    ->get() : null,
            ],
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return $this->edit(new Writing);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return array<string, string>
     */
    public function store(Request $request, ImageStorage $images): array
    {
        return $this->update($request, new Writing, $images);
    }

    /**
     * Display the specified resource.
     */
    public function show(Writing $writing): Response
    {
        $this->countViewOnce($writing);

        $writing->loadCount(['likes', 'comments', 'shelf'])->load([
            'author' => fn ($query) => $query->forAuthorSummary(withKarma: true),
            'categories:id,name,slug',
            'tags:id,name,slug',
        ]);

        $user = Auth::user();

        return Inertia::render('writings/PoWritingsShow', [
            'meta' => [
                'title' => getPageTitle([
                    $writing->title,
                    $writing->author?->getName() ?? '',
                ]),
                'canonical' => $writing->path(),
            ],
            'writing' => $writing,
            'likers' => $writing->likers(self::LIKERS_SHOWN),
            'related' => [
                'from_author' => Writing::whereNot('id', $writing->id)
                    ->where('user_id', $writing->user_id)
                    ->inRandomOrder()->take(self::RELATED_SHOWN)->get(),
                'from_category' => randomWritingsWithAuthor(
                    Writing::whereNot('id', $writing->id)
                        ->visibleTo($this->getBlockedUsers())
                        ->whereIn(
                            'id',
                            DB::table('category_writing')
                                ->select('writing_id')
                                ->whereIn('category_id', $writing->categories->modelKeys())
                        ),
                    self::RELATED_SHOWN,
                ),
            ],
            'isAuthorBlocked' => $user !== null && $writing->author !== null ? $user->isAuthorBlocked($writing->author) : false,
        ]);
    }

    /**
     * Display a random resource.
     */
    public function random(): RedirectResponse|Redirector
    {
        $writing = User::has('writings', '>', 0)
            ->inRandomOrder()
            ->firstOrFail()
            ->writings()
            ->inRandomOrder()
            ->firstOrFail();

        return redirect($writing->path());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Writing $writing): Response
    {
        // Ensure user has the proper permission
        if ($writing->exists === true) {
            $this->authorize('update', $writing);
        }

        $mainCategories = Category::select('id', 'name')
            ->whereNull('parent_id')
            ->with('descendants')
            ->get();

        return Inertia::render('writings/PoWritingsForm', [
            'meta' => [
                'title' => request()->route()?->getName() === 'writings.edit'
                    ? getPageTitle([__('Update writing')])
                    : getPageTitle([__('Publish a writing')]),
            ],
            'writing' => [
                'data' => $writing,
                'main_category' => $writing->exists ? $writing->mainCategory()->value('id') : null,
                'categories' => $writing->exists ? $writing->altCategories()->pluck('id') : [],
                'tags' => $writing->exists ? $writing->tags()->pluck('name') : null,

            ],
            'isUpdate' => $writing->exists,
            'main_categories' => $mainCategories,
            'max-file-size' => getSiteConfig('uploads_max_file_size'),
            'agreement' => Auth::user()?->isInAgreement() ?? false,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array<string, string>
     */
    public function update(Request $request, Writing $writing, ImageStorage $images): array
    {
        $isNew = $writing->exists === false;

        // Ensure user has the proper permission
        if ($isNew === false) {
            $this->authorize('update', $writing);
        }

        $user = $this->requireAuthUser();

        if ($isNew === true) {
            $this->ensureBelowDailyPostLimit($user);
        }

        // The form posts unchecked agreements even when the user already accepted them
        $agreementRules = $user->isInAgreement() ? [] : [
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ];

        // Validate user input
        $request->validate([
            'title' => 'required|string|min:3|max:100',
            'main_category' => ['required', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id')],
            'categories' => 'required|array|exists:categories,id|max:2',
            'text' => 'required|string|min:10|max:4000',
            'tags' => 'nullable|array|max:'.self::MAX_TAGS,
            'tags.*' => 'string|min:1|max:'.self::MAX_TAG_LENGTH,
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size'),
            ...$agreementRules,
        ]);

        // Process the uploaded cover, if any
        $currentCover = $writing->extra_info['cover'] ?? '';
        $cover = $currentCover;

        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            $cover = $images->storeUpload($request->file('cover'), 'covers', self::COVER_WIDTH, self::COVER_HEIGHT);
        }

        DB::transaction(function () use ($request, $writing, $user, $cover): void {
            $writing->title = $request->input('title');

            if ($writing->exists === false) {
                $writing->author()->associate($user);
                $writing->slug = slugify($writing->getTable(), $writing->title);
            }

            $writing->text = $request->input('text');
            $writing->extra_info = [
                ...($writing->extra_info ?? []),
                'link' => $request->input('link') ?? '',
                'cover' => $cover,
            ];
            $writing->save();

            $writing->categories()->sync([$request->input('main_category'), ...(array) $request->input('categories')]);
            $writing->tags()->sync($this->resolveTagIds((array) $request->input('tags')));
        });

        if ($cover !== $currentCover) {
            $images->delete($currentCover);
        }

        // Update user aura / karma
        $writing->author?->updateAura();

        // Persist user agreements to avoid asking again
        if (isTruthy($request->input('service_agreement')) && isTruthy($request->input('privacy_agreement'))) {
            $writing->author?->acceptAgreements();
        }

        if ($isNew === true) {
            // Share on social media
            $writing->author?->notify(new WritingPublished($writing));
        }

        // Set response data
        return [
            'url' => $writing->path(),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<int, mixed>
     */
    public function destroy(Writing $writing, ImageStorage $images): array
    {
        $this->authorize('delete', $writing);

        $cover = $writing->extra_info['cover'] ?? null;

        DB::transaction(function () use ($writing): void {
            $writing->deleteOrFail();

            // Delete related notifications
            DB::table('notifications')->where('data->writing_id', $writing->id)->delete();

            // Delete related likes
            Like::where([
                ['likeable_type', Writing::class],
                ['likeable_id', $writing->id],
            ])->delete();
        });

        $images->delete($cover);

        return [];
    }

    private function ensureBelowDailyPostLimit(User $user): void
    {
        $postsToday = $user->writings()->where('created_at', '>=', Carbon::today())->count();
        $dailyPostLimit = getSiteConfig('writings.daily_post_limit') ?? self::DEFAULT_DAILY_POST_LIMIT;

        if ($postsToday >= $dailyPostLimit) {
            throw ValidationException::withMessages([
                'title' => __('You have reached your maximum number of posts for today. Please try again tomorrow.'),
            ]);
        }
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
