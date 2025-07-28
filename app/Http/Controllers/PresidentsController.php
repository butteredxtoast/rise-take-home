<?php

namespace App\Http\Controllers;

use App\Services\PresidentsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class PresidentsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['message' => 'Welcome to the US Presidents API']);
    }

    /**
     * Returns the president in office on a given date and the length of their term.
     * @param $date
     * @param PresidentsService $presidentsService
     * @return JsonResponse
     */
    public function getPresidentByDate($date, PresidentsService $presidentsService): JsonResponse
    {
        $validator = validator(['date' => $date], $this->validationRules());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        try {
            $inputDate = Carbon::createFromFormat('m-d-Y', $date);
            $president = $presidentsService->findPresidentByDate($inputDate);

            return $president
                ? response()->json($president)
                : response()->json(['message' => 'No results found'], 404);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Health check endpoint that verifies BigQuery connectivity
     * @param PresidentsService $presidentsService
     * @return JsonResponse
     */
    public function bigqueryHealth(PresidentsService $presidentsService): JsonResponse
    {
        try {
            $health = $presidentsService->healthCheck();
            return response()->json($health);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'BigQuery connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Returns some fun stats, including...
     * most common astrological (sun) sign
     * most common birth day of week (Mon-Sun)
     * most common death day of week (Mon-Sun)
     *
     * @param PresidentsService $presidentsService
     * @return JsonResponse
     */
    public function random(PresidentsService $presidentsService): JsonResponse
    {
        try {
            $stats = $presidentsService->getFunStats();
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function validationRules(): array
    {
        return [
            'date' => [
                'required',
                'date_format:m-d-Y',
                'after_or_equal:04-30-1789',
                'before_or_equal:today',
            ],
        ];
    }
}
