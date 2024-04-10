<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProgramRequest;
use App\CvProgram;
use Illuminate\Database\QueryException;

class ProgramController extends Controller {

    // *** Consultar todos los programas *** //

    public function index() {

        return CvProgram::orderBy('id', 'desc')->get();
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //
    public function create() {
        abort(404);
    }

    // *** Registrar un nuevo programa *** //
    public function store(ProgramRequest $request) {

        try {
            //Instancia de la clase CvProgram
            $program = new CvProgram();

            $program->name = $request->program_name;

            if ($program->save()) {
                return [
                    "message" => "Registro exitoso",
                    "code" => 200
                ];
            } else {
                return [
                    "message" => "Hubo un error en el registro",
                    "code" => 500
                ];
            }
        } catch (Exception $e) {
            return "Se ha presentado un error: " . $e->getMessage() . "\n";
        }
    }

    // *** Información de un programa ***//

    public function show($id) {

        //Instancia de la clase CvProgram
        $program = CvProgram::find($id);

        if ($program != "") {
            return $program;
        } else {
            return [
                "message" => "El programa no existe en el sistema",
                "code" => 200
            ];
        }
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function edit($id) {

        return abort(404);
    }

    // *** Actualizar información de un programa *** //

    public function update(ProgramRequest $request, $id) {

        try {
            //Instancia de la clase CvProgram
            $program = CvProgram::find($id);

            if ($program != "") {

                $program->name = $request->program_name;

                if ($program->save()) {
                    return [
                        "message" => "Registro actualizado",
                        "code" => 200
                    ];
                } else {
                    return [
                        "message" => "Hubo un error en el registro",
                        "code" => 500
                    ];
                }
            } else {
                return [
                    "message" => "El programa no existe en el sistema",
                    "code" => 200
                ];
            }
        } catch (Exception $e) {
            return "Se ha presentado un error: " . $e->getMessage() . "\n";
        }
    }

    // *** Ruta por defecto de laravel - retorna error 404 *** //

    public function destroy($id) {

        abort(404);
    }

}
