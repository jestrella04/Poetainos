<?php

namespace App\Http\Controllers;

use App\Type;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TypesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function show(Type $type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function edit(Type $type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Get type model
        $type = Type::where('id', request('id'))->firstOrNew();

        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'name' => ['required', 'string', Rule::unique('App\Type')->ignore($type), 'min:3', 'max:40'],
            'description' => 'required|string|min:3|max:255',
        ]);

        // Update accordingly
        $type->name = request('name');
        $type->description = request('description');

        if (! $type->exists) {
            $action = 'create';
            $type->slug = slugify($type->getTable(), request('name'));
        }

        $type->save();

        if (isset($action) && 'create' === $action) {
            $message = __('Type created successfully');
        } else {
            $message = __('Type updated successfully');
        }

        return [
            'message' => $message,
            'action' => $action ?? 'update',
            'id' => $type->id,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Type  $type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Type $type)
    {
        $type->delete();

        return [
            'message' => __('Type deleted successfully.')
        ];
    }
}
