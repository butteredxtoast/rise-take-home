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

    /**
     * Returns some fun stats, including...
     * most common astrological (sun) sign
     * most common birth day of week (Mon-Sun)
     * most common death day of week (Mon-Sun)
     *
     * @return array
     */
    public function getFunStats(): array
    {
        $query = $this->bigQuery->query(
            'SELECT * FROM `' . self::TABLE_NAME . '`'
        );
        $results = $this->bigQuery->runQuery($query);

        $signs = [];
        $birthDays = [];
        $deathDays = [];

        foreach ($results as $row) {
            $birthDate = Carbon::createFromFormat('F j, Y', $row['Born']);
            $sign = $this->getAstrologicalSign($birthDate);

            if ($sign) {
                if (!isset($signs[$sign])) {
                    $signs[$sign] = 0;
                }
                $signs[$sign]++;
            }

            $birthDay = $birthDate->format('l');
            if ($birthDay) {
                if (!isset($birthDays[$birthDay])) {
                    $birthDays[$birthDay] = 0;
                }
                $birthDays[$birthDay]++;
            }

            if (strtolower(trim($row['Died'])) !== 'still alive') {
                $deathDate = Carbon::createFromFormat('F j, Y', $row['Died']);
                $deathDay = $deathDate->format('l');
                if ($deathDay) {
                    if (!isset($deathDays[$deathDay])) {
                        $deathDays[$deathDay] = 0;
                    }
                    $deathDays[$deathDay]++;
                }
            }
        }

        $mostCommonSign = array_search(max($signs), $signs) ?: 'N/A';
        $leastCommonSign = array_search(min($signs), $signs) ?: 'N/A';

        $mostCommonBirthDay = array_search(max($birthDays), $birthDays) ?: 'N/A';
        $leastCommonBirthDay = array_search(min($birthDays), $birthDays) ?: 'N/A';

        $mostCommonDeathDay = array_search(max($deathDays), $deathDays) ?: 'N/A';
        $leastCommonDeathDay = array_search(min($deathDays), $deathDays) ?: 'N/A';

        return [
            'Most Common Sun Sign' => $mostCommonSign,
            'Least Common Sun Sign' => $leastCommonSign,
            'Most Common Birth Day' => $mostCommonBirthDay,
            'Least Common Birth Day' => $leastCommonBirthDay,
            'Most Common Death Day' => $mostCommonDeathDay,
            'Least Common Death Day' => $leastCommonDeathDay
        ];
    }

    private function getAstrologicalSign(Carbon $date): string
    {
        $month = $date->month;
        $day = $date->day;

        if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) return 'Aries';
        if (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) return 'Taurus';
        if (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) return 'Gemini';
        if (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) return 'Cancer';
        if (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) return 'Leo';
        if (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) return 'Virgo';
        if (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) return 'Libra';
        if (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) return 'Scorpio';
        if (($month == 11 && $day >= 22) || ($month == 12 && $day <= 21)) return 'Sagittarius';
        if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) return 'Capricorn';
        if (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) return 'Aquarius';
        if (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) return 'Pisces';

        return 'Not of this Planet';
    }
}
