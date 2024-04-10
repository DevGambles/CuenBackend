<?php

use Illuminate\Database\Seeder;
use App\User;
use Faker\Factory as Faker;

class CvUserDefaultTableSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {

        $faker = Faker::create();

        //*** Crear usuario por defecto ***//

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Super administrador',
            'email' => 'sadmoncvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 1,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Administrativo',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 2,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Coordinador',
            'email' => 'coordinadorcuencacvmdll@yahoo.com',
            'password' => bcrypt('12345678'),
            'role_id' => 3,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameFemale,
            'last_names' => $faker->lastName,
            'name' => 'Guarda cuenca',
            'email' => 'guardacuencaverdecvmdll@hotmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 4,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameFemale,
            'last_names' => $faker->lastName,
            'name' => 'Contratista',
            'email' => 'contratistacvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 5,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Sig',
            'email' => 'sigcvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 6,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Equipo seguimiento',
            'email' => 'equiposeguimientocvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 7,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Juridico',
            'email' => 'juridicocvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 8,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameFemale,
            'last_names' => $faker->lastName,
            'name' => 'Restauración y buenas practicas',
            'email' => 'restauracioncvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 9,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);
        User::insert([
            'names' => $faker->firstNameFemale,
            'last_names' => $faker->lastName,
            'name' => 'Recurso hídrico',
            'email' => 'recursohidricocvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 10,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);
        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Financiero',
            'email' => 'financierocvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 11,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);
        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Direccion',
            'email' => 'direccioncvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 12,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);
        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Comunicacion',
            'email' => 'comunicacioncvmdll@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 13,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameFemale,
            'last_names' => $faker->lastName,
            'name' => 'Prueba administrativo',
            'email' => 'administrativotest@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 2,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameFemale,
            'last_names' => $faker->lastName,
            'name' => 'Guarda cuenca 2',
            'email' => 'guardacuenca2@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 4,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Guarda cuenca 3',
            'email' => 'guardacuenca3@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 4,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Tecnico de Monitoreo',
            'email' => 'tecnicomonitoreo@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 14,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Apoyo Restauracion',
            'email' => 'apoyorestauracion@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 15,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Apoyo Recurso Hidrico',
            'email' => 'apoyorecursohidrico@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 16,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);

        User::insert([
            'names' => $faker->firstNameMale,
            'last_names' => $faker->lastName,
            'name' => 'Apoyo comunicacion',
            'email' => 'apoyocomunicacion@gmail.com',
            'password' => bcrypt('12345678'),
            'role_id' => 17,
            'created_at' => date('Y-m-d H:m:s'),
            'updated_at' => date('Y-m-d H:m:s')
        ]);
    }

}
