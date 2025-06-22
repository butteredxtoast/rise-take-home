<?php

namespace App\Http\Controllers;

use Google\Cloud\BigQuery\BigQueryClient;

class PresidentsController extends Controller
{
    public function index(): string
    {
        $bigquery = new BigQueryClient([
            'projectId' => 'rise-take-home-463523'
        ]);

        $query = $bigquery->query('SELECT * FROM us_presidents.presidents ORDER BY name');
        $results = $bigquery->runQuery($query);

        foreach ($results as $row) {
            return response()->json($row);
        }

        return response()->json(['message' => 'No data found'], 404);
    }
}
