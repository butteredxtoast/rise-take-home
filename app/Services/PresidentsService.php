<?php

namespace App\Services;

use Carbon\Carbon;
use Google\Cloud\BigQuery\BigQueryClient;

class PresidentsService
{
    const PROJECT_ID = 'rise-take-home-463523';
    const TABLE_NAME = 'rise-take-home-463523.us_presidents.presidents';
    private BigQueryClient $bigQuery;

    public function __construct()
    {
        $this->bigQuery = new BigQueryClient([
            'projectId' => config('services.bigquery.project_id', self::PROJECT_ID)
        ]);
    }

    public function findPresidentByDate(Carbon $date): ?array
    {
        $query = $this->bigQuery->query(
            'SELECT * FROM `' . self::TABLE_NAME . '`'
        );
        $results = $this->bigQuery->runQuery($query);

        foreach ($results as $row) {
            $termStart = Carbon::createFromFormat('F j, Y', $row['Term Start']);

            if (strtolower(trim($row['Term End'])) === 'present') {
                $termEnd = Carbon::now();
            } else {
                $termEnd = Carbon::createFromFormat('F j, Y', $row['Term End']);
            }

            if ($date >= $termStart && $date <= $termEnd) {
                return [
                    'President' => $row['President'],
                    'Term Start' => $row['Term Start'],
                    'Term End' => $row['Term End'],
                    'Length of Term' => $termStart->diff($termEnd)->y . ' years, ' .
                        $termStart->diff($termEnd)->m . ' months, ' .
                        $termStart->diff($termEnd)->d . ' days'
                ];
            }
        }

        return null;
    }
}
