<?php

namespace App\Infrastructure\Controllers;

use App\Infrastructure\Requests\AnalyticsUsersRequest;
use App\Services\UserDataManager;
use Illuminate\Http\JsonResponse;

class AnalyticsUsersController extends Controller
{
    private UserDataManager $userDataManager;

    const ERROR_STATUS = 503;

    public function __construct(UserDataManager $userDataManager)
    {
        $this->userDataManager = $userDataManager;
    }

    public function __invoke(AnalyticsUsersRequest $request): JsonResponse
    {
        $userId = $request->input('id');

        $userData = $this->userDataManager->getUserData($userId);

        if ($this->containsServerError($userData))
            return response()->json($userData, self::ERROR_STATUS);

        return response()->json($userData);
    }

    private function containsServerError(array $userData): bool
    {
        return isset($userData['error']);
    }
}
