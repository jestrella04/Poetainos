<?php

namespace App\Http\Controllers;

use App\Type;
use Illuminate\Http\Request;

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
        // Validate user input
        request()->validate([
            'id' => 'required|integer',
            'title' => 'required|string|unique:types,name|min:3|max:40',
            'description' => 'required|string|min:3|max:255',
        ]);

        // Get type model
        $type = Type::where('id', request('id'))->firstOrNew();

        // Update accordingly
        $type->name = request('title');
        $type->description = request('description');

        if (! $type->exists) {
            $action = 'create';
            $type->slug = slugify($type->getTable(), request('title'));
        }

        $type->save();

        return [
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
        //
    }
}
