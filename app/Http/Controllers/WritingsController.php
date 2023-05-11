<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Notifications\WritingPublished;
use App\Models\Tag;
use App\Models\User;
use App\Models\Like;
use App\Models\Writing;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;

class WritingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';
        $filter = [0];

        if (auth()->check()) {
            $filter = User::find(auth()->user()->id)->getBlockedAuthors($asArrayOfIds = true);
        }

        if ('latest' === $sort) {
            $writings = Writing::whereNotIn('user_id', $filter)
            ->latest()
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = Writing::whereNotIn('user_id', $filter)
            ->orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = Writing::whereNotIn('user_id', $filter)
            ->withCount('likes')
            ->orderBy('likes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        $params = [
            'title' => getPageTitle([]),
            'canonical' => route('home'),
        ];

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params,
            'sort' => $sort,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(Writing $writing)
    {
        $params = [
            'writings_single_entry' => true,
            'title' => getPageTitle([
                $writing->title,
                $writing->author->getName(),
            ]),
            'canonical' => $writing->path(),
        ];

        // Increment writing views
        $writing->incrementViews();

        // Update Aura
        $writing->updateAura();
        //$writing->author->updateAura();

        return view('writings.show', [
            'writing' => $writing,
            'params' => $params
        ]);
    }

    /**
     * Display a random resource.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function random() {
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Writing $writing)
    {
        // Ensure user has the proper permission
        if ($writing->exists) {
            $this->authorize('update', $writing);
        }

        $mainCategories = Category::whereNull('parent_id')->get();
        $subCategories = Category::whereNotNull('parent_id')->get();
        $params = [
            'title' => [
                'update' => getPageTitle([__('Update writing')]),
                'create' => getPageTitle([__('Publish a writing')]),
            ],
        ];

        return view('writings.edit', [
            'params' => $params,
            'mainCategories' => $mainCategories,
            'subCategories' => $subCategories,
            'writing' => $writing,
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
        // Ensure user has the proper permission
        if ($writing->exists) {
            $this->authorize('update', $writing);
            $action = 'update';
        }

        // Validate user input
        request()->validate([
            'title' => 'required|string|min:3|max:100',
            'main_category' => 'required|integer|exists:categories,id',
            'categories' => 'nullable|array|exists:categories,id',
            'text' => 'required|string|min:10|max:2000',
            'tags' => 'nullable|array',
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|image|max:' . getSiteConfig('uploads_max_file_size'),
            'service_agreement' => 'sometimes|required|accepted',
            'privacy_agreement' => 'sometimes|required|accepted',
        ]);

        // Process the uploaded cover, if any
        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            // Persist the image
            $cover = $request->file('cover')->store('covers');
            $coverRealPath = storage_path('app/' . $cover);

            // Scale image and enforce 16:9 aspect ratio
            Image::make($coverRealPath)->resize(1280, null, function ($constraint) {
                $constraint->aspectRatio();
            })->crop(1280, 720)->save();

            // Optimize the image
            app(\Spatie\ImageOptimizer\OptimizerChain::class)->optimize($coverRealPath);
        }

        // Create the extra info array
        $extraInfo = [
            'link' => request('link') ?? '',
            'cover' => $cover ?? ($writing->extra_info['cover'] ?? ''),
        ];

        // Persist to database
        $writing->title = request('title');

        if (! $writing->exists) {
            $writing->user_id = auth()->user()->id;
            $writing->slug = slugify($writing->getTable(), $writing->title);
        }

        $writing->text = request('text');
        $writing->extra_info = $extraInfo;
        $writing->save();

        $categories = request('categories');

        if (! empty(request('categories'))) {
            array_unshift($categories, request('main_category'));
        }

        $tagsToSync = [];

        // Let's grab the entered tags
        if (! empty(request('tags'))) {
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

        // Update user aura
        $writing->author->updateAura();

        // Persist user agreements to avoid asking again
        if (!empty(request('service_agreement') && !empty(request('privacy_agreement')))) {
            $writing->author->acceptAgreements();
        }

        // Set response message and trigger notification
        if (isset($action) && 'update' === $action) {
            $message = __('Your writing was successfully updated.');
        } else {
            $message = __('Your writing was successfully posted.');

            // Share on social media
            $writing->author->notify(new WritingPublished($writing));

            // Add a like automatically from the poster
            $like = new Like;
            $like->user_id = auth()->user()->id;
            $like->vote = 1;
            $like->likeable()->associate(Writing::find($writing->id));
            $like->save();
        }

        // Append link
        $message .= ' <a href="{url}">' . __('Take a look for yourself') . '</a>';

        // Set response data
        $response = $writing->toArray();
        $response['url'] = $writing->path();
        $response['message'] = str_replace('{url}', $writing->path(), $message);
        $response['action'] = $action ?? 'create';

        // Output the response
        return $response;
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
        $writing->delete();
        DatabaseNotification::where('data->writing_id', $writing->id)->delete();

        // Delete related notifications
        DB::delete('DELETE FROM `notifications` WHERE JSON_EXTRACT(`data`, "$.writing_id") = ?', [$writing->id]);

        if (request('redirect')) {
            request()->session()->flash('flash', __('Writing deleted successfully'));
        }

        return [
            'message' => __('Writing deleted successfully')
        ];
    }
}
