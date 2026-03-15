<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::with('category')->latest()->get();
        $categories = Category::all();
        return Inertia::render('admin/games/index', [
            'games' => $games,
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'thumbnail' => 'nullable|string',
            'input_label' => 'required|string',
            'has_server' => 'required|boolean',
        ]);

        Game::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'thumbnail' => $request->thumbnail,
            'input_label' => $request->input_label,
            'has_server' => $request->has_server,
            'is_active' => true,
        ]);

        return back()->with('success', 'Game berhasil ditambahkan.');
    }

    public function update(Request $request, Game $game)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
        ]);

        $game->update($request->all());

        return back()->with('success', 'Game berhasil diupdate.');
    }

    public function destroy(Game $game)
    {
        $game->delete();
        return back()->with('success', 'Game berhasil dihapus.');
    }
}
