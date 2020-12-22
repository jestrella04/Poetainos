<?php

namespace App\Http\Controllers;

use App\Category;
use App\Notifications\WritingPublished;
use App\Tag;
use App\User;
use App\Writing;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WritingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort = in_array(request('sort'), ['latest', 'popular', 'likes']) ? request('sort') : 'latest';

        if ('latest' === $sort) {
            $writings = Writing::latest()
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = Writing::orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        } elseif ('likes' === $sort) {
            $writings = Writing::withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->simplePaginate($this->pagination);
        }

        $params = [
            'title' => getPageTitle([]),
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
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->edit(new Writing());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $this->update($request, new Writing());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Writing  $writing
     * @return \Illuminate\Http\Response
     */
    public function show(Writing $writing)
    {
        $params = [
            'single_entry' => true,
            'title' => getPageTitle([
                $writing->title,
                $writing->author->getName(),
            ]),
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
     * @return \Illuminate\Http\Response
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
     * @param  \App\Writing  $writing
     * @return \Illuminate\Http\Response
     */
    public function edit(Writing $writing)
    {
        // Ensure user has the proper permission
        if ($writing->exists) {
            $this->authorize('update', $writing);
        }

        $mainCategories = Category::whereNull('parent_id')->get();
        $params = [
            'title' => [
                'update' => getPageTitle([__('Update writing')]),
                'create' => getPageTitle([__('Publish a writing')]),
            ],
        ];

        return view('writings.edit', [
            'params' => $params,
            'mainCategories' => $mainCategories,
            'writing' => $writing,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Writing  $writing
     * @return \Illuminate\Http\Response
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
            'tags' => 'nullable|string|min:3|max:50',
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|image|max:' . getSiteConfig('uploads_max_file_size')
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

        $tags = [];

        // Let's grab the entered tags
        if (! empty(request('tags'))) {
            $rawTags = preg_replace('/\s+/', ' ', request('tags'));
            $rawTags = preg_replace('/,+/', ',', request('tags'));
            $rawTags = explode(',', $rawTags);

            foreach ($rawTags as $rawTag) {
                $rawTag = trim($rawTag);

                $tag = Tag::firstOrCreate(
                    ['name' => $rawTag],
                    ['slug' => slugify('tags', $rawTag)]
                );

                $tags[] = $tag->id;
            }
        }

        // Persist categories
        $writing->categories()->sync($categories);

        // Persist tags
        $writing->tags()->sync($tags);

        // Update user aura
        $writing->author->updateAura();

        // Set response message and trigger notification
        if (isset($action) && 'update' === $action) {
            $message = __('Your writing was successfully updated.');
        } else {
            $message = __('Your writing was successfully posted.');

            // Share on social media
            $writing->author->notify(new WritingPublished($writing));
        }

        // Apend link
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
     * @param  \App\Writing  $writing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Writing $writing)
    {
        $this->authorize('delete', $writing);
        $writing->delete();

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
