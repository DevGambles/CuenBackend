<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest {

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
            //Información del usuario
            'user.names' => "required|min:3|max:50",
            'user.last_names' => "required|min:3|max:50",
            'user.email' => 'required|email|unique:users,email,' . $this->id . '|max:140',
            'user.name' => "required|unique:users,name," . $this->id . "|max:100",
            'user.rol_id' => "required|integer",
            'quota' => "required_if:user.rol_id,4|numeric|min:1|max:999"
        ];
    }

    /**
     * Get the validation rules that apply to the request with messages personalized
     *
     * @return array
     */
    public function messages() {

        return [
            //--- Información del usuario ---//

            'user.names.required' => "Los nombres del usuario son requeridos",
            'user.names.min' => "Ingrese minimo 3 caracteres",
            'user.names.max' => "Ingrese maximo 50 caracteres",
            'user.last_names.required' => "Los apellidos del usuario son requeridos",
            'user.last_names.min' => "Ingrese minimo 3 caracteres",
            'user.last_names.max' => "Ingrese maximo 50 caracteres",
            'user.email.required' => "Ingrese un correo electronico",
            'user.email.email' => "Ingrese un formato valido de correo electronico",
            'user.email.unique' => "El correo electronico ya se encuentra en el sistema",
            'user.email.max' => "Ingrese maximo 140 caracteres",
            'user.name.required' => "Ingrese un nombre de usuario",
            'user.name.unique' => "El usuario ya se encuentra en el sistema",
            'user.name.max' => "Ingrese maximo 100 caracteres",
            'user.pass.required' => "La contraseña del usuario es requerida",
            'user.pass.max' => "Ingrese maximo 150 caracteres",
            'quota.required' => "El ususario con el rol de guarda cuenca debe ingresar una cuota",
            'quota.numeric' => "La cuota debe ser de tipo numerica",
            'quota.min' => "Ingrese minimo 1 caracter",
            'quota.max' => "Ingrese minimo 999 caracter"
        ];
    }

    public function response(array $errors) {

        $info = array();

        foreach ($errors as $error) {
            array_push($info, $error);
        }

        return response()->json([
                    "response_code" => 500,
                    "errors" => $info
        ]);
    }

}
