<?php

use Illuminate\Database\Seeder;
use App\CvTypeFile;

class CvTypeFilesSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $data = [
            [
                'name' => 'OFAC',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'name' => 'Contratista',
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]
        ];
        CvTypeFile::insert($data);
    }

}
