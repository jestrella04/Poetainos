<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Setting $setting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Setting $setting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Setting  $setting
     * @return Response
     */
    public function update(Request $request)
    {
        // Validate user input
        request()->validate([
            'json' => 'required|json|min:3',
        ]);

        // Get settings model
        $setting = Setting::where('name', 'site')->firstOrFail();

        // Update accordingly
        $setting->data = json_decode(request('json'));
        $setting->save();

        $message = __('Settings saved successfully');

        return [
            'message' => $message,
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(Setting $setting)
    {
        //
    }
}
