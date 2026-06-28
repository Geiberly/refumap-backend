<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Support\Geo\VenezuelaBounds;

class StoreMapPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Controlado por middleware de roles
    }

    public function rules(): array
    {
        return [
            'category_id'        => 'required|exists:categories,id',
            'name'               => 'required|string|min:3|max:200',
            'description'        => 'nullable|string|max:2000',
            'address'            => 'nullable|string|max:300',
            'latitude'           => VenezuelaBounds::latitudeRule(),
            'longitude'          => VenezuelaBounds::longitudeRule(),
            'status'             => 'required|in:active,full,closed,unverified,danger',
            'source'             => 'required|in:official,operator,citizen,seed,unverified',
            'capacity_total'     => 'nullable|integer|min:1|max:100000',
            'capacity_available' => 'nullable|integer|min:0|max:100000|lte:capacity_total',
            'accepts_children'   => 'boolean',
            'accepts_elderly'    => 'boolean',
            'accepts_pets'       => 'boolean',
            'has_water'          => 'boolean',
            'has_food'           => 'boolean',
            'has_medicine'       => 'boolean',
            'has_power_charging' => 'boolean',
            'contact_phone'      => 'nullable|string|max:30',
            'notes'              => 'nullable|string|max:1000',
            'urgency_level'      => 'integer|between:1,4',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Debes seleccionar una categoría.',
            'category_id.exists'   => 'La categoría seleccionada no existe.',
            'name.required'        => 'El nombre del punto es obligatorio.',
            'name.min'             => 'El nombre debe tener al menos 3 caracteres.',
            'latitude.required'    => 'La latitud es obligatoria.',
            'latitude.between'     => VenezuelaBounds::message(),
            'longitude.required'   => 'La longitud es obligatoria.',
            'longitude.between'    => VenezuelaBounds::message(),
            'status.required'      => 'El estado del punto es obligatorio.',
            'status.in'            => 'El estado debe ser: activo, saturado, cerrado, no verificado o peligro.',
            'source.required'      => 'La fuente de información es obligatoria.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Los datos del formulario son inválidos.',
            'errors'  => $validator->errors(),
        ], 422));
    }
}
