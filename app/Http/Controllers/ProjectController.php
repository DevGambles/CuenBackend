<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use Illuminate\Support\Facades\DB;
use App\CvProject;
use App\CvProgram;
use App\User;
use App\CvTask;
use Exception;
use App\CvProjectByActivity;

class ProjectController extends Controller {

    //*** Consultar proyectos en general o especificos de acuerdo al rol del usuario autenticado ***//

    public function index() {

        switch ($this->userLoggedInRol()) {
            case 1:
                $listsProjects = CvProject::where('state', 0)->orderBy('id', 'desc')->get();
                break;
            case 3:
                $listsProjects = CvProject::where('state', 0)->orderBy('id', 'desc')->get();
                break;

            default:

                $user_tasks = User::find($this->userLoggedInId())->task;

                foreach ($user_tasks as $task) {
                    $listsProjects = CvTask::find($task->id)->project;
                }
                break;
        }

        return $listsProjects;
    }

    //*** Consultar tipo de proyecto para la realización de un nuevo registro ***//

    public function create() {

        $typeProject = CvProgram::consultCvProgram();

        return $typeProject;
    }

    //*** Registrar un nuevo proyecto ***//

    public function store(ProjectRequest $request) {

        //--- Registrar proyecto ---//

        $newProject = new CvProject();

        $newProject->program_id = $request->program_id;
        $newProject->name = $request->project_name;
        $newProject->description = $request->project_description;

        $error = null;

        DB::beginTransaction();

        try {

            $newProject->save();


            foreach ($request->activities as $value) {

                //--- Registrar las actividades del proyecto ---//

                $newProjectByActivity = new CvProjectByActivity();

                $newProjectByActivity->project_id = $newProject->id;
                $newProjectByActivity->activity_id = $value;
                $newProjectByActivity->save();
            }

            DB::commit();

            $success = true;
        } catch (Exception $e) {

            $success = false;
            $error = $e->getMessage();

            DB::rollback();
        }
        if ($success) {
            return [
                "message" => "Registro exitoso",
                "code" => 200
            ];
        }

        return $error;
    }

    //*** Mostrar la información de un proyecto en especifico ***//

    public function show($id) {

        $project = CvProject::find($id);
        return $project->load("typeProject");
    }

    //*** Ruta por defecto de laravel - retorna error 404 ***//

    public function edit($id) {

        abort(404);
    }

    //*** Actualizar información de un proyecto en especifico ***//

    public function update(Request $request, $id) {

        $updateProject = CvProject::find($id);

        $updateProject->type_project_id = $request->type_project;
        $updateProject->name = $request->name_project;
        $updateProject->description = $request->description_project;

        if ($updateProject->save()) {
            return "Proyecto actualizado.";
        }
    }

    //*** Ruta por defecto de laravel - retorna error 404 ***//

    public function destroy($id) {

        abort(404);
    }

}
