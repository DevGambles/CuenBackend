<?php

namespace App\Http\Controllers;

use App\CvPotentialProperty;
use App\CvProcess;
use App\CvProperty;
use App\CvTask;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;

class PropertyController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    //*** Consultar la informacion de la encuesta ***//

    public function consultProperty($id) {

        $task = CvTask::find($id)->property;
        $property = CvProperty::find($task->id);
        $infoProperty = $property->load("properyCorrelation")->load("contactsProperty");

        return $infoProperty;
    }

    //*** Consultar informacion de la encuesta - Filtrar informacion especifica ***//

    public function consultPropertyInfo($id) {

        $task = CvTask::find($id);

        if (!empty($task)) {
            $task->property;
            if (!empty($task->property)) {
                return $task->property->info_json_general;
            } else {
                return [
                    "message" => "La tarea no cuenta con encuesta",
                    "response_code" => 200
                ];
            }
        } else {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200
            ];
        }
    }

    public function getPropertiesByProcess(Request $data) {
        
        $filterData = $data->all();

        $collectionResult = new Collection();
        $modelProcess = CvProcess::all();

        foreach ($modelProcess as $process) {
            $signMinuta = null;
            $signLetterIntention = null;
            $status = 'Documentos del predio han sido aprobados';
            $municipality = null;
            $embalse = null;

            //*** Obtener la actividad ***//

            $activitiesArray = array();

            foreach ($process->processByProjectByActivity as $proccessActivity) {
                array_push($activitiesArray, (string) $proccessActivity->name);
            }

            if ($process->potential_property_id !== null & ($process->type_process == null || $process->type_process == 'gestion')) {
                if ($process->potentialProperty->potential_sub_type_id == 4) {
                    $propertyName = $process->potentialProperty->property_name;
                    $model = $process->processByTasks->where('task_sub_type_id', '>=', 4)->first();
                    if ($model) {
                        $status = $model->taskSubType->name;
                    }


                    if ($process->processByTasks[0]->task_sub_type_id == 33) {
                        $signMinuta = $process->processByTasks[0]->created_at;
                    }

                    $arrPool = json_decode($process->potentialProperty->potentialPropertyPoll->info_json_general, true);

                    if (array_key_exists('municipality', $arrPool)) {
                        $municipality = $arrPool['municipality'];
                    }

                    if (array_key_exists('property_reservoir', $arrPool)) {
                        $embalse = $arrPool['property_reservoir'];
                    }

                    if ($process->letterIntention != null) {
                        $letterIntention = $process->letterIntention;
                        $formArrLetterIntention = json_decode($letterIntention->form_letter, true);
                        if (array_key_exists('property_visit_date', $formArrLetterIntention)) {
                            $signLetterIntention = $formArrLetterIntention['property_visit_date'];
                        }
                    }

                    if (!$process->poolByProcess->isEmpty()) {
                        $status = $this->getStateTasksExecution($process);
                    }

                    $process['municipality'] = $municipality;
                    $process['propertyReservoir'] = $embalse;
                    $process['signMinuta'] = $signMinuta;
                    if ($signLetterIntention != null) //georgi add
                        $process['signLetterIntention'] = $signLetterIntention['year'] . '-' . $signLetterIntention['month'] . '-' . $signLetterIntention['day'];
                    $process['status'] = $status;
                    $process['property_name'] = $propertyName;
                    $process['relations'] = [];

                    //*** Activies ***//
                    $process['activies'] = implode(", ", $activitiesArray);
                    $collectionResult->add($process);
                }
            }
        }

        $collectionResult = $this->sedFilter($filterData, $collectionResult);

        $arrResult = Array();
        foreach ($collectionResult as $item) {
            array_push($arrResult, $item);
        }

        return $arrResult;
    }

    /**
     * @param $process
     * @return string
     */
    private function getStateTasksExecution($process) {
        $count = 0;
        $countTasksExecution = 0;
        $boolExecuted = true;

        foreach ($process->poolByProcess as $poolByProcess) {
            if ($poolByProcess->poolActionsByUserContractor !== null) {
                $count += 1;
                if ($poolByProcess->poolActionsByUserContractor->taskExecutionByUser !== null) {
                    $countTasksExecution += 1;

                    foreach ($poolByProcess->poolActionsByUserContractor->taskExecutionByUser as $item) {
                        if ($item->taskExecution->task_open_sub_type_id !== 17) {
                            $boolExecuted = false;
                        }
                    }
                }
            }
        }
        if ($process->poolByProcess->count() !== $count) {
            $status = 'Por contratar';
        } else {
            $status = 'Contratado';
        }

        if ($countTasksExecution !== 0) {
            if ($process->poolByProcess->count() !== $countTasksExecution) {
                $status = 'En ejecución';
            } else {
                if ($boolExecuted) {
                    $status = 'Ejecutado';
                }
            }
        }
        return $status;
    }

    /**
     * @param $filterData
     * @param $collectionResult
     * @return mixed
     */
    private function sedFilter($filterData, $collectionResult) {
        if ($filterData['id'] !== "0" & $filterData['id'] !== "null" & $filterData['id'] !== null) {
            $collectionResult = $collectionResult->filter(function ($value, $key) use ($filterData) {
                return $value->id == $filterData['id'];
            });
        }
        if ($filterData['name'] !== "null" & $filterData['name'] !== null) {
            $collectionResult = $collectionResult->filter(function ($value, $key) use ($filterData) {
                return stripos($value->property_name, $filterData['name']) !== false;
            });
        }
        if ($filterData['status'] !== "0") {
            $collectionResult = $collectionResult->filter(function ($value, $key) use ($filterData) {
                return $value->status == $filterData['status'];
            });
        }
        return $collectionResult;
    }

}
