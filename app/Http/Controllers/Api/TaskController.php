<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralTaskController;

class TaskController extends Controller {

    //--- Listado de las tareas por usuario autenticado ---//
    public function listTasks() {

        // --- Instancia clase para obtener las tareas del usuario logueado o por rol --- //

        $generalTaskController = new GeneralTaskController();

        return $generalTaskController->listTasksByRol();
    }

}
