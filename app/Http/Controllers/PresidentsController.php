<?php

namespace App\Http\Controllers;

use Google\Cloud\BigQuery\BigQueryClient;

class PresidentsController extends Controller
{
    public function index()
    {
        try {
            $bigquery = new BigQueryClient(['projectId' => 'rise-take-home-463523']);

            $query = $bigquery->query('SELECT * FROM us_presidents.presidents WHERE No_ = 1');
            $results = $bigquery->runQuery($query);

            foreach ($results as $row) {
                return response()->json($row);
            }
            return response()->json(['message' => 'No results found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ], 500);
        }
    }
}
