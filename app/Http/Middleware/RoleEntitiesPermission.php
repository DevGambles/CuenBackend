<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\CvRole;
use App\CvEntitiesPermission;

class RoleEntitiesPermission {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        //--- Validar que si el usuario ha sido eliminado ---//

        if (Auth::user()->state == 1) {

            return response("El usuario ha sido eliminado del sistema");
        }

        //Saber si cuenta con el permiso
        $permission_active = 1;

        //Obtener el indice principal de la url
        $urlEntity = strstr($request->path(), '/', true);

        if ($urlEntity == false) {
            $urlEntity = $request->path();
        }

        //Consultar la relacion de rol con la tabla de entidades permisos

        if (count(CvRole::find(Auth::user()->role_id)->rolEntityPermission) != 0) {

            //Obtener la informacion de la tabla de entidades - permisos
            $rolEntityPermission = CvRole::find(Auth::user()->role_id)->rolEntityPermission;

            foreach ($rolEntityPermission as $data) {

                //Obtener los permisos
                $permission = CvEntitiesPermission::find($data->entities_permission_id)->permission;
                //Obtener las entidades
                $entity = CvEntitiesPermission::find($data->entities_permission_id)->entities;


                //Validar si el usuario tiene permiso de acceder a esta ruta por medio del permiso y la entidad
                if ($urlEntity == $entity->name && $permission->name == $request->method()) {
                    $permission_active = 0;
                }
            }
        }

        //Validar si cuenta con el permiso para realizar la accion
        if ($permission_active == 0) {
            return $next($request);
        } else {
            return $next($request);
            //return response("El usuario con este rol no cuenta con permisos para realizar esta accion");
        }

        return response("El usuario no cuenta con un rol asignado");
    }

}
