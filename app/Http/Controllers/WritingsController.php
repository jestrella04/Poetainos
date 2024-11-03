<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Notifications\WritingPublished;
use App\Models\Tag;
use App\Models\User;
use App\Models\Like;
use App\Models\Writing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\ImageOptimizer\OptimizerChain;

class WritingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response|\Illuminate\Contracts\Pagination\Paginator
     */
    public function index()
    {
        $awards = 'writings.awards' === request()->route()->getName();
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $filterAwards = $awards ? 'home_posted_at' : 'id';
        $writings = Writing::whereNotIn('user_id', $this->getBlockedUsers())
            ->whereNotNull($filterAwards)
            ->withCount(['likes', 'comments', 'shelf'])
            ->with([
                'author' => function ($query) {
                    $query->select('id', 'username', 'name', 'karma', 'extra_info->avatar AS avatar');
                }
            ]);

        if ('latest' === $sort) {
            $writings = $writings->latest();
        } elseif ('popular' === $sort) {
            $writings = $writings->orderBy('views', 'desc');
        } elseif ('likes' === $sort) {
            $writings = $writings->orderBy('likes_count', 'desc');
        }

        if (request()->expectsJson()) {
            return $writings->simplePaginate($this->pagination)->withQueryString();
        }

        return Inertia::render('writings/PoWritingsIndex', [
            'meta' => [
                'title' => $awards ? getPageTitle([__('Golden Flowers')]) : getPageTitle([]),
                'canonical' => route('home'),
            ],
            'writings' => Inertia::lazy(fn() => $writings->simplePaginate($this->pagination)->withQueryString()),
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return $this->edit(new Writing());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        return $this->update($request, new Writing());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Writing  $writing
     * @return \Inertia\Response
     */
    public function show(Writing $writing)
    {
        // Increment writing views
        $writing->incrementViews();

        // Update Aura
        $writing->updateAura();
        //$writing->author->updateAura();

        $user = auth()->check() ? User::find(auth()->user()->id) : null;

        return Inertia::render('writings/PoWritingsShow', [
            'meta' => [
                'title' => getPageTitle([
                    $writing->title,
                    $writing->author->getName(),
                ]),
                'canonical' => $writing->path(),
            ],
            'writing' => Writing::whereId($writing->id)
                ->withCount(['likes', 'comments', 'shelf'])
                ->with([
                    'author' => function ($query) {
                        $query->select('id', 'username', 'name', 'karma', 'extra_info->avatar AS avatar');
                    }
                ])
                ->with([
                    'categories' => function ($query) {
                        $query->select('id', 'name', 'slug');
                    }
                ])
                ->with([
                    'tags' => function ($query) {
                        $query->select('id', 'name', 'slug');
                    }
                ])
                ->first(),
            'likers' => $writing->likers()->shuffle()->take(5),
            'related' => [
                'from_author' => Writing::whereNot('id', $writing->id)
                    ->where('user_id', $writing->user_id)
                    ->inRandomOrder()->take(5)->get(),
                'from_category' => Writing::whereIn(
                    'id',
                    DB::table('category_writing')
                        ->select('writing_id')
                        ->whereIn('category_id', $writing->categories()->pluck('id'))
                )->with([
                            'author' => function ($query) {
                                $query->select('id', 'username', 'name', 'extra_info->avatar AS avatar');
                            }
                        ])->inRandomOrder()->take(5)->get(),

            ],
            'isAuthorBlocked' => auth()->check() ? $user->isAuthorBlocked($writing->author) : false
        ]);
    }

    /**
     * Display a random resource.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function random()
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
     *
     * @param  \App\Models\Writing  $writing
     * @return \Inertia\Response
     */
    public function edit(Writing $writing)
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
                'title' => request()->route()->getName() === 'writings.edit'
                    ? getPageTitle([__('Update writing')])
                    : getPageTitle([__('Publish a writing')])
            ],
            'writing' => [
                'data' => $writing,
                'main_category' => $writing->exists ? $writing->mainCategory()->pluck('id')->first() : null,
                'categories' => $writing->exists ? $writing->altCategories()->pluck('id') : [],
                'tags' => $writing->exists ? $writing->tags()->pluck('name') : null,

            ],
            'main_categories' => $mainCategories,
            'max-file-size' => getSiteConfig('uploads_max_file_size'),
            'agreement' => User::find(auth()->user()->id)->isInAgreement(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Writing  $writing
     * @return array
     */
    public function update(Request $request, Writing $writing)
    {
        $action = 'create';

        // Ensure user has the proper permission
        if ($writing->exists) {
            $this->authorize('update', $writing);
            $action = 'update';
        }

        // Check number of posts by user
        $posts = auth()->user()->writings()->whereDate("created_at", "=", Carbon::today())->count();

        if ($posts >= 3) {
            throw ValidationException::withMessages([
                'title' => __('You have reached your maximum number of posts for today. Please try again tomorrow.')
            ]);
        }

        // Validate user input
        request()->validate([
            'title' => 'required|string|min:3|max:100',
            'main_category' => 'required|integer|exists:categories,id',
            'categories' => 'required|array|exists:categories,id|max:2',
            'text' => 'required|string|min:10|max:2000',
            'tags' => 'nullable|array',
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|image|max:' . getSiteConfig('uploads_max_file_size'),
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ]);

        //dd($request);

        // Process the uploaded cover, if any
        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            // Persist the image
            $cover = $request->file('cover')->store('covers');
            $coverRealPath = storage_path('app/' . $cover);

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

        if (!$writing->exists) {
            $writing->user_id = auth()->user()->id;
            $writing->slug = slugify($writing->getTable(), $writing->title);
        }

        $writing->text = request('text');
        $writing->extra_info = $extraInfo;
        $writing->save();

        $categories = request('categories');
        array_unshift($categories, request('main_category'));

        $tagsToSync = [];

        // Let's grab the entered tags
        if (!empty(request('tags'))) {
            foreach (request('tags') as $tag) {
                $tag = preg_replace('/\s+/', ' ', $tag);
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
        $writing->author->updateAura();
        //$writing->author->updateKarma();

        // Persist user agreements to avoid asking again
        if (!empty(request('service_agreement') && !empty(request('privacy_agreement')))) {
            $writing->author->acceptAgreements();
        }

        // Set response message and trigger notification
        if ('create' === $action) {
            // Share on social media
            $writing->author->notify(new WritingPublished($writing));

            // Add a like automatically from the poster
            /* $like = new Like;
            $like->user_id = auth()->user()->id;
            $like->vote = 1;
            $like->likeable()->associate(Writing::find($writing->id));
            $like->save(); */
        }

        // Set response data
        return [
            'url' => $writing->path(),
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Writing  $writing
     * @return array
     */
    public function destroy(Writing $writing)
    {
        $this->authorize('delete', $writing);
        $writing->deleteOrFail();

        // Delete related notifications
        DatabaseNotification::where('data->writing_id', $writing->id)->delete();

        // Delete related likes
        Like::where([
            ['likeable_type', 'App\Models\Writing'],
            ['likeable_id', $writing->id]
        ])->delete();

        if (request('redirect')) {
            request()->session()->flash('flash', __('Writing deleted successfully'));
        }

        return [];
    }
}
