<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest {

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
            //Información general de las tareas
            'type_id' => "min:1",
            'description' => "required_unless:type_id,5",
            'option_date' => "required_unless:type_id,5|min:0|max:1|integer",
            'startdate' => "required_unless:type_id,5|required_if:option_date,1",
            'comment' => "min:5|max:200"
        ];
    }

    /**
     * Get the validation rules that apply to the request with messages personalized
     *
     * @return array
     */
    public function messages() {

        return [
            //Mensajes personalizados de la información general de las tareas
            'type_id.min' => "Seleccione minimo un tipo de tarea",
            'description.required' => "La descripción de la tarea es requerida",
            'option_date.required' => "La opcion de la fecha es requerida",
            'option_date.min' => "La mínima opcion debe ser 0",
            'option_date.max' => "La maxima opcion debe ser 1",
            'startdate.date' => "Ingrese un formato de fecha valido",
            'startdate.required_if' => "Ingrese una fecha de inicio cuando la opción de la fecha es 1",
            'deadline.date' => "Ingrese un formato de fecha valido",
            'deadline.required_if' => "Ingrese una fecha de finalización cuando la opción de la fecha es 1",
            'deadline.after_or_equal' => "La fecha de finalización debe ser igual o mayor a la inicial",
            'comment.min' => "Ingrese mínimo 5 caracteres",
            'comment.max' => "Ingrese mínimo 200 caracteres"
        ];
    }

    public function response(array $errors) {

        return response()->json($errors);
    }

}
