<?php

use Illuminate\Database\Seeder;
use App\CvProjectByActivity;

class CvProjectByActivityTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $projectActvity = [
            [
                'activity_id' => '1',
                'project_id' => '1'
            ],
            [
                'activity_id' => '2',
                'project_id' => '2'
            ],
            [
                'activity_id' => '3',
                'project_id' => '2'
            ],
            [
                'activity_id' => '4',
                'project_id' => '2'
            ],
            [
                'activity_id' => '5',
                'project_id' => '3'
            ],
            [
                'activity_id' => '6',
                'project_id' => '3'
            ],
            [
                'activity_id' => '7',
                'project_id' => '4'
            ],
            [
                'activity_id' => '8',
                'project_id' => '4'
            ],
            [
                'activity_id' => '9',
                'project_id' => '4'
            ],
            [
                'activity_id' => '10',//
                'project_id' => '5'
            ],
            [
                'activity_id' => '11',
                'project_id' => '5'
            ],
            [
                'activity_id' => '12',//
                'project_id' => '6'
            ],
            [
                'activity_id' => '13',
                'project_id' => '6'
            ],
            [
                'activity_id' => '14',
                'project_id' => '7'
            ],
            [
                'activity_id' => '15',
                'project_id' => '7'
            ],
            [
                'activity_id' => '16',
                'project_id' => '7'
            ],
        ];

        CvProjectByActivity::insert($projectActvity);
    }

}
