<?php

use Illuminate\Database\Seeder;
use App\CvProgramByProject;

class CvProgramByProjectTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            [
                "project_id" => 1,
                "program_id" => 1
            ],
            [
                "project_id" => 2,
                "program_id" => 1
            ],
            [
                "project_id" => 3,
                "program_id" => 2
            ],
            [
                "project_id" => 4,
                "program_id" => 2
            ],
            [
                "project_id" => 5,
                "program_id" => 2
            ],
            [
                "project_id" => 6,
                "program_id" => 3
            ],
            [
                "project_id" => 7,
                "program_id" => 4
            ],
        ];

        CvProgramByProject::insert($data);
    }

}
