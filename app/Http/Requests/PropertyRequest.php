<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class PropertyRequest extends FormRequest {

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
            //Información general del predio
            'property_type.value' => "required",
            'property_type.other' => "max:50",
            'property_name' => "required",
            'property_colanta_partner' => "required",
            'property_milk_merchant' => "required",
            'property_reservoir' => "max:50",
            'property_retail_name' => "max:50",
            'property_sector' => "max:50",
            'property_correlation_id' => "required",
            'property_visit_date' => "required",
            //Información del contacto
            'contact.contact_name' => "required",
            'contact.contact_email' => "required",
            'contact.contact_id_card_number' => "required",
            'contact.contact_land_line_number' => "required",
            'contact.contact_mobile_number' => "required",
        ];
    }

    public function response(array $errors) {

        return response()->json($errors);
    }

}
