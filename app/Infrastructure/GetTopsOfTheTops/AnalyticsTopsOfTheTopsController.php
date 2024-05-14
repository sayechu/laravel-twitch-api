<?php

namespace App\Infrastructure\GetTopsOfTheTops;

use App\Infrastructure\Controllers\Controller;
use App\Services\GetTopsOfTheTopsService;
use Illuminate\Http\JsonResponse;

class AnalyticsTopsOfTheTopsController extends Controller
{
    private GetTopsOfTheTopsService $getTopsOfTopsServ;

    public function __construct(GetTopsOfTheTopsService $getTopsOfTopsServ)
    {
        $this->getTopsOfTopsServ = $getTopsOfTopsServ;
    }

    public function __invoke(AnalyticsTopsOfTheTopsRequest $request): JsonResponse
    {
        $since = $request->input('since') ?? (10 * 60);

        $topsOfTheTops = $this->getTopsOfTopsServ->getTopsOfTheTops($since);

        return response()->json($topsOfTheTops);
    }
}
