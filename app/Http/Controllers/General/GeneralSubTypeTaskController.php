<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\CvSubTypeTask;

class GeneralSubTypeTaskController extends Controller {

    //*** Funcion para consultar ***//
    public function consultSubTypeTask() {

        return CvSubTypeTask::orderBy("id", "desc")->get();
    }

    // *** Indicar el sub tipo de la tarea de acuerdo al tipo de la tarea *** //

    public function subTypeTask($rol, $typeTask, $taskSubType) {

        // --- Deacuerdo al rol del usuario se le indica el sub tipo a la tarea --- //

        $valueSubType = null;

        switch ($rol) {

            /*
             * Determinar el rol del usuario 
             */

            // --- Rol coordinador de guarda cuenca --- //
            case 3:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:
                        switch ($taskSubType) {

                            case 3:
                                $valueSubType = 1;
                                break;

                            case 14:
                                $valueSubType = 22;
                                break;

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 4;
                                break;
                        }
                        break;

                    case 3:

                        switch ($taskSubType) {

                            case 0:
                                $valueSubType = 1;
                                break;
                        }

                        break;
                }
                break;

            // --- Rol guarda cuenca --- //

            case 4:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:

                        switch ($taskSubType) {

                            case 4:
                                $valueSubType = 5;
                                break;

                            case 32:
                                $valueSubType = 33;
                                break;
                        }
                        break;

                    case 3:

                        switch ($taskSubType) {

                            case 1:
                                $valueSubType = 2;
                                break;
                        }

                        break;
                }

                break;

            // --- Rol sig --- //

            case 6:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:

                        switch ($taskSubType) {

                            case 12:
                                $valueSubType = 14;

                                break;

                            case 11:
                                $valueSubType = 13;

                                break;

                            case 5:
                                $valueSubType = 6;

                                break;

                            case 15:

                                $valueSubType = 24;
                                break;

                            case 29:

                                $valueSubType = 14;
                                break;
                        }

                        break;
                    case 3:

                        $valueSubType = 14;

                        break;
                }

                break;

            // --- Rol equipo de seguimiento --- //

            case 7:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:

                        switch ($taskSubType) {

                            case 6:
                                $valueSubType = 15;

                                break;

                            case 5:

                                $valueSubType = 6;
                                break;

                            case 11:

                                $valueSubType = 13;
                                break;

                            case 13:

                                $valueSubType = 15;
                                break;
                        }
                        break;
                }

                break;

            // --- Rol equipo de administrativo --- //

            case 2:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {
                    case 1:

                        switch ($taskSubType) {

                            case 20:
                                $valueSubType = 16;
                                break;
                        }
                        break;

                    case 3:


                        switch ($taskSubType) {

                            case 2:
                                $valueSubType = 3;
                                break;

                            case 10:
                                $valueSubType = 3;
                                break;
                        }
                        break;
                }

                break;

            // --- Rol equipo de juridico --- //

            case 8:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:

                        switch ($taskSubType) {

                            case 22:
                                $valueSubType = 20;
                                break;

                            case 28:
                                $valueSubType = 29;
                                break;
                        }
                        break;

                    case 3:
                        $valueSubType = 10;
                        break;
                }

                break;

            // --- Rol equipo de restauracion de buenas practicas --- //

            case 9:
            case 15:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:
                        switch ($taskSubType) {

                            case 20:
                                $valueSubType = 21;
                                break;

                            case 24:
                                $valueSubType = 25;
                                break;

                            case 27:
                                $valueSubType = 28;
                                break;

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 4;
                                break;
                        }
                        break;

                    case 3:

                        switch ($taskSubType) {

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 1;
                                break;
                        }

                        break;
                }

                break;

            // --- Rol equipo de recurso hidrico --- //

            case 10:
            case 16:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {
                    case 1:
                        switch ($taskSubType) {

                            case 20:
                                $valueSubType = 21;
                                break;

                            case 24:
                                $valueSubType = 25;
                                break;

                            case 27:
                                $valueSubType = 28;
                                break;

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 4;
                                break;
                        }
                        break;

                    case 3:

                        switch ($taskSubType) {

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 1;
                                break;
                        }

                        break;
                }

                break;

            // --- Rol equipo de financiero --- //

            case 11:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:

                        switch ($taskSubType) {

                            case 17:
                                $valueSubType = 19;
                                break;

                            case 26:
                                $valueSubType = 27;
                                break;
                        }

                        break;
                }

                break;

            // --- Rol equipo de direccion --- //

            case 12:

                //--- Validar las acciones de acuerdo al tipo de tarea ---//

                switch ($typeTask) {

                    case 1:

                        switch ($taskSubType) {

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 4;
                                break;

                            case 25:
                                $valueSubType = 26;

                                break;

                            case 16:
                                $valueSubType = 21;

                                break;
                        }

                        break;

                    case 3:

                        switch ($taskSubType) {

                            //--- Cuando no se encuentra sub tipo ---//

                            case 0:
                                $valueSubType = 1;
                                break;
                        }

                        break;
                }

                break;

            // --- Rol equipo de comunicaciones --- //

            case 13:

                break;

            default :

                return "El usuario no cuenta con el rol indicado para seleccionar un sub tipo de tarea";
        }

        return $valueSubType;
    }

    // *** Cambiar los subtipos de tarea cuando se regresa la tarea ***//

    public function changeSubTypeTask($subType, $user_id) {

        // --- Deacuerdo al rol del usuario se le indica el sub tipo a la tarea --- //

        switch ($subType) {

            // --- Cambiar estado para tipos de tarea de medicion de predio --- //

            case 5:
                $valueSubType = 4;
                break;

            // --- Cambiar estado de aprobar en validacion a solicitud de edición de mapa en verificación --- //

            case 6:

                switch ($user_id) {
                    case 7:

                        $valueSubType = 11;

                        break;
                }
                break;

            // --- Cambiar estado para tipos de tarea de encuesta --- //

            case 2:
                $valueSubType = 1;
                break;

            // --- Cambiar estado validar cerificado a carga de certificado --- //

            case 9:
                $valueSubType = 8;
                break;

            // --- Cambiar estado validar aprobar validacion con edicion en sig a aprobar en validacion --- //

            case 11:
                $valueSubType = 6;
                break;

            // --- Cambiar estado validar aprobar validacion con actualizacion en sig a aprobar validacion con edicion en sig --- //

            case 13:
                $valueSubType = 11;
                break;

            // --- Cambiar estado validar aprobar validacion con edicion en sig a cargar mapa de verificacion --- //

            case 15:
                $valueSubType = 11;
                break;

            // --- Cambiar estado edicion minuta con edicion en coordinador de guardacuenca a edicion mapa presupuesto en sig --- //

            case 14:
                $valueSubType = 12;
                break;

            // --- Cambiar estado  aprobacion de presupuesto en financiero presupues rechazada desde financiero a direccion--- //

            case 19:
                $valueSubType = 18;
                break;

            // --- Cambiar estado de validacion minuta juridico a Generación minuta coordinación guardacuencas --- //

            case 22:
                $valueSubType = 14;
                break;

            // --- Cambiar estado de Validacion minuta administrativo a Validacion minuta juridico --- //

            case 20:
                $valueSubType = 22;
                break;

            // --- Cambiar estado de Visualizacion minuta Direccion a Validacion minuta administrativo --- //

            case 16:
                $valueSubType = 20;
                break;

            // --- Cambiar estado de Firma minuta Propietario coordinador GuardaCuenca a Visualizacion minuta Direccion --- //

            case 21:
                $valueSubType = 16;
                break;

            // --- Cambiar estado de Firma minuta Propietario GuardaCuenca o validacion a Firma minuta Propietario coordinador GuardaCuenca --- //

            case 23:
                $valueSubType = 21;
                break;

            // --- Cambiar estado de Aprobación de dirección presupuesto a Aprobación de coordinacion presupuesto --- //

            case 25:
                $valueSubType = 24;
                break;

            default :

                return "El usuario no cuenta con el rol indicado para seleccionar un sub tipo de tarea";
        }

        return $valueSubType;
    }

}
