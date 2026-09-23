<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('pages/PoPagesIndex', [
            'meta' => [
                'title' => getPageTitle([__('Pages')]),
            ],
            'pages' => Page::all(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page): Response
    {
        $page->text = hydrateSettings($page->text);

        return Inertia::render('pages/PoPagesShow', [
            'meta' => [
                'title' => getPageTitle([$page->title]),
            ],
            'page' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return array<string, mixed>
     */
    public function update(Request $request): array
    {
        // Get type model
        $page = Page::where('id', request('id'))->firstOrNew();

        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'title' => ['required', 'string', Rule::unique('App\Models\Page')->ignore($page), 'min:3', 'max:40'],
            'text' => 'required|string|min:100',
        ]);

        $action = $page->exists ? 'update' : 'create';

        // Update accordingly
        $page->title = request('title');
        $page->text = request('text');

        if ($action === 'create') {
            $page->slug = slugify($page->getTable(), request('title'));
        }

        $page->save();

        $message = $action === 'create'
            ? __('Page created successfully')
            : __('Page updated successfully');

        return [
            'message' => $message,
            'action' => $action,
            'id' => $page->id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return array<string, string>
     */
    public function destroy(Page $page): array
    {
        $page->delete();

        return [
            'message' => __('Page deleted successfully'),
        ];
    }
}
