<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Career;
use App\Models\Course;
use App\Models\University;

class UpdateRecommendationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajusta si usas Policies/Roles (ej. return $this->user()?->hasRole('admin') ?? false;)
        return true;
    }

    public function rules(): array
    {
        return [
            'title'        => ['sometimes', 'string', 'max:255'],
            'entity_type'  => ['sometimes', Rule::in(['career', 'course', 'university'])],
            'entity_id'    => ['sometimes', 'integer', 'min:1'],
            'weight'       => ['nullable', 'numeric', 'min:0', 'max:1'],
            'filters'      => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.string'          => 'El título debe ser texto.',
            'title.max'             => 'El título no puede superar 255 caracteres.',
            'entity_type.in'        => 'El tipo de entidad debe ser: career, course o university.',
            'entity_id.integer'     => 'El ID de la entidad debe ser un número entero.',
            'entity_id.min'         => 'El ID de la entidad debe ser mayor o igual a 1.',
            'weight.numeric'        => 'El peso debe ser numérico.',
            'weight.min'            => 'El peso no puede ser menor a 0.',
            'weight.max'            => 'El peso no puede ser mayor a 1.',
            'filters.array'         => 'Los filtros deben enviarse como un objeto/array.',
        ];
    }

    /**
     * Validación extra: si el payload incluye entity_type/entity_id,
     * verificamos la existencia del registro correspondiente.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Solo validamos si ambos campos vienen en la actualización
            $hasType = $this->has('entity_type');
            $hasId   = $this->has('entity_id');

            if (!($hasType && $hasId)) {
                return;
            }

            $type = $this->input('entity_type');
            $id   = (int) $this->input('entity_id');

            if (!$type || !$id) {
                return;
            }

            $exists = match ($type) {
                'career'     => Career::query()->where('id', $id)->exists(),
                'course'     => Course::query()->where('id', $id)->exists(),
                'university' => University::query()->where('id', $id)->exists(),
                default      => false,
            };

            if (!$exists) {
                $v->errors()->add('entity_id', 'El ID no existe para el tipo de entidad especificado.');
            }
        });
    }
}
