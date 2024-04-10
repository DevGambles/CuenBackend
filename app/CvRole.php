<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\CvRoleEntitiesPermission;

class CvRole extends Model {

    protected $table = "cv_role";

    //*** Consultar los roles que se requieran por el usuario autenticado para tareas***//
    public static function consultCvRolesFilter($roles) {

        return CvRole::whereIn('id', $roles)->get();
    }

    //*** Consultar rol si es equipo seguimiento o guarda cuenca ***//
    public static function consultCvRoleGuardTeam() {

        return CvRole::whereIn('id', [4, 7])->get();
    }

    //*** Relaciones ***//
    public function user() {
        return $this->hasMany(User::class, 'role_id');
    }

    public function rolEntityPermission() {
        return $this->hasMany(CvRoleEntitiesPermission::class, 'role_id');
    }

    //*** Consultar todos los roles ***//
    public static function consultAllCvRole() {

        $roles = CvRole::get();

        if (empty($roles)) {
            return [
                "message" => "No se encuentran roles en el sistema",
                "code" => 500
            ];
        }

        return $roles;
    }

}
