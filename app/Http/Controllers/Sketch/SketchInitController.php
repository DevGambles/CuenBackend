<?php

namespace App\Http\Controllers\Sketch;

use App\CvCommentHaschPoint;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use ElevenLab\PHPOGC\DataTypes\Polygon as Polygon;
use Illuminate\Support\Facades\DB;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Coordinate\Ellipsoid;
use League\Geotools\Polygon\Polygon as LeaguePolygon;
use App\CvSketchBasin;
use App\CvSketchProperty;
use GuzzleHttp\Client;

class SketchInitController extends Controller {

    protected $gp_sketch;

    function __construct() {
        $this->gp_sketch = new CvSketchBasin();
    }

//Funcion con librerias laravel para Guardar poligonos de Cuencas
    public function insert(Request $map) {

        $polys = array();
        $agregado = 0;
        foreach ($map->features as $features) {
            $polys = Polygon::fromArray($features["geometry"]["coordinates"][0]);
            $users = DB::table('cv_sketch_basins')->insert(
                ['regions_bounds' => (DB::raw("GeomFromText('POLYGON(" . $polys . ")')")),
                    'type_geometry' => $features['geometry']['type'],
                    'name_basin' => $features['properties']['Name'],
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s'),
                ]);
            $agregado = $agregado + 1;
        }

        return [
            "message" => "Se han agregado " . $agregado . " mapas",
            "code" => 500,
        ];
    }

//Funcion con librerias laravel para Guardar poligonos de propiedades
    public function insertSketchProperty(Request $map) {

        $agregado = 0;
        foreach ($map->features as $features) {

            $polys = Polygon::fromArray($features["geometry"]["coordinates"][0]);
            $users = DB::table('cv_sketch_properties')->insert(
                ['regions_bounds' => (DB::raw("GeomFromText('POLYGON(" . $polys . ")')")),
                    'type_geometry' => $features['geometry']['type'],
                    'name_basin' => $features['properties']['Name'],
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s'),
                ]);
            $agregado = $agregado + 1;
        }

        return [
            "message" => "Se han agregado " . $agregado . " mapas",
            "code" => 500,
        ];
    }

//Funcion con librerias laravel para buscar un punto en un poligono
    public function shearPointBasin(Request $param) {
        $idmax = DB::table('cv_sketch_basins')->max('id');
        $polygonosmap = array();
        for ($i = 1; $i <= $idmax; $i++) {
            $exist = CvSketchBasin::find($i);
            if ($exist) {
                //Consulta en toda la base de datos y se trae los POLIGONOS
                $query = DB::table('cv_sketch_basins')->select(DB::raw("AsText(regions_bounds)"))->where('id', $i)->get();
                //Limpia la consulta
                $parts = explode('POLYGON((', $query);
                $param = explode('))"}]', $parts[1]);
                $ponti = explode(',', str_replace("),(", ",", $param[0]));

                //prepara el poligono en la libreria
                $polygon = new LeaguePolygon($ponti);

                //Busca el punto en el poligono
                $mapShear = ($polygon->pointInPolygon(new Coordinate([-75.512710195668, 6.4107988802292])));
                if ($mapShear == TRUE) {
                    //Si el punto se encuentra cargara el poligono
                    array_push($polygonosmap, $ponti);
                }
            }
        }
        if ($polygonosmap) {
            //Retorna el array de los poligonos donde se encontro el punto
            return [
                "message" => "Se han buscado en " . $idmax . " mapas",
                "polygono" => $polygonosmap,
                "code" => 500
            ];
        } else {
            return [
                "message" => "Se han buscado en " . $idmax . " mapas, y la ubicacion no se encuentra",
                "code" => 200,
            ];
        }
    }

    //--- Funcion que consume API Mongo y consulta un punto en poligonos ---//
    public function shearPointProperty($lat, $log, $property_id, $potential_id) {

        $respons = ($this->gp_sketch->ubicate($lat, $log));
        //Si la api no conecta o da algun tipo de error
        if ($respons == 1) {
            sleep(2); //Reintente consumo a los 2 segundos
            $respons = ($this->gp_sketch->ubicate($lat, $log));
            if ($respons == 1) {
                //Si la api no conecta o da algun tipo de error
                return 500;
            }
        }

        $propmap = ($potential_id == null) ? new CvSketchProperty() : CvSketchProperty::find($potential_id);
        $propmap->info_json_general = $respons;
        $propmap->property_id = $property_id;
        if ($propmap->save()) {
            return 200;
        } else {
            return 500;
        }
    }

    public function CommentPoint(Request $data)
    {
        foreach ($data->all() as $info){
            foreach ($info['comment'] as $comment){
                $insert=new CvCommentHaschPoint();
                $insert->hash_map=$info['hash'];
                $insert->description=$comment;
                $insert->task_id=$info['task_id'];
                $insert->type=$info['type_task'];
                $insert->user_id=$this->userLoggedInId();
                $insert->save();
            }
        }
        return [
            "message" => "Comentarios registrados",
            "code" => 200,
        ];
    }

    public function getCommentPoint($type,$task,$hash)
    {
        $info=array();
        $add_comment=array();
        $comment=CvCommentHaschPoint::where('task_id',$task)->where('type',$type)->where('hash_map',$hash);
        foreach ($comment->get() as $detail){
            array_push($add_comment,$detail->description);
        }
        $data=$comment->first();

        $info["hash"]  =$data->hash_map;
        $info["comment"]  =$add_comment;
        $info["type_task"]  =$data->type;
        $info["task_id"] =$data->task_id;

        return $info;
    }

}
