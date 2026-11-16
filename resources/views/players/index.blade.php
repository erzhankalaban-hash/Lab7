@extends('layouts.app')

@section('title', 'Гравці')

@section('content')
    <h1>Гравці</h1>

    <p><a href="{{ route('players.create') }}">Додати гравця</a></p>

    @php
        $currentSort = request('sort');
        $sortLink = function (string $field, string $label) use ($currentSort) {
            $next = $currentSort === $field ? '-' . $field : $field;
            $url = route('players.index', array_merge(request()->except('sort', 'page'), ['sort' => $next]));
            $arrow = $currentSort === $field ? ' ↑' : ($currentSort === '-' . $field ? ' ↓' : '');
            return '<a href="' . e($url) . '">' . e($label) . $arrow . '</a>';
        };
    @endphp

    <form method="GET" action="{{ route('players.index') }}"
          style="display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end;">
        <div>
            <label for="q">Прізвище / ім'я</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}"
                   placeholder="Пошук гравця" style="width:170px">
        </div>
        <div>
            <label for="tournamentName">Назва турніру</label>
            <input type="text" id="tournamentName" name="tournamentName"
                   value="{{ request('tournamentName') }}"
                   placeholder="Пошук за турніром" style="width:170px">
        </div>
        <div>
            <label for="country_code">Країна</label>
            <select id="country_code" name="country_code" style="width:170px">
                <option value="">Усі країни</option>
                @foreach ($countries as $code => $name)
                    <option value="{{ $code }}" @selected(request('country_code') === $code)>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tournament_id">Турнір</label>
            <select id="tournament_id" name="tournament_id" style="width:170px">
                <option value="">Усі турніри</option>
                @foreach ($tournaments as $id => $name)
                    <option value="{{ $id }}" @selected((string) request('tournament_id') === (string) $id)>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <div>
            <button type="submit">Застосувати</button>
            <a href="{{ route('players.index') }}">Скинути</a>
        </div>
    </form>

    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>{!! $sortLink('name', 'Прізвище') !!}</th>
            <th>Ім'я</th>
            <th>Позиція</th>
            <th>{!! $sortLink('country', 'Країна') !!}</th>
            <th>{!! $sortLink('birth', 'Дата нар.') !!}</th>
            <th>{!! $sortLink('tournaments', 'Турніри') !!}</th>
            <th>Дії</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($players as $i => $player)
            <tr>
                <td>{{ $players->firstItem() + $i }}</td>
                <td>
                    <a href="{{ route('players.show', $player) }}">{{ $player->last_name }}</a>
                </td>
                <td>{{ $player->first_name }}</td>
                <td>{{ $player->position }}</td>
                <td>{{ $player->country?->name ?? '—' }} ({{ $player->country_code }})</td>
                <td>{{ $player->birth_date->format('d.m.Y') }}</td>
                <td>
                    <strong>{{ $player->tournaments_count }}</strong>
                    @foreach ($player->tournaments as $tournament)
                        <span class="badge">{{ $tournament->name }}</span>
                    @endforeach
                </td>
                <td>
                    <a href="{{ route('players.edit', $player) }}">Редагувати</a>
                    <form method="POST" action="{{ route('players.destroy', $player) }}" class="inline"
                          onsubmit="return confirm('Видалити гравця?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Видалити</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Гравців не знайдено.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    {{ $players->links() }}
@endsection
