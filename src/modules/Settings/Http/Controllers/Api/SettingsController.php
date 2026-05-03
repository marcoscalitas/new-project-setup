<?php

namespace Modules\Settings\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Settings\Http\Requests\UpdateSettingRequest;
use Modules\Settings\Http\Resources\SettingResource;
use Modules\Settings\Models\Setting;
use Modules\Settings\Services\SettingsService;

class SettingsController
{
    public function __construct(private readonly SettingsService $settingsService) {}

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

        $this->settingsService->set($key, $request->input('value'));

        return SettingResource::make(Setting::findOrFail($key))->response();
    }

    public function destroy(string $key): JsonResponse
    {
        $setting = Setting::findOrFail($key);

        Gate::authorize('delete', $setting);

        $this->settingsService->forget($key);

        return response()->json(null, 204);
    }
}
