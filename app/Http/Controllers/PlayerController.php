<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerRequest;
use App\Models\Country;
use App\Models\Player;
use App\Models\Tournament;
use App\Queries\PlayerFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PlayerController extends Controller
{
    public function index(Request $request, PlayerFilter $filter)
    {
        $input = $request->validate([
            'q'              => ['nullable', 'string', 'max:50'],
            'tournamentName' => ['nullable', 'string', 'max:100'],
            'country_code'   => ['nullable', 'string', 'size:2', 'exists:country,code'],
            'tournament_id'  => ['nullable', 'integer', 'exists:tournaments,id'],
            'sort'           => ['nullable', 'string', 'max:30'],
        ]);

        $players = $filter->apply($input)
            ->paginate(5)
            ->withQueryString();

        $countries   = Country::orderBy('name')->pluck('name', 'code');
        $tournaments = Tournament::orderBy('name')->pluck('name', 'id');

        return view('players.index', compact('players', 'countries', 'tournaments'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        $tournaments = Tournament::orderBy('name')->pluck('name', 'id');

        return view('players.create', compact('countries', 'tournaments'));
    }

    public function store(PlayerRequest $request)
    {
        $data = $request->validated();

        $player = Player::create(Arr::except($data, ['tournaments']));
        $player->tournaments()->sync($data['tournaments'] ?? []);

        return redirect()->route('players.index');
    }

    public function show(Player $player)
    {
        $player->load('tournaments', 'country');

        return view('players.show', compact('player'));
    }

    public function edit(Player $player)
    {
        $countries = Country::orderBy('name')->get();
        $tournaments = Tournament::orderBy('name')->pluck('name', 'id');

        return view('players.edit', compact('player', 'countries', 'tournaments'));
    }

    public function update(PlayerRequest $request, Player $player)
    {
        $data = $request->validated();

        $player->update(Arr::except($data, ['tournaments']));
        $player->tournaments()->sync($data['tournaments'] ?? []);

        return redirect()->route('players.index');
    }

    public function destroy(Player $player)
    {
        $player->delete();

        return redirect()->route('players.index');
    }
}
