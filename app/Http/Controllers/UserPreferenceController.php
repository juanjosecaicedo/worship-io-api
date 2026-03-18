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
     * View all user preferences (including defaults)
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $request->user()->load('preferences');

        return response()->json([
            'data' => UserPreferenceResource::full($request->user()),
        ]);
    }


    /**
     * View available keys with their default values
     * @return JsonResponse
     */
    public function defaults(): JsonResponse
    {
        return response()->json([
            'data' => UserPreference::DEFAULTS,
        ]);
    }


    /**
     * SUpdate a single preference
     * @param UpdatePreferenceRequest $request
     * @return JsonResponse
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
            'message' => 'Preference updated successfully.',
            'data'    => [
                'key'   => $preference->key,
                'value' => $preference->value,
            ],
        ]);
    }

    /**
     * Update multiple preferences in a single request
     * @param UpdatePreferencesBulkRequest $request
     * @return JsonResponse
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
            'message' => count($updated) . ' preference(s) updated successfully.',
            'data'    => $updated,
        ]);
    }

    /**
     * Reset a single preference to its default value
     * @param Request $request
     * @param string $key
     * @return JsonResponse
     */
    public function reset(Request $request, string $key): JsonResponse
    {
        abort_unless(
            array_key_exists($key, UserPreference::DEFAULTS),
            422,
            'Invalid preference key.'
        );

        UserPreference::where('user_id', $request->user()->id)
            ->where('key', $key)
            ->delete();

        return response()->json([
            'message' => 'Preference reset to default value.',
            'data'    => [
                'key'   => $key,
                'value' => UserPreference::DEFAULTS[$key],
            ],
        ]);
    }

    /**
     * Reset all preferences to their default values
     * @param Request $request
     * @return JsonResponse
     */
    public function resetAll(Request $request): JsonResponse
    {
        UserPreference::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'All preferences reset to default values.',
            'data'    => UserPreference::DEFAULTS,
        ]);
    }
}
