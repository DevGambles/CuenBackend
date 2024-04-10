<?php

namespace App\Http\Controllers\Api;

use App\CvOtherInfoContractor;
use App\CvOtherInfoMinuteProperty;
use Illuminate\Http\Request;
use App\Http\Requests\PropertyRequest;
use App\Http\Controllers\Controller;
use App\CvProperty;
use App\CvTask;
use App\User;
use App\Http\Controllers\General\GeneralHistoryTaskController;
use App\Http\Controllers\General\GeneralSubTypeTaskController;
use App\Http\Controllers\Search\SearchAlgoliaController;
use App\Http\Controllers\General\GeneralSearchController;
use App\Http\Controllers\General\GeneralMapController;
use App\Http\Controllers\General\GeneralNotificationController;

class PropertyController extends Controller {

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function index() {
        return abort(404);
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function create() {
        return abort(404);
    }

    // *** Guardar la información de la encuesta desde movil *** //

    public function store(Request $request) {

        try {

            //--- Respuesta del registro o actualizacion ---//
            $responseProperty = [];

            //--- Instance para el registro o actualizacion de la encuesta ---//
            $instanceProperty = "";

            //--- Consultar si la tarea ya cuenta con el registro de la encuesta de un predio ---//

            $taskPropertyExist = CvTask::find($request->task_id);

            if (empty($taskPropertyExist)) {
                return [
                    "message" => "La tarea no existe en el sistema",
                    "response_code" => 200
                ];
            }

            //--- Validar que la tarea sea de encuesta ---//
            if ($taskPropertyExist->task_type_id != 3) {
                return [
                    "message" => "La tarea solicitada no es de tipo encuesta",
                    "response_code" => 200,
                ];
            }

            //--- Ingresar el tipo de tarea de acuerdo al usuario ---//

            $subTypeController = new GeneralSubTypeTaskController();
            $taskPropertyExist->task_sub_type_id = $subTypeController->subTypeTask($this->userLoggedInRol(), $taskPropertyExist->task_type_id, $taskPropertyExist->task_sub_type_id);
            $taskPropertyExist->save();

            if ($taskPropertyExist != "") {

                if ($taskPropertyExist->property_id == "") {

                    $instanceProperty = new CvProperty();
                    $responseProperty = [
                        "message" => "Registro exitoso",
                        "response_code" => 200
                    ];
                } else {

                    $instanceProperty = CvProperty::find($taskPropertyExist->property_id);
                    $responseProperty = [
                        "message" => "Registro actualizado",
                        "response_code" => 200
                    ];
                }
            } else {
                return "No existe tarea";
            }

            //--- Información general del predio ---//

            $property = json_decode($request->info_general, true);

            $instanceProperty->info_json_general = $request->info_general;
            $instanceProperty->property_correlation_id = $property["property_correlation_id"];

            if ($instanceProperty->save()) {

                // --- Guardar informacion del buscador --- //

                $this->infoSearchProperty($instanceProperty->id, $taskPropertyExist->id);

                /*
                 *  Actualizar información de la tarea del mapa en el buscador cuando se actualiza la encuesta del mismo procedimiento
                 */

                $updateSearchMapByProperty = new GeneralSearchController();
                $map_id = $updateSearchMapByProperty->updateSearchMapByProperty($instanceProperty->id);
                $generalMapController = new GeneralMapController();
                $generalMapController->infoSearchGeoJson($map_id);

                // --- Cambiar el estado de la tarea --- //

                $updateTask = CvTask::find($request->task_id);

                $updateTask->property_id = $instanceProperty->id;

                // --- Asignarle la tarea al rol de administrador --- //

                $info = array(
                    "permission_route" => "permission_poll",
                    "task_id" => $updateTask->id,
                    "task_type_id" => $updateTask->task_type_id,
                    "task_status_id" => $updateTask->task_status_id
                );

                $filterSendTask = $this->routesAutomatics($info);

                //--- Guardar el historial de la tarea ---//
                if (isset($filterSendTask["user"]) && isset($filterSendTask["task"])) {

                    if ($updateTask->save()) {

                        //--- Enviar la notificacion ---//

                        $notificationTask = new GeneralNotificationController();

                        $content = "El guarda cuenca " . User::find($filterSendTask["user"])->name . " ha realizado la medición del "
                                . "procedimiento " . $updateTask->process[0]->name . ".";

                        $notificationTask->notificationTask($updateTask, $content, $filterSendTask["user"]);

                        // --- Actualizar información de los monitoreos para el buscador enviando el Id de la tarea --- //
                        $updateSearchMapByProperty->updateSearchMonitoringByTask($updateTask->id);

                        // --- Enviar información de la tarea para registrar su historial --- //
                        $historyTask = array();

                        array_push($historyTask, array(
                            "type_task" => "Register_task_property",
                            "info" => $instanceProperty->info_json_general,
                            "task_id" => $request->task_id,
                            "user_from" => $this->userLoggedInId(),
                            "user_to" => $filterSendTask["user"]
                                )
                        );

                        // --- Enviar información al controlador en el cual va a filtrar los datos de la tarea --- //
                        $historyController = new GeneralHistoryTaskController();

                        if ($historyController->saveHistoryTask($historyTask[0]) == 200) {

                            return $responseProperty;
                        }
                    }
                } else {
                    return $filterSendTask;
                }
            }
        } catch (Exception $e) {
            return "Se ha presentado un error: " . $e->getMessage() . "\n";
        }
    }

    // *** Consultar informacion de la encuesta *** //

    public function show($id) {

        $property = CvProperty::find($id);

        if (empty($property)) {
            return [
                "message" => "La encuesta no existe en el sistema",
                "response_code" => 200
            ];
        }

        $propertyJson = json_decode($property->info_json_general, true);

        return $propertyJson;
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function edit($id) {
        return abort(404);
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function update(PropertyRequest $request, $id) {
        return abort(404);
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function destroy($id) {
        return abort(404);
    }

    //*** Consultar información del predio real de una tarea en especifico ***//

    public function consultPropertySpecific($id) {

        $property = CvTask::find($id);

        if (!empty($property)) {

            $infoCoordinate = $property->property;

            if (!empty($infoCoordinate->main_coordinate)) {
                $coordinate = explode(",", $infoCoordinate->main_coordinate);

                $info = array(
                    "name" => $property->property->property_name,
                    "lat" => $coordinate[0],
                    "lng" => $coordinate[1],
                    "info" => $property->info_json_general
                );

                return $info;
            }

            return [];
        } else {
            return [
                "message" => "La tarea no existe en el sistema",
                "response_code" => 200
            ];
        }
    }

    //*** Filtrar informacion del usuario para el buscador ***//

    public function infoSearchProperty($property_id, $task_id) {

        $property = CvProperty::find($property_id);
        $type = "Encuesta";

        //--- Instancia del modelo del buscador universal con algolia ---//

        $searchAlgoliaController = new SearchAlgoliaController();

        $propertyInfo = json_decode($property->info_json_general, true);

        //--- Validaciones para los datos con valores true o false ---//

        /*
         * General
         */

        $property_colanta_partner = ($propertyInfo["property_colanta_partner"] == true) ? "Si" : "No";
        $property_type_commercial = ($propertyInfo["property_type"]["commercial"] == true) ? "Si" : "No";
        $property_type_residential = ($propertyInfo["property_type"]["residential"] == true) ? "Si" : "No";
        $property_type_property_other = ($propertyInfo["property_type"]["property_other"] == true) ? "Si" : "No";
        $property_type_other = ($propertyInfo["property_type"]["other"] == true) ? "Si" : "No";

        /*
         *  Necesidades basicoas 
         */

        $networkAqueduct = ($propertyInfo["basic_needs"]["aqueduct_network"] == true) ? "Si" : "No";
        $networkElectrical = ($propertyInfo["basic_needs"]["electrical_network"] == true) ? "Si" : "No";
        $systemSewerage = ($propertyInfo["basic_needs"]["sewerage_system"] == true) ? "Si" : "No";

        /*
         *  Actividad economica de la propiedad
         */

        $agriculture = ($propertyInfo["economic_activity_in_the_property"]["agriculture"] == true) ? "Si" : "No";
        $chemical = ($propertyInfo["economic_activity_in_the_property"]["chemical"] == true) ? "Si" : "No";
        $commercial = ($propertyInfo["economic_activity_in_the_property"]["commercial"] == true) ? "Si" : "No";
        $dairy_cattle = ($propertyInfo["economic_activity_in_the_property"]["dairy_cattle"] == true) ? "Si" : "No";
        $dual_purpose_cattle = ($propertyInfo["economic_activity_in_the_property"]["dual_purpose_cattle"] == true) ? "Si" : "No";
        $good_practices_certification = ($propertyInfo["economic_activity_in_the_property"]["good_practices_certification"] == true) ? "Si" : "No";
        $handwork = ($propertyInfo["economic_activity_in_the_property"]["handwork"] == true) ? "Si" : "No";
        $machining = ($propertyInfo["economic_activity_in_the_property"]["machining"] == true) ? "Si" : "No";
        $porcine_farming = ($propertyInfo["economic_activity_in_the_property"]["porcine_farming"] == true) ? "Si" : "No";
        $poultry_farming = ($propertyInfo["economic_activity_in_the_property"]["poultry_farming"] == true) ? "Si" : "No";
        $recreational = ($propertyInfo["economic_activity_in_the_property"]["recreational"] == true) ? "Si" : "No";
        $residential = ($propertyInfo["economic_activity_in_the_property"]["residential"] == true) ? "Si" : "No";
        $self_consumption = ($propertyInfo["economic_activity_in_the_property"]["self_consumption"] == true) ? "Si" : "No";
        $tillage_methods = ($propertyInfo["economic_activity_in_the_property"]["tillage_methods"] == true) ? "Si" : "No";

        /*
         * Daño ambiental
         */

        $natives_logging = ($propertyInfo["environmental_damage"]["natives_logging"] == true) ? "Si" : "No";
        $wetland_desiccation = ($propertyInfo["environmental_damage"]["wetland_desiccation"] == true) ? "Si" : "No";


        /*
         * Ecosistemas naturales de la propiedad
         */

        $mountainside_forest = ($propertyInfo["natural_ecosystems_in_the_property"]["mountainside_forest"] == true) ? "Si" : "No";
        $riverbank_area = ($propertyInfo["natural_ecosystems_in_the_property"]["riverbank_area"] == true) ? "Si" : "No";
        $riverbank_forest = ($propertyInfo["natural_ecosystems_in_the_property"]["riverbank_forest"] == true) ? "Si" : "No";
        $spring = ($propertyInfo["natural_ecosystems_in_the_property"]["spring"] == true) ? "Si" : "No";

        /*
         * Estado legal de la propiedad
         */

        $possession = ($propertyInfo["property_legal_status"]["possession"] == true) ? "Si" : "No";
        $succession = ($propertyInfo["property_legal_status"]["succession"] == true) ? "Si" : "No";
        $tenant_status = ($propertyInfo["property_legal_status"]["tenant_status"] == true) ? "Si" : "No";

        /*
         * Características de vertimiento
         */

        $ar_treatment_system = ($propertyInfo["shedding_characteristics"]["ar_treatment_system"] == true) ? "Si" : "No";

        /*
         * Hay actividades agrícolas en la propiedad
         */

        $chemical_fertilizers = ($propertyInfo["shedding_characteristics"]["are_there_agricultural_activities_in_the_property"]["chemical_fertilizers"] == true) ? "Si" : "No";
        $organic_fertilizers = ($propertyInfo["shedding_characteristics"]["are_there_agricultural_activities_in_the_property"]["organic_fertilizers"] == true) ? "Si" : "No";
        $pesticides = ($propertyInfo["shedding_characteristics"]["are_there_agricultural_activities_in_the_property"]["pesticides"] == true) ? "Si" : "No";

        /*
         * Manejo químico y / o de captura de ganado
         */
        $burning = ($propertyInfo["shedding_characteristics"]["chemical_and_or_cattle_raising_handling"]["burning"] == true) ? "Si" : "No";
        $burying = ($propertyInfo["shedding_characteristics"]["chemical_and_or_cattle_raising_handling"]["burying"] == true) ? "Si" : "No";
        $delivery_to_a_collecting_entity_without_separation = ($propertyInfo["shedding_characteristics"]["chemical_and_or_cattle_raising_handling"]["delivery_to_a_collecting_entity_without_separation"] == true) ? "Si" : "No";
        $separation_and_delivery_to_a_collecting_entity = ($propertyInfo["shedding_characteristics"]["chemical_and_or_cattle_raising_handling"]["separation_and_delivery_to_a_collecting_entity"] == true) ? "Si" : "No";

        /*
         * Manejo de cosecha de desechos sólidos domesticos
         */
        $burningDomestic = ($propertyInfo["shedding_characteristics"]["domestic_solid_waste_harvesting_handling"]["burning"] == true) ? "Si" : "No";
        $harnessing = ($propertyInfo["shedding_characteristics"]["domestic_solid_waste_harvesting_handling"]["harnessing"] == true) ? "Si" : "No";
        $none = ($propertyInfo["shedding_characteristics"]["domestic_solid_waste_harvesting_handling"]["none"] == true) ? "Si" : "No";
        $recycling = ($propertyInfo["shedding_characteristics"]["domestic_solid_waste_harvesting_handling"]["recycling"] == true) ? "Si" : "No";
        $separation = ($propertyInfo["shedding_characteristics"]["domestic_solid_waste_harvesting_handling"]["separation"] == true) ? "Si" : "No";

        /*
         * La última fecha de mantenimiento del sistema de alcantarillado
         */

        $years12 = ($propertyInfo["shedding_characteristics"]["sewerage_system_last_maintenance_date"]["years12"] == true) ? "Si" : "No";
        $years23 = ($propertyInfo["shedding_characteristics"]["sewerage_system_last_maintenance_date"]["years23"] == true) ? "Si" : "No";
        $years34 = ($propertyInfo["shedding_characteristics"]["sewerage_system_last_maintenance_date"]["years34"] == true) ? "Si" : "No";

        /*
         * Información socioeconómica
         */

        $family_compensation_fund = ($propertyInfo["socio_economic_information"]["family_compensation_fund"] == true) ? "Si" : "No";
        $sisben = ($propertyInfo["socio_economic_information"]["sisben"] == true) ? "Si" : "No";

        /*
         * Importancia estratégica de la propiedad
         */

        $biodiversity_conservation = ($propertyInfo["strategic_importance_of_the_property"]["biodiversity_conservation"] == true) ? "Si" : "No";
        $carbon_bonds_sale_certification = ($propertyInfo["strategic_importance_of_the_property"]["carbon_bonds_sale_certification"] == true) ? "Si" : "No";
        $ecological_connectivity = ($propertyInfo["strategic_importance_of_the_property"]["ecological_connectivity"] == true) ? "Si" : "No";
        $high_degree_of_conservation_forest = ($propertyInfo["strategic_importance_of_the_property"]["high_degree_of_conservation_forest"] == true) ? "Si" : "No";
        $productive_water_source = ($propertyInfo["strategic_importance_of_the_property"]["productive_water_source"] == true) ? "Si" : "No";
        $sequestered_carbon = ($propertyInfo["strategic_importance_of_the_property"]["sequestered_carbon"] == true) ? "Si" : "No";
        $supply_source = ($propertyInfo["strategic_importance_of_the_property"]["supply_source"] == true) ? "Si" : "No";
        $water_quality_improvement = ($propertyInfo["strategic_importance_of_the_property"]["water_quality_improvement"] == true) ? "Si" : "No";
        $water_regulation = ($propertyInfo["strategic_importance_of_the_property"]["water_regulation"] == true) ? "Si" : "No";

        /*
         * Formas de acceso 
         */

        $can_be_reached_by_car = ($propertyInfo["ways_of_access"]["can_be_reached_by_car"] == true) ? "Si" : "No";
        $primary_road = ($propertyInfo["ways_of_access"]["primary_road"] == true) ? "Si" : "No";
        $secondary_road = ($propertyInfo["ways_of_access"]["secondary_road"] == true) ? "Si" : "No";
        $third_class_road = ($propertyInfo["ways_of_access"]["third_class_road"] == true) ? "Si" : "No";
        $unpaved_road = ($propertyInfo["ways_of_access"]["unpaved_road"] == true) ? "Si" : "No";

        $description = "Dirección" . ": " . $propertyInfo["address"] . ", " .
                "Necesidades basicas: " .
                "Red de acueducto" . ": " . $networkAqueduct . ", " .
                "Red de eléctrica" . ": " . $networkElectrical . ", " .
                "Sistema de desagüe" . ": " . $systemSewerage . ", " .
                "Actividad económica: " .
                "Agricultura" . ": " . $agriculture . ", " .
                "Químico" . ": " . $chemical . ", " .
                "Comercial" . ": " . $commercial . ", " .
                "Vacas lecheras" . ": " . $dairy_cattle . ", " .
                "Ganado de doble propósito" . ": " . $dual_purpose_cattle . ", " .
                "Certificación de buenas practicas" . ": " . $good_practices_certification . ", " .
                "Trabajo manual" . ": " . $handwork . ", " .
                "Latitud" . ": " . $propertyInfo["economic_activity_in_the_property"]["latitude"] . ", " .
                "Longitud" . ": " . $propertyInfo["economic_activity_in_the_property"]["longitude"] . ", " .
                "Mecanizado" . ": " . $machining . ", " .
                "Otro" . ": " . $propertyInfo["economic_activity_in_the_property"]["other"] . ", " .
                "Cultivo porcino" . ": " . $porcine_farming . ", " .
                "Avicultura" . ": " . $poultry_farming . ", " .
                "Área de producción" . ": " . $propertyInfo["economic_activity_in_the_property"]["production_area"] . ", " .
                "Área de propiedad" . ": " . $propertyInfo["economic_activity_in_the_property"]["property_area"] . ", " .
                "Recreativo" . ": " . $recreational . ", " .
                "Residencial" . ": " . $residential . ", " .
                "Autoconsumo" . ": " . $self_consumption . ", " .
                "Métodos de labranza" . ": " . $tillage_methods . ", " .
                "Contacto: " .
                "Cédula de Ciudadanía" . ": " . $propertyInfo["contact"]["contact_id_card_number"] . ", " .
                "Nombre" . ": " . $propertyInfo["contact"]["contact_name"] . ", " .
                "Correo electrónico" . ": " . $propertyInfo["contact"]["contact_email"] . ", " .
                "Celular" . ": " . $propertyInfo["contact"]["contact_mobile_number"] . ", " .
                "Teléfono" . ": " . $propertyInfo["contact"]["contact_land_line_number"] . ", " .
                "Daño ambiental: " .
                "Comentario" . ": " . $propertyInfo["environmental_damage"]["comments"] . ", " .
                "Registro de nativos" . ": " . $natives_logging . ", " .
                "Otro" . ": " . $propertyInfo["environmental_damage"]["others"] . ", " .
                "Desecación de humedales" . ": " . $wetland_desiccation . ", " .
                "Fuente hidrológica" . ": " . $propertyInfo["hydrological_source"] . ", " .
                "Carril" . ": " . $propertyInfo["lane"] . ", " .
                "Micro cuenca" . ": " . $propertyInfo["micro_basin"] . ", " .
                "Municipio" . ": " . $propertyInfo["municipality"] . ", " .
                "Ecosistemas naturales en la propiedad: " .
                "Contaminado" . ": " . $propertyInfo["natural_ecosystems_in_the_property"]["contaminated"] . ", " .
                "Erosión" . ": " . $propertyInfo["natural_ecosystems_in_the_property"]["erosion"] . ", " .
                "Bosque de montaña" . ": " . $mountainside_forest . ", " .
                "Área del rio" . ": " . $riverbank_area . ", " .
                "Bosque de ribera" . ": " . $riverbank_forest . ", " .
                "Primavera" . ": " . $spring . ", " .
                "Desprotegido" . ": " . $propertyInfo["natural_ecosystems_in_the_property"]["un_protected"] . ", " .
                "NIT" . ": " . $propertyInfo["nit"] . ", " .
                "Socio de la propiedad colanta" . ": " . $property_colanta_partner . ", " .
                "Correlación de la propiedad" . ": " . $propertyInfo["property_correlation"] . ", " .
                "Tipo de correlación de la propiedad" . ": " . ($property->property_correlation_id != null) ? $property->properyCorrelation->name : "ninguna" . ", " .
                "Estado legal de la propiedad: " . ", " .
                "Comentario" . ": " . $propertyInfo["property_legal_status"]["comments"] . ", " .
                "Posesión" . ": " . $possession . ", " .
                "Sucesión" . ": " . $succession . ", " .
                "Estado del inquilino" . ": " . $tenant_status . ", " .
                "Valor" . ": " . $propertyInfo["property_legal_status"]["value"] . ", " .
                "Comerciante de leche propiedad" . ": " . $propertyInfo["property_milk_merchant"] . ", " .
                "Depósito de propiedad" . ": " . $propertyInfo["property_reservoir"] . ", " .
                "Nombre comercial de la propiedad" . ": " . $propertyInfo["property_retail_name"] . ", " .
                "Sector de la propiedad" . ": " . $propertyInfo["property_sector"] . ", " .
                "Tipo de propiedad: " .
                "Comercial" . ": " . $property_type_commercial . ", " .
                "Otra propiedad" . ": " . $property_type_property_other . ", " .
                "Residencial" . ": " . $property_type_residential . ", " .
                "Otro" . ": " . $property_type_other . ", " .
                "Fecha de la propiedad" . ": " . $propertyInfo["property_visit_date"]["day"] . "/" . $propertyInfo["property_visit_date"]["month"] . $propertyInfo["property_visit_date"]["year"] . ", " .
                "Características de vertimiento: " .
                "Sistema de tratamiento de aire" . ": " . $ar_treatment_system . ", " .
                "Actividades agrícolas en la propiedad: " .
                "Fertilizantes quimicos" . ": " . $chemical_fertilizers . ", " .
                "Marcas de fertilizantes" . ": " . $propertyInfo["shedding_characteristics"]["are_there_agricultural_activities_in_the_property"]["fertilizers_brands"] . ", " .
                "Descripción de fertilizantes" . ": " . $propertyInfo["shedding_characteristics"]["are_there_agricultural_activities_in_the_property"]["fertilizers_description"] . ", " .
                "Fertilizantes orgánicos" . ": " . $organic_fertilizers . ", " .
                "Pesticidas" . ": " . $pesticides . ", " .
                "Manejo químico y / o de captura de ganado: " .
                "Quemado" . ": " . $burning . ", " .
                "Enterrar" . ": " . $burying . ", " .
                "Entrega a una entidad recaudadora sin separación" . ": " . $delivery_to_a_collecting_entity_without_separation . ", " .
                "Otro" . ": " . $propertyInfo["shedding_characteristics"]["chemical_and_or_cattle_raising_handling"]["other_describe"] . ", " .
                "Separación y entrega a una entidad recaudadora" . ": " . $separation_and_delivery_to_a_collecting_entity . ", " .
                "Manejo de cosecha de desechos sólidos domesticos: " .
                "Quemado" . ": " . $burningDomestic . ", " .
                "Aprovechando" . ": " . $harnessing . ", " .
                "Ninguna" . ": " . $none . ", " .
                "Reciclaje" . ": " . $recycling . ", " .
                "Separación" . ": " . $separation . ", " .
                "La última fecha de mantenimiento del sistema de alcantarillado: " .
                "Año 12" . ": " . $years12 . ", " .
                "Año 23" . ": " . $years23 . ", " .
                "Año 34" . ": " . $years34 . ", " .
                "Información socioeconómica: " .
                "Fondo de compensación familiar" . ": " . $family_compensation_fund . ", " .
                "Nombre del fondo de compensación familiar" . ": " . $propertyInfo["socio_economic_information"]["family_compensation_fund_name"] . ", " .
                "Población de unidades de vivienda" . ": " . $propertyInfo["socio_economic_information"]["housing_units_population"] . ", " .
                "Cantidad de grupos familiares" . ": " . $propertyInfo["socio_economic_information"]["number_of_family_groups"] . ", " .
                "Sisben" . ": " . $sisben . ", " .
                "Nivel del sisben" . ": " . $propertyInfo["socio_economic_information"]["sisben_level"] . ", " .
                "Capa socioeconómica" . ": " . $propertyInfo["socio_economic_information"]["socioeconomic_layer"] . ", " .
                "¿Por qué no tiene Fondo de Compensación Familiar?" . ": " . $propertyInfo["socio_economic_information"]["why_has_hasnt_family_compensation_fund"] . ", " .
                "Importancia estratégica de la propiedad: " .
                "Certificación de venta de bonos de carbono" . ": " . $carbon_bonds_sale_certification . ", " .
                "Conservación de la Biodiversidad" . ": " . $biodiversity_conservation . ", " .
                "Conectividad ecológica" . ": " . $ecological_connectivity . ", " .
                "Alto grado de bosque de conservación" . ": " . $high_degree_of_conservation_forest . ", " .
                "Fuente de agua productiva" . ": " . $productive_water_source . ", " .
                "Psa" . ": " . $propertyInfo["strategic_importance_of_the_property"]["psa"] . ", " .
                "Carbono secuestrado" . ": " . $sequestered_carbon . ", " .
                "Fuente de suministro" . ": " . $supply_source . ", " .
                "Mejora de la calidad del agua" . ": " . $water_quality_improvement . ", " .
                "Regulación del agua" . ": " . $water_regulation . ", " .
                "Pueblo" . ": " . $propertyInfo["township"] . ", " .
                "Formas de acceso: " .
                "Se puede llegar en coche" . ": " . $can_be_reached_by_car . ", " .
                "Camino principal" . ": " . $primary_road . ", " .
                "Carretera secundaria" . ": " . $secondary_road . ", " .
                "Carretera de tercera clase" . ": " . $third_class_road . ", " .
                "Carretera sin asfaltar" . ": " . $unpaved_road . ", " .
                "Zona" . ": " . $propertyInfo["zone"];

        $dataSearch = [
            "name" => $propertyInfo["property_name"],
            "description" => $description,
            "type" => $type,
            "entity_id" => $task_id
        ];

        if ($searchAlgoliaController->registerSearchUniversal($dataSearch) == 200) {
            return true;
        }
    }

    public function addMinuteData(Request $data) {
        $other = CvOtherInfoMinuteProperty::where('property_id', $data->property_id);
        if ($other->exists()) {
            $other = $other->first();
        } else {
            $other = new CvOtherInfoMinuteProperty();
        }
        $other->property_id = $data->property_id;
        $other->infojson = json_encode($data->data, true);
        $other->save();
        return[
            'message' => 'informacion almacenada',
            'code' => 200
        ];
    }

    public function getMinuteDataforTask($task_id) {
        $task = CvTask::find($task_id)->process[0]->potentialProperty->id;
        return $this->getMinuteDataforProperty($task);
    }

    public function getMinuteDataforProperty($property_id) {
        $other = CvOtherInfoMinuteProperty::where('property_id', $property_id);
        if ($other->exists()) {
            return $other = json_decode($other->first()->infojson, true);
        } else {
            return[
                'message' => 'No existe informacio registrada',
                'code' => 500
            ];
        }
    }

}
