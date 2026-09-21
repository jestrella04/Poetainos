<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @return array<string, string>
     */
    public function update(Request $request, SiteSettings $siteSettings): array
    {
        // Validate user input
        $request->validate([
            'json' => 'required|json|min:3',
        ]);

        $data = json_decode((string) $request->input('json'), true);

        if (is_array($data) === false) {
            throw ValidationException::withMessages([
                'json' => __('The settings must be a JSON object.'),
            ]);
        }

        $validator = validator($data, [
            'name.value' => 'present|string',
            'slogan.value' => 'present|string',
            'pagination.value' => 'required|integer|min:1|max:100',
            'uploads_max_file_size.value' => 'required|integer|min:1',
            'writings.daily_post_limit.value' => 'sometimes|integer|min:1',
            'aura.min_at_home.value' => 'sometimes|numeric|min:0',
            'aura.points.writing.*.value' => 'numeric|min:0',
            'aura.points.user.*.value' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'json' => $validator->errors()->all(),
            ]);
        }

        // Get settings model
        $setting = Setting::where('name', 'site')->firstOrFail();

        // Update accordingly
        $setting->data = $data;
        $setting->save();

        $siteSettings->refresh();

        $message = __('Settings saved successfully');

        return [
            'message' => $message,
        ];
    }
}
