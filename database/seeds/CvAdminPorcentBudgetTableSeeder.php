<?php

use Illuminate\Database\Seeder;
use App\CvAdminPorcentBudget;

class CvAdminPorcentBudgetTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $info = [
            [   
                'administration' => 20,
                'utility' => 5,
                'iva' => 19,
                'created_at' => date("Y-m-d H:i:s") ,
                'updated_at' => date("Y-m-d H:i:s") 
            ]
        ];
        
        CvAdminPorcentBudget::insert($info);
    }
}
