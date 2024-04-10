<?php

use Illuminate\Database\Seeder;
use App\CvQuota;

class CvUserQuoteTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $data = [
            [
                'quota' => '10',
                'user_id' => 4,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'quota' => '5',
                'user_id' => 15,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ],
            [
                'quota' => '8',
                'user_id' => 16,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]/*,
            [
                'quota' => '15',
                'user_id' => 17,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]*/
        ];
        CvQuota::insert($data);
    }

}
