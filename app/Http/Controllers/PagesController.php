<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('pages/PoPagesIndex', [
            'pages' => Page::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return Inertia::render('pages/PoPagesShow', [
            'page' => $page,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Get type model
        $page = Page::where('id', request('id'))->firstOrNew();

        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'title' => ['required', 'string', Rule::unique('App\Models\Page')->ignore($page), 'min:3', 'max:40'],
            'text' => 'required|string|min:100',
        ]);

        // Update accordingly
        $page->title = request('title');
        $page->text = request('text');

        if (!$page->exists) {
            $action = 'create';
            $page->slug = slugify($page->getTable(), request('title'));
        }

        $page->save();

        if (isset($action) && 'create' === $action) {
            $message = __('Page created successfully');
        } else {
            $message = __('Page updated successfully');
        }

        return [
            'message' => $message,
            'action' => $action ?? 'update',
            'id' => $page->id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {
        $page->delete();

        return [
            'message' => __('Page deleted successfully')
        ];
    }
}
