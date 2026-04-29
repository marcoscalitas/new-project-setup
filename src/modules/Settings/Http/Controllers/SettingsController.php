<?php

namespace Modules\Settings\Http\Controllers;

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

    public function index(Request $request): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('viewAny', Setting::class);

        $settings = Setting::orderBy('key')->get();

        if ($request->expectsJson()) {
            return SettingResource::collection($settings)->response();
        }

        return view('settings::settings.index', compact('settings'));
    }

    public function show(Request $request, string $key): JsonResponse|\Illuminate\View\View
    {
        Gate::authorize('view', Setting::class);

        $setting = Setting::findOrFail($key);

        if ($request->expectsJson()) {
            return (new SettingResource($setting))->response();
        }

        return view('settings::settings.show', compact('setting'));
    }

    public function update(UpdateSettingRequest $request, string $key): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('update', Setting::class);

        $this->settingsService->set($key, $request->input('value'));

        $setting = Setting::findOrFail($key);

        if ($request->expectsJson()) {
            return (new SettingResource($setting))->response();
        }

        return redirect()->route('settings.index')->with('success', __('ui.setting_updated'));
    }

    public function destroy(Request $request, string $key): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        Gate::authorize('delete', Setting::class);

        Setting::findOrFail($key);

        $this->settingsService->forget($key);

        if ($request->expectsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->route('settings.index')->with('success', __('ui.setting_deleted'));
    }
}
