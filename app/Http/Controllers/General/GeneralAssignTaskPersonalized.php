<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\User;
use App\CvTask;
use App\CvTaskUser;

class GeneralAssignTaskPersonalized extends Controller {
    /*
     * Asignar tarea a mas de un usuario en este caso a los siguientes roles para el siguiente sub tipo:
     * 
     * Sub tipo: Aprobación de coordinación en presupuesto
     * Roles: 
     * 
     * 1. Apoyo de coordinación de restauracion y buenas practicas
     * 2. Apoyo coordinación de gestión de recurso hidrico
     * 
     */

    public function assignTaskBudgetRbpRh($task_id, $user_id) {

        $task = CvTask::find($task_id);

        if (!empty($task)) {

            //--- Asginar tarea al apoyo de coordinaciones ---//

            if ($task->task_sub_type_id == 15 || $task->task_sub_type_id == 26) {

                $usersRoles = User::whereIn('role_id', [10])->get();

                //--- Asignar la tarea a los usuarios que cuentan con ese rol ---//

                foreach ($usersRoles as $user) {

                    $taskByUser = new CvTaskUser();

                    $taskByUser->user_id = $user->id;
                    $taskByUser->task_id = $task->id;

                    //--- Validar que el usuario no cuente con esa tarea ---//
                    $consultTaskByUser = CvTaskUser::where("user_id", $user->id)->where("task_id", $task->id)->exists();

                    if (!$consultTaskByUser) {
                        $taskByUser->save();
                    }
                }
            } else {

                //--- Borrar la tarea a los demas usuarios y asignarla nada mas a quien la aprobo ---//

                $usersRoles = CvTaskUser::whereIn('user_id', [3, 9, 10])->get();

                foreach ($usersRoles as $userRole) {
                    if ($userRole->user_id != $user_id) {
                        $userRole->delete();
                    }
                }
            }
        }
    }

}
