<?php

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Settings\Http\Requests\UpdateSettingRequest;
use Modules\Settings\Http\Resources\SettingResource;
use Modules\Settings\Models\Setting;
use Shared\Contracts\Settings\SettingsWriter;

class SettingsController
{
    public function __construct(private readonly SettingsWriter $settingsWriter) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Setting::class);

        $settings = Setting::orderBy('key')->get();

        return SettingResource::collection($settings)->response();
    }

    public function show(string $key): JsonResponse
    {
        $setting = Setting::findOrFail($key);

        Gate::authorize('view', $setting);

        return SettingResource::make($setting)->response();
    }

    public function update(UpdateSettingRequest $request, string $key): JsonResponse
    {
        Gate::authorize('update', Setting::class);

        $this->settingsWriter->set($key, $request->input('value'));

        return SettingResource::make(Setting::findOrFail($key))->response();
    }

    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::findOrFail($key);

        Gate::authorize('delete', $setting);

        $this->settingsWriter->forget($key);

        return response()->json(null, 204);
    }
}
