<?php

namespace App\Queries;

use App\Models\Player;
use Illuminate\Database\Eloquent\Builder;

class PlayerFilter
{
    // Білий список колонок для сортування (імена з URL -> реальні колонки)
    private const SORTABLE = [
        'name'        => 'players.last_name',
        'birth'       => 'players.birth_date',
        'country'     => 'country.name',
        'tournaments' => 'tournaments_count',
    ];

    public function apply(array $input): Builder
    {
        $query = Player::query()
            ->select('players.*')
            ->leftJoin('country', 'players.country_code', '=', 'country.code')
            ->with(['country', 'tournaments'])
            ->withCount('tournaments');

        // Пошук за основними полями гравця
        $query->when($input['q'] ?? null, function (Builder $q, string $value) {
            $q->where(function (Builder $w) use ($value) {
                $w->where('players.last_name', 'like', "%{$value}%")
                    ->orWhere('players.first_name', 'like', "%{$value}%");
            });
        });

        // Пошук за полем пов'язаної таблиці (назва турніру)
        $query->when($input['tournamentName'] ?? null, function (Builder $q, string $value) {
            $q->whereHas('tournaments', fn (Builder $related) =>
            $related->where('tournaments.name', 'like', "%{$value}%")
            );
        });

        // Випадаючий список: країна
        $query->when($input['country_code'] ?? null, function (Builder $q, string $value) {
            $q->where('players.country_code', $value);
        });

        // Випадаючий список: турнір (зв'язок N:N)
        $query->when($input['tournament_id'] ?? null, function (Builder $q, $value) {
            $q->whereHas('tournaments', fn (Builder $related) =>
            $related->whereKey($value)
            );
        });

        return $this->sort($query, $input['sort'] ?? null);
    }

    private function sort(Builder $query, ?string $sort): Builder
    {
        $desc  = str_starts_with($sort ?? '', '-');
        $field = $desc ? substr($sort, 1) : $sort;

        if (! isset(self::SORTABLE[$field])) {
            return $query->orderBy('players.last_name')
                ->orderBy('players.id');
        }

        return $query
            ->orderBy(self::SORTABLE[$field], $desc ? 'desc' : 'asc')
            ->orderBy('players.id');
    }
}
