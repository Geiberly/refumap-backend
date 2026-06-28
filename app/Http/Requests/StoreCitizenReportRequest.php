<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Support\Geo\VenezuelaBounds;

class StoreCitizenReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Público
    }

    protected function prepareForValidation(): void
    {
        // Los reportes genéricos desde el mapa llegan como multipart; normalizamos metadata JSON a array.
        if (is_string($this->metadata)) {
            $decoded = json_decode($this->metadata, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['metadata' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'report_type'   => 'required|in:new_help_point,shelter_full,hospital_closed,road_blocked,danger_zone,lack_of_supplies,collapsed_building,incorrect_info,other',
            'title'         => 'required|string|min:5|max:150',
            'description'   => 'nullable|string|max:1000',
            'latitude'      => VenezuelaBounds::latitudeRule(false),
            'longitude'     => VenezuelaBounds::longitudeRule(false),
            'address'       => 'nullable|string|max:255',
            'photo'         => 'nullable|image|mimes:jpeg,png,webp|max:3072', // 3MB máx
            'contact_phone' => 'nullable|string|max:30|regex:/^[\d\s\+\-\(\)]+$/',
            'metadata'      => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'report_type.required' => 'El tipo de reporte es obligatorio.',
            'report_type.in'       => 'El tipo de reporte no es válido.',
            'title.required'       => 'El título del reporte es obligatorio.',
            'title.min'            => 'El título debe tener al menos 5 caracteres.',
            'title.max'            => 'El título no puede superar los 150 caracteres.',
            'description.max'      => 'La descripción no puede superar los 1000 caracteres.',
            'latitude.numeric'     => 'La latitud debe ser un número.',
            'latitude.between'     => VenezuelaBounds::message(),
            'longitude.numeric'    => 'La longitud debe ser un número.',
            'longitude.between'    => VenezuelaBounds::message(),
            'photo.image'          => 'El archivo debe ser una imagen.',
            'photo.mimes'          => 'La imagen debe ser JPEG, PNG o WebP.',
            'photo.max'            => 'La imagen no puede pesar más de 3MB.',
            'contact_phone.max'    => 'El teléfono no puede superar los 30 caracteres.',
            'contact_phone.regex'  => 'El teléfono solo puede contener números, espacios y los caracteres + - ( ).',
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
