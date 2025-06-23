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
