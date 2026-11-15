<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTournamentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $tournament = $this->route('tournament');

        return [
            'slug' => ['required', 'string', 'max:120', Rule::unique('tournaments', 'slug')->ignore($tournament?->id)],
            'game' => ['required', 'string', 'max:80'],
            'name_en' => ['required', 'string', 'max:160'], 'name_ru' => ['nullable', 'string', 'max:160'], 'name_tj' => ['nullable', 'string', 'max:160'],
            'description_en' => ['nullable', 'string', 'max:3000'], 'description_ru' => ['nullable', 'string', 'max:3000'], 'description_tj' => ['nullable', 'string', 'max:3000'],
            'prize_en' => ['nullable', 'string', 'max:1000'], 'prize_ru' => ['nullable', 'string', 'max:1000'], 'prize_tj' => ['nullable', 'string', 'max:1000'],
            'format' => ['required', 'in:single_elimination,double_elimination,round_robin'],
            'status' => ['required', 'in:draft,published,registration,in_progress,completed,cancelled'],
            'participant_limit' => ['nullable', 'integer', 'min:2', 'max:1000'], 'team_size' => ['required', 'integer', 'min:1', 'max:20'],
            'registration_opens_at' => ['nullable', 'date'], 'registration_closes_at' => ['nullable', 'date', 'after:registration_opens_at'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'registration_fee' => ['nullable', 'numeric', 'min:0', 'max:10000', 'regex:/^\d+(\.\d{1,2})?$/'],
        ];
    }
}
