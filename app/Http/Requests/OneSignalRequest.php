<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OneSignalRequest extends FormRequest {

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
            'player_id' => "required"
        ];
    }

    /**
     * Get the validation rules that apply to the request with messages personalized
     *
     * @return array
     */
    public function messages() {

        return [
            'player_id.required' => "El player_Id del usuario es requerido.",
        ];
    }

    public function response(array $errors) {

        return response()->json([
                    "response_code" => 500,
                    "errors" => $errors["player_id"][0]
        ]);
    }

}
