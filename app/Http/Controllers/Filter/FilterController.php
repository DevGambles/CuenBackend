<?php

namespace App\Http\Controllers\Filter;

use App\CvProcess;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FilterController extends Controller
{

    /**
     * Filtro para informacion de la vista de tareas
     */

    public function filterTasksGeneral(Request $request)
    {

        switch ($request->type_report) {

            //--- Nombre de procedimiento ---//
            case "process":
                /**
                 * Consultar todas las tareas a partir del nombre del procedimiento
                 */
                $taskGeneralProcess = $this->filterTaskGeneralProcess($request->name_process);
                $taskGeneralProcessOpen = $this->filterTaskGeneralProcessOpen($request->name_process);
                return array_merge($taskGeneralProcess, $taskGeneralProcessOpen);
                break;

            //--- Fechas ---//
            case "process":
                /**
                 * Consultar todas las tareas a partir del nombre del procedimiento
                 */
                $taskGeneralProcess = $this->filterTaskGeneralProcess($request->name_process);
                $taskGeneralProcessOpen = $this->filterTaskGeneralProcessOpen($request->name_process);
                return array_merge($taskGeneralProcess, $taskGeneralProcessOpen);
                break;

        }
    }

    /**
     * Tareas general medicion o encuesta
     */

    public function filterTaskGeneralProcess($nameProcess)
    {

        $process = CvProcess::where('name', 'like', '%' . $nameProcess . '%')->get();

        $infoTasks = array();

        foreach ($process as $processItem) {

            $tasksGeneral = $processItem->processByTasks;

            if (count($tasksGeneral) > 0) {

                foreach ($tasksGeneral as $itemTaskGeneral) {

                    /**
                     * Infor del procedimiento presonalizado
                     */
                    $itemTaskGeneral["process"] = array(
                        "id" => $processItem->id,
                        "name" => $processItem->name,
                        "type_process" => $processItem->type_process,
                        "potential_property_id" => $processItem->potential_property_id,
                        "description" => $processItem->description,
                    );

                    /**
                     * Estado, sub tipo de la tarea
                     */

                    $itemTaskGeneral["task_status_name"] = $itemTaskGeneral->taskStatus->name;

                    $itemTaskGeneral["open"] = false;

                    unset($itemTaskGeneral["task_status"]);
                    unset($itemTaskGeneral["pivot"]);

                    /**
                     * Info de las tareas
                     */
                    array_push($infoTasks, $itemTaskGeneral);

                }
            }
        }

        return $infoTasks;

    }

    /**
     * Tareas abiertas
     */

    public function filterTaskGeneralProcessOpen($nameProcess)
    {

        $process = CvProcess::where('name', 'like', '%' . $nameProcess . '%')->get();

        $infoTaskOpen = array();

        foreach ($process as $processItem) {

            $tasksOpen = $processItem->taskOpenProcess;

            if (count($tasksOpen) > 0) {
                foreach ($tasksOpen as $taskOpenItem) {

                    /**
                     * Infor del procedimiento presonalizado
                     */
                    $taskOpenItem["process"] = array(
                        "id" => $processItem->id,
                        "name" => $processItem->name,
                        "type_process" => $processItem->type_process,
                        "potential_property_id" => $processItem->potential_property_id,
                        "description" => $processItem->description,
                    );

                    $taskOpenItem["open"] = true;

                    /**
                     * Estado, sub tipo de la tarea
                     */

                    $taskOpenItem["task_status_name"] = $taskOpenItem->status->name;

                    unset($taskOpenItem["status"]);

                    /**
                     * Info de las tareas
                     */
                    array_push($infoTaskOpen, $taskOpenItem);
                }
            }
        }

        return $infoTaskOpen;
    }

}
