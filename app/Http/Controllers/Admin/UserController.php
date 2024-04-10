<?php

namespace App\Http\Controllers\Admin;

use App\CvRole;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralEmailController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function createUser(Request $data)
    {

        DB::beginTransaction();

        if (User::where('email', $data->email)->exists()) {
            return [
                "message" => 'El correo ya existe',
                "code" => 500,
            ];
        }

        $passwordRandom = Str::random(8);

        $rol = CvRole::find($data->rol_id);
        $user = new User();
        $user->names = $data->names;
        $user->name = $rol->name;
        $user->last_names = $data->last_names;
        $user->email = $data->email;
        $user->password = bcrypt($passwordRandom);
        $user->state = 0;
        $user->role_id = $rol->id;

        if ($user->save()) {

            $view = "emails.task_assigned";
            $emailController = new GeneralEmailController();
            $infoEmail = array(
                "email" => $data->email,
                "subject" => "Registro de usuario",
                "title" => "Hola",
                "type" => $data->names . " " . $data->last_names,
                "description" => "Te han registrado en la plataforma de cuenca verde como " . $rol->name . " con el correo " . $data->email . " y la contraseña " . $passwordRandom,
            );

            $emailController->sendEmail($view, $infoEmail);
            DB::commit();
            return [
                "message" => 'Registro de usuario',
                "code" => 200,
            ];
        } else {
            DB::rollback();
        }
    }

    public function allRole()
    {
        return CvRole::all();
    }

    public function coordinatorRole()
    {
        return CvRole::whereIn('id', [9, 10, 13, 15, 16])->get();
    }

    public function allUsers()
    {
        $users = User::all();
        foreach ($users as $userdetail) {
            unset($userdetail['password']);
            $userdetail->role;
        }
        return $users;
    }

    public function detailUser($id_user)
    {
        $user = User::find($id_user);
        unset($user['password']);
        $user->role;
        return $user;
    }

    public function updateUser(Request $data)
    {
        $rol = CvRole::find($data->rol_id);
        $user = User::find($data->user_id);
        $user->names = $data->names;
        $user->name = $rol->name;
        $user->last_names = $data->last_names;
        $user->email = $data->email;
        $user->state = 0;
        $user->role_id = $rol->id;
        $user->save();
        return [
            "message" => 'Actualizacion de usuario',
            "code" => 200,
        ];
    }

}
