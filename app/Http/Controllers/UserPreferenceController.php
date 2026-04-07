<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePreferenceRequest;
use App\Http\Requests\User\UpdatePreferencesBulkRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{

    /**
     * List preferences
     * 
     * Returns all user preferences including those with default values.
     */
    public function index(Request $request): JsonResponse
    {
        $request->user()->load('preferences');

        return response()->json([
            'data' => UserPreferenceResource::full($request->user()),
        ]);
    }

    /**
     * Get default preferences
     * 
     * Returns the available preference keys and their default values.
     */
    public function defaults(): JsonResponse
    {
        return response()->json([
            'data' => UserPreference::DEFAULTS,
        ]);
    }

    /**
     * Update preference
     */
    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        $preference = UserPreference::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'key'     => $request->key,
            ],
            ['value' => $request->value]
        );

        return response()->json([
            'message' => __('users.preference_updated'),
            'data'    => [
                'key'   => $preference->key,
                'value' => $preference->value,
            ],
        ]);
    }

    /**
     * Bulk update preferences
     * 
     * Updates multiple preferences in a single request.
     */
    public function bulkUpdate(UpdatePreferencesBulkRequest $request): JsonResponse
    {
        $userId   = $request->user()->id;
        $updated  = [];

        foreach ($request->preferences as $item) {
            UserPreference::updateOrCreate(
                [
                    'user_id' => $userId,
                    'key'     => $item['key'],
                ],
                ['value' => $item['value']]
            );

            $updated[$item['key']] = $item['value'];
        }

        return response()->json([
            'message' => __('users.bulk_preferences_updated', ['count' => count($updated)]),
            'data'    => $updated,
        ]);
    }

    /**
     * Reset preference
     * 
     * Resets a specific preference to its default value by deleting the custom setting.
     */
    public function reset(Request $request, string $key): JsonResponse
    {
        abort_unless(
            array_key_exists($key, UserPreference::DEFAULTS),
            422,
            __('users.invalid_preference_key')
        );

        UserPreference::where('user_id', $request->user()->id)
            ->where('key', $key)
            ->delete();

        return response()->json([
            'message' => __('users.preference_reset'),
            'data'    => [
                'key'   => $key,
                'value' => UserPreference::DEFAULTS[$key],
            ],
        ]);
    }

    /**
     * Reset all preferences
     * 
     * Resets all user preferences to their default values.
     */
    public function resetAll(Request $request): JsonResponse
    {
        UserPreference::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => __('users.all_preferences_reset'),
            'data'    => UserPreference::DEFAULTS,
        ]);
    }
}
