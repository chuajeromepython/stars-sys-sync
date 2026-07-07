<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ECDC extends Model
{
    use HasFactory;
    // use \OwenIt\Auditing\Auditable;

    const BOSY = 1;

    const MOSY = 2;

    const EOSY = 3;

    protected $table = 'tbl_ecdcs';

    public static function saveResults($ecdc_id)
    {

        $ecdc = ECDC::find($ecdc_id);

        $students = StudentClassroom::where('classroom_id', $ecdc->classroom_id)->get();
        $data = [];
        foreach ($students as $key => $student) {

            $students_ecdcs = StudentECDC::where('ecdc_id', $ecdc_id)
                ->join('tbl_ecdc_competencies', 'tbl_student_ecdcs.ecdc_competency_id', 'tbl_ecdc_competencies.id')
                ->where('student_id', $student->student_id)
                ->get();

            if ($students_ecdcs->count() > 0) {
                foreach ($students_ecdcs as $key => $students_ecdc) {
                    $data[$student->student_id][$students_ecdc->ecdc_competency_id] = [
                        'student_ecdc_id' => $students_ecdc->id,
                        'ecdc_competency_id' => $students_ecdc->ecdc_competency_id,
                        'competency' => $students_ecdc->competency,
                        'score' => $students_ecdc->score,
                    ];
                }
            }
        }

        return $data;

    }

    public static function getResults($ecdc_id)
    {

        $data = ECDC::saveResults($ecdc_id);
        $result = [];
        $ecdc = ECDC::find($ecdc_id);

        foreach ($data as $student_id => $competencies) {

            $student = Student::select(
                'tbl_students.id', 'lrn',
                'first_name', 'middle_name', 'last_name', 'birth_date', 'gender'
            )->join('tbl_users', 'tbl_students.user_id', 'tbl_users.id')
                ->join('tbl_persons', 'tbl_users.person_id', 'tbl_persons.id')
                ->where('tbl_students.id', $student_id)
                ->first();
            $date = new DateTime($ecdc->date);
            $birthdate = new DateTime($student->birth_date);
            $age = $date->diff($birthdate);
            $age = $age->y.'.'.$age->m;

            $domains_score = [];
            $domains_competencies = [];
            $domains = [];

            foreach ($competencies as $competency_id => $points) {
                $domain_id = ECDC::getDomain($competency_id);

                if (array_key_exists($domain_id, $domains_score)) {
                    // $domains_score[$domain_id] = $domains_score[$domain_id]+$points['score'];

                    $domains_score[$domain_id] = $domains_score[$domain_id] + $points['score'];
                } else {
                    $domains_score[$domain_id] = $points['score'];
                }
                // push competency on perspective domain

                $domains_competencies[$domain_id]['competencies'][$points['student_ecdc_id']] = $points;

            }

            $total_scaled_score = 0;
            foreach ($domains_score as $domain_id => $score) {
                $scaled_score = ECDC::getScaledScore($score, $domain_id, $age);
                $domains[$domain_id] = [
                    'score' => $score,
                    'scaled_score' => $scaled_score,
                    'competencies' => $domains_competencies[$domain_id]['competencies'],
                ];
                $total_scaled_score += $scaled_score;
            }

            $result[$student_id] = [
                'lrn' => $student->lrn,
                'name' => $student->last_name.', '.$student->first_name.' '.$student->middle_name,
                'age' => $age,
                'date_tested' => $ecdc->date,
                'domains' => $domains,
                'total_scaled_score' => $total_scaled_score,
                'standard_score' => ECDC::getStandardScore($total_scaled_score),
                'interpretation' => ECDC::getInterpretation(ECDC::getStandardScore($total_scaled_score)),
            ];

        }

        Storage::disk('public')->put('ecdc-'.$ecdc_id.'.json', json_encode($result, JSON_PRETTY_PRINT));

        return $result;

    }

    public static function getJsonResult($ecdc_id)
    {
        $path = storage_path().'\app\public\ecdc-'.$ecdc_id.'.json';
        $results = json_decode(file_get_contents($path), true);

        return $results;
    }

    public static function getInterpretation($standard_score)
    {

        if ($standard_score <= 69) {
            $interpretation = 'Suggest significant delay in overall development';
        } elseif ($standard_score >= 70 && $standard_score <= 79) {
            $interpretation = 'Suggest slight delay in overall development';
        } elseif ($standard_score >= 80 && $standard_score <= 119) {
            $interpretation = 'Average development';
        } elseif ($standard_score >= 120 && $standard_score <= 129) {
            $interpretation = 'Suggest slightly advanced development';
        } elseif ($standard_score >= 130) {
            $interpretation = 'Suggest highly advanced development';
        }

        return $interpretation;
    }

    public static function getDomain($competency_id)
    {

        $domain_id = 0;
        if ($competency_id >= 1 && $competency_id <= 13) {
            $domain_id = 1;
        } elseif ($competency_id >= 14 && $competency_id <= 24) {
            $domain_id = 2;
        } elseif ($competency_id >= 25 && $competency_id <= 51) {
            $domain_id = 3;
        } elseif ($competency_id >= 52 && $competency_id <= 56) {
            $domain_id = 4;
        } elseif ($competency_id >= 57 && $competency_id <= 64) {
            $domain_id = 5;
        } elseif ($competency_id >= 65 && $competency_id <= 85) {
            $domain_id = 6;
        } elseif ($competency_id >= 86 && $competency_id <= 109) {
            $domain_id = 7;
        }

        return $domain_id;

    }

    public static function getScaledScore($score, $domain_id, $age)
    {

        if ($age >= 3.1 && $age <= 4.0) {
            $reference = ECDC::getScaledScoreAgeBracket_A();
        } elseif ($age >= 4.1 && $age <= 5.0) {
            $reference = ECDC::getScaledScoreAgeBracket_B();
        } elseif ($age >= 5.1 && $age <= 5.11) {
            $reference = ECDC::getScaledScoreAgeBracket_C();
        } else {
            $reference = ECDC::getScaledScoreAgeBracket_C();
        }

        $scaled_score = $reference[$domain_id][$score];

        return $scaled_score;

    }

    public static function countCompetencyPerDomain()
    {

        $result = [];
        $domains = ECDCDomain::all();
        foreach ($domains as $domain) {
            $count_competencies = ECDCCompetency::where('domain_id', $domain->id)->count();
            $result[$domain->id] = $count_competencies;
        }

        return $result;

    }

    public static function getScaledScoreAgeBracket_A()
    {

        $domain_lengths = ECDC::countCompetencyPerDomain();
        $data = [];
        foreach ($domain_lengths as $domain_id => $length) {

            switch ($domain_id) {
                case 1:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 3) {
                            $data[$domain_id][$score] = 1;
                        }
                        if ($score >= 4 && $score <= 5) {
                            $data[$domain_id][$score] = $score - 2;
                        }
                        if ($score >= 6 && $score <= 9) {
                            $data[$domain_id][$score] = $score - 1;
                        }
                        if ($score >= 10 && $score <= 13) {
                            $data[$domain_id][$score] = $score;
                        }
                        if ($score == 14) {
                            $data[$domain_id][$score] = 13;
                        }
                    }
                    break;
                case 2:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 3) {
                            $data[$domain_id][$score] = 2;
                        }
                        if ($score >= 4 && $score <= 5) {
                            $data[$domain_id][$score] = $score;
                        }
                        if ($score == 6) {
                            $data[$domain_id][$score] = 7;
                        }
                        if ($score == 7) {
                            $data[$domain_id][$score] = 9;
                        }
                        if ($score == 8) {
                            $data[$domain_id][$score] = 10;
                        }
                        if ($score == 9) {
                            $data[$domain_id][$score] = 12;
                        }
                        if ($score == 10) {
                            $data[$domain_id][$score] = 14;
                        }
                        if ($score == 11) {
                            $data[$domain_id][$score] = 15;
                        }

                    }
                    break;
                case 3:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 9) {
                            $data[$domain_id][$score] = 1;
                        }
                        if ($score >= 10 && $score <= 13) {
                            $data[$domain_id][$score] = $score - 8;
                        }
                        if ($score >= 14 && $score <= 18) {
                            $data[$domain_id][$score] = $score - 9;
                        }
                        if ($score >= 19 && $score <= 23) {
                            $data[$domain_id][$score] = $score - 10;
                        }
                        if ($score >= 24 && $score <= 27) {
                            $data[$domain_id][$score] = $score - 11;
                        }
                    }
                    break;
                case 4:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 1) {
                            $data[$domain_id][$score] = 3;
                        }
                        if ($score == 2) {
                            $data[$domain_id][$score] = 5;
                        }
                        if ($score == 3) {
                            $data[$domain_id][$score] = 7;
                        }
                        if ($score == 4) {
                            $data[$domain_id][$score] = 10;
                        }
                        if ($score == 5) {
                            $data[$domain_id][$score] = 12;
                        }
                    }
                    break;
                case 5:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 2) {
                            $data[$domain_id][$score] = 1;
                        }
                        if ($score >= 3 && $score <= 4) {
                            $data[$domain_id][$score] = $score;
                        }
                        if ($score == 5) {
                            $data[$domain_id][$score] = 7;
                        }
                        if ($score == 6) {
                            $data[$domain_id][$score] = 9;
                        }
                        if ($score == 7) {
                            $data[$domain_id][$score] = 11;
                        }
                        if ($score == 8) {
                            $data[$domain_id][$score] = 13;
                        }
                    }
                    break;
                case 6:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 2) {
                            $data[$domain_id][$score] = $score + 3;
                        }
                        if ($score >= 3 && $score <= 8) {
                            $data[$domain_id][$score] = $score + 2;
                        }
                        if ($score >= 9 && $score <= 13) {
                            $data[$domain_id][$score] = $score + 1;
                        }
                        if ($score >= 14 && $score <= 19) {
                            $data[$domain_id][$score] = $score;
                        }
                        if ($score > 19) {
                            $data[$domain_id][$score] = 19;
                        }
                    }
                    break;
                case 7:
                    for ($score = 0; $score <= $length; $score++) {
                        if ($score >= 0 && $score <= 9) {
                            $data[$domain_id][$score] = 1;
                        }
                        if ($score >= 10 && $score <= 11) {
                            $data[$domain_id][$score] = 2;
                        }
                        if ($score >= 12 && $score <= 15) {
                            $data[$domain_id][$score] = $score - 9;
                        }
                        if ($score == 17) {
                            $data[$domain_id][$score] = 8;
                        }
                        if ($score >= 14 && $score <= 17) {
                            $data[$domain_id][$score] = $score - 9;
                        }
                        if ($score >= 18) {
                            $data[$domain_id][$score] = $score - 10;
                        }
                    }
                    break;
                default:
                    // code...
                    break;
            }

        }

        return $data;

    }

    public static function getScaledScoreAgeBracket_B()
    {

        $data = [
            1 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 2,
                7 => 4,
                8 => 5,
                9 => 7,
                10 => 8,
                11 => 10,
                12 => 11,
                13 => 13,
            ],
            2 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 2,
                5 => 4,
                6 => 5,
                7 => 7,
                8 => 9,
                9 => 10,
                10 => 12,
                11 => 14,

            ],
            3 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
                7 => 1,
                8 => 1,
                9 => 1,
                10 => 1,
                11 => 1,
                12 => 1,
                13 => 1,
                14 => 1,
                15 => 1,
                16 => 2,
                17 => 3,
                18 => 4,
                19 => 5,
                20 => 6,
                21 => 8,
                22 => 9,
                23 => 10,
                24 => 11,
                25 => 12,
                26 => 13,
                27 => 14,
            ],
            4 => [
                0 => 1,
                1 => 1,
                2 => 3,
                3 => 6,
                4 => 9,
                5 => 11,
            ],
            5 => [
                0 => 2,
                1 => 2,
                2 => 2,
                3 => 2,
                4 => 2,
                5 => 2,
                6 => 5,
                7 => 8,
                8 => 11,

            ],
            6 => [
                0 => 1,
                1 => 2,
                2 => 3,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
                7 => 6,
                8 => 7,
                9 => 8,
                10 => 8,
                11 => 9,
                12 => 10,
                13 => 11,
                14 => 11,
                15 => 12,
                16 => 13,
                17 => 13,
                18 => 14,
                19 => 15,
                20 => 15,
                21 => 16,
            ],
            7 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
                7 => 1,
                8 => 1,
                9 => 1,
                10 => 1,
                11 => 1,
                12 => 1,
                13 => 1,
                14 => 2,
                15 => 3,
                16 => 4,
                17 => 5,
                18 => 7,
                19 => 8,
                20 => 9,
                21 => 10,
                22 => 11,
                23 => 12,
                24 => 13,
            ],
        ];

        return $data;
    }

    public static function getScaledScoreAgeBracket_C()
    {

        $data = [
            1 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
                7 => 1,
                8 => 1,
                9 => 1,
                10 => 1,
                11 => 4,
                12 => 7,
                13 => 11,

            ],

            2 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 3,
                7 => 5,
                8 => 7,
                9 => 8,
                10 => 10,
                11 => 12,
            ],

            3 => [
                0 => 2,
                1 => 2,
                2 => 2,
                3 => 2,
                4 => 2,
                5 => 2,
                6 => 2,
                7 => 2,
                8 => 2,
                9 => 2,
                10 => 2,
                11 => 2,
                12 => 2,
                13 => 2,
                14 => 2,
                15 => 2,
                16 => 2,
                17 => 2,
                18 => 2,
                19 => 2,
                20 => 3,
                21 => 4,
                22 => 6,
                23 => 7,
                24 => 9,
                25 => 10,
                26 => 12,
                27 => 13,
            ],
            4 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 4,
                4 => 8,
                5 => 11,
            ],
            5 => [
                0 => 5,
                1 => 5,
                2 => 5,
                3 => 5,
                4 => 5,
                5 => 5,
                6 => 5,
                7 => 5,
                8 => 11,
            ],
            6 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
                7 => 1,
                8 => 1,
                9 => 1,
                10 => 2,
                11 => 3,
                12 => 4,
                13 => 5,
                14 => 6,
                15 => 7,
                16 => 8,
                17 => 9,
                18 => 10,
                19 => 11,
                20 => 12,
                21 => 13,
            ],
            7 => [
                0 => 1,
                1 => 1,
                2 => 1,
                3 => 1,
                4 => 1,
                5 => 1,
                6 => 1,
                7 => 1,
                8 => 1,
                9 => 1,
                10 => 1,
                11 => 1,
                12 => 1,
                13 => 1,
                14 => 1,
                15 => 1,
                16 => 2,
                17 => 3,
                18 => 5,
                19 => 6,
                20 => 7,
                21 => 9,
                22 => 10,
                23 => 11,
                24 => 13,
            ],

        ];

        return $data;
    }

    public static function getStandardScore($total_scaled_score)
    {
        $data = [
            29 => 37,
            30 => 38,
            31 => 40,
            32 => 41,
            33 => 43,
            34 => 44,
            35 => 45,
            36 => 47,
            37 => 48,
            38 => 50,
            39 => 51,
            40 => 53,
            41 => 54,
            42 => 56,
            43 => 57,
            44 => 59,
            45 => 60,
            46 => 62,
            47 => 63,
            48 => 65,
            49 => 66,
            50 => 67,
            51 => 69,
            52 => 70,
            53 => 72,
            54 => 73,
            55 => 75,
            56 => 76,
            57 => 78,
            58 => 79,
            59 => 81,
            60 => 82,
            61 => 84,
            62 => 85,
            63 => 86,
            64 => 88,
            65 => 89,
            66 => 91,
            67 => 92,
            68 => 94,
            69 => 95,
            70 => 97,
            71 => 98,
            72 => 100,
            73 => 101,
            74 => 103,
            75 => 104,
            76 => 105,
            77 => 107,
            78 => 108,
            79 => 110,
            80 => 111,
            81 => 113,
            82 => 114,
            83 => 116,
            84 => 117,
            85 => 119,
            86 => 120,
            87 => 122,
            88 => 123,
            89 => 124,
            90 => 126,
            91 => 127,
            92 => 129,
            93 => 130,
            94 => 132,
            95 => 133,
            96 => 135,
            97 => 136,
            98 => 138,

        ];

        if ($total_scaled_score < 29) {
            $standard_score = 29;
        }

        if ($total_scaled_score > 98) {
            $standard_score = 138;
        }

        if ($total_scaled_score >= 29 && $total_scaled_score <= 98) {
            $standard_score = $data[$total_scaled_score];
        }

        return $standard_score;
    }
}
