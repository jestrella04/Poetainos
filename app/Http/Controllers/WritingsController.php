<?php

namespace App\Http\Controllers;

use App\Category;
use App\Tag;
use App\Type;
use App\Writing;
use Illuminate\Http\Request;

class WritingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sort = request('sort') ?? 'latest';

        $params = [
            'title' => __('Writings'),
        ];

        if ('latest' === $sort) {
            $writings = Writing::latest()
            ->simplePaginate($this->pagination);
        } elseif ('popular' === $sort) {
            $writings = Writing::orderBy('views', 'desc')
            ->simplePaginate($this->pagination);
        }

        return view('writings.index', [
            'writings' => $writings,
            'params' => $params
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
            'title' => $writing->title,
        ];

        // Increment writing views
        $writing->incrementViews();

        // Update Aura
        $writing->updateAura();
        $writing->author->updateAura();

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
        return redirect(Writing::inRandomOrder()->first()->path());
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

        $categories = Category::all();
        $types = Type::all();

        $params = [
            'title' => [
                'update' => __('Update writing'),
                'create' => __('Publish a writing'),
            ],
        ];

        return view('writings.edit', [
            'params' => $params,
            'categories' => $categories,
            'types' => $types,
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
        }

        // Validate user input
        request()->validate([
            'title' => 'required|string|min:3|max:150',
            'type' => 'nullable|integer|exists:types,id',
            'category' => 'nullable|integer|exists:categories,id',
            'text' => 'required|string|min:10|max:2000',
            'tags' => 'nullable|string',
            'link' => 'nullable|url|max:250',
            'cover' => 'nullable|file|image|max:2048'
        ]);

        // Process the uploaded cover, if any
        if ($request->hasFile('cover') && $request->file('cover')->isValid()) {
            $cover = $request->file('cover')->store('public');
        }

        // Create the extra info array
        $extraInfo = [
            'link' => request('link') ?? '',
            'cover' => $cover ?? '',
        ];

        // Persist to database if validation is successful
        $writing->user_id = auth()->user()->id;
        $writing->category_id = request('category');
        $writing->type_id = request('type');
        $writing->title = request('title');

        if (! $writing->exists) {
            $writing->slug = createSlug('writings', $writing->title);
        }

        $writing->text = request('text');
        $writing->extra_info = $extraInfo;
        $writing->save();

        $tags = [];

        // Let's grab the entered tags
        if (!empty(request('tags'))) {
            $rawTags = explode(',', request('tags'));

            foreach ($rawTags as $rawTag) {
                $tag = Tag::firstOrCreate(
                    ['name' => $rawTag],
                    ['slug' => createSlug('tags', $rawTag)]
                );

                $tags[] = $tag->id;
            }
        }

        // Persist tags
        $writing->tags()->sync($tags);

        // Set response data
        $response = $writing->toArray();
        $response['url'] = $writing->path();

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
        return $writing->delete();
    }
}
