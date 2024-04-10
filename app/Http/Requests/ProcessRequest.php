<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {

        return [
            //Información general de los procesos
            'name' => "required",
            'description' => "required",
            'activities' => "min:1"
        ];
    }

    /**
     * Get the validation rules that apply to the request with messages personalized
     *
     * @return array
     */
    public function messages() {

        return [
            //Mensajes de la información general de los procesos
            'name.required' => "El nombre del procedimiento es requerido",
            'description.required' => "La descripción del procedimiento es requerido",
            'activities.min' => "Por favor selecciones minimo 1 actividad"
        ];
    }

    public function response(array $errors) {

        return response()->json($errors);
    }

}
