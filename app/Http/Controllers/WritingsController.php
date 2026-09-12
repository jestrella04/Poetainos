<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Like;
use App\Models\Tag;
use App\Models\User;
use App\Models\Writing;
use App\Notifications\WritingPublished;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChain;

class WritingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response|Paginator<int, Writing>
     */
    public function index(): Response|Paginator
    {
        $awards = request()->route()?->getName() === 'writings.awards';
        $sort = resolveSort(['latest', 'popular', 'likes']);
        $filterAwards = $awards ? 'home_posted_at' : 'id';
        $writings = Writing::visibleTo($this->getBlockedUsers())
            ->whereNotNull($filterAwards)
            ->withListingRelations()
            ->sorted($sort);

        if (request()->expectsJson()) {
            return $writings->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => $awards ? getPageTitle([__('Golden Flowers')]) : getPageTitle([]),
                'canonical' => route('home'),
            ],
            'writings' => Inertia::optional(fn () => $writings->simplePaginate($this->pagination)->withQueryString()),
            'sort' => $sort,
        ]);
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
    public function store(Request $request): array
    {
        return $this->update($request, new Writing);
    }

    /**
     * Display the specified resource.
     */
    public function show(Writing $writing): Response
    {
        // Increment writing views
        $writing->incrementViews();

        // Update Aura
        $writing->updateAura();

        $user = Auth::user();

        return Inertia::render('writings/PoWritingsShow', [
            'meta' => [
                'title' => getPageTitle([
                    $writing->title,
                    $writing->author?->getName() ?? '',
                ]),
                'canonical' => $writing->path(),
            ],
            'writing' => Writing::where('id', $writing->id)
                ->withCount(['likes', 'comments', 'shelf'])
                ->with([
                    'author' => function ($query): void {
                        $query->forAuthorSummary(withKarma: true);
                    },
                ])
                ->with([
                    'categories' => function ($query): void {
                        $query->select('id', 'name', 'slug');
                    },
                ])
                ->with([
                    'tags' => function ($query): void {
                        $query->select('id', 'name', 'slug');
                    },
                ])
                ->first(),
            'likers' => $writing->likers()->shuffle()->take(5),
            'related' => [
                'from_author' => Writing::whereNot('id', $writing->id)
                    ->where('user_id', $writing->user_id)
                    ->inRandomOrder()->take(5)->get(),
                'from_category' => randomWritingsWithAuthor(
                    Writing::whereIn(
                        'id',
                        DB::table('category_writing')
                            ->select('writing_id')
                            ->whereIn('category_id', $writing->categories()->pluck('id'))
                    )
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
        if ($writing->exists) {
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
    public function update(Request $request, Writing $writing): array
    {
        $action = 'create';

        // Ensure user has the proper permission
        if ($writing->exists) {
            $this->authorize('update', $writing);
            $action = 'update';
        }

        $user = $this->requireAuthUser();

        // Check number of posts by user
        $posts = $user->writings()->whereDate('created_at', '=', Carbon::today())->count();
        $dailyPostLimit = getSiteConfig('writings.daily_post_limit') ?? 3;

        if ($posts >= $dailyPostLimit) {
            throw ValidationException::withMessages([
                'title' => __('You have reached your maximum number of posts for today. Please try again tomorrow.'),
            ]);
        }

        // Validate user input
        request()->validate([
            'title' => 'required|string|min:3|max:100',
            'main_category' => 'required|integer|exists:categories,id',
            'categories' => 'required|array|exists:categories,id|max:2',
            'text' => 'required|string|min:10|max:4000',
            'tags' => 'nullable|array',
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:'.getSiteConfig('uploads_max_file_size'),
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ]);

        // Process the uploaded cover, if any
        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            // Persist the image
            $cover = $request->file('cover')->store('covers');
            $coverRealPath = storage_path('app/'.$cover);

            // Scale image and enforce 16:9 aspect ratio
            Image::read($coverRealPath)->cover(1280, 720)->save();

            // Optimize the image
            app(OptimizerChain::class)->optimize($coverRealPath);
        }

        // Create the extra info array
        $extraInfo = [
            'link' => request('link') ?? '',
            'cover' => $cover ?? ($writing->extra_info['cover'] ?? ''),
        ];

        // Persist to database
        $writing->title = request('title');

        if (! $writing->exists) {
            $writing->author()->associate($user);
            $writing->slug = slugify($writing->getTable(), $writing->title);
        }

        $writing->text = request('text');
        $writing->extra_info = $extraInfo;
        $writing->save();

        $categories = (array) request('categories');
        array_unshift($categories, request('main_category'));

        $tagsToSync = [];

        // Let's grab the entered tags
        if (! empty(request('tags'))) {
            foreach ((array) request('tags') as $tag) {
                $tag = (string) preg_replace('/\s+/', ' ', (string) $tag);
                $tag = trim($tag);
                $tag = Tag::firstOrCreate(
                    ['name' => $tag],
                    ['slug' => slugify('tags', $tag)]
                );

                $tagsToSync[] = $tag->id;
            }
        }

        // Persist categories
        $writing->categories()->sync($categories);

        // Persist tags
        $writing->tags()->sync($tagsToSync);

        // Update user aura / karma
        $writing->author?->updateAura();

        // Persist user agreements to avoid asking again
        if (request('service_agreement') && request('privacy_agreement')) {
            $writing->author?->acceptAgreements();
        }

        // Set response message and trigger notification
        if ($action === 'create') {
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
    public function destroy(Writing $writing): array
    {
        $this->authorize('delete', $writing);
        $writing->deleteOrFail();

        // Delete related notifications
        DB::table('notifications')->where('data->writing_id', $writing->id)->delete();

        // Delete related likes
        Like::where([
            ['likeable_type', Writing::class],
            ['likeable_id', $writing->id],
        ])->delete();

        if (request('redirect')) {
            request()->session()->flash('flash', __('Writing deleted successfully'));
        }

        return [];
    }
}
