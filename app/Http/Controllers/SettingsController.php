<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @return array<string, string>
     */
    public function update(Request $request): array
    {
        // Validate user input
        request()->validate([
            'json' => 'required|json|min:3',
        ]);

        // Get settings model
        $setting = Setting::where('name', 'site')->firstOrFail();

        // Update accordingly
        $setting->data = json_decode((string) request('json'));
        $setting->save();

        $message = __('Settings saved successfully');

        return [
            'message' => $message,
        ];
    }
}
