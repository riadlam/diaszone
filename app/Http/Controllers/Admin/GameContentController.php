<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameContent;
use App\Models\GameImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GameContentController extends Controller
{
    /**
     * Display list of games with their content status
     */
    public function index()
    {
        $games = Game::where('is_active', true)
            ->with('content')
            ->orderBy('name')
            ->get();

        return view('admin.game-content.index', compact('games'));
    }

    /**
     * Show form to create/edit game content
     */
    public function edit(Game $game)
    {
        $content = $game->content;
        $images = $game->images()->orderBy('display_order')->get();

        return view('admin.game-content.edit', compact('game', 'content', 'images'));
    }

    /**
     * Store or update game content
     */
    public function store(Request $request, Game $game)
    {
        $validator = Validator::make($request->all(), [
            'currency_name' => 'nullable|string|max:255',
            'about_text' => 'nullable|string',
            'instructions_text' => 'nullable|string',
            'id_format' => 'nullable|string|in:user_id_zone_id,player_id,user_id,user_id_server',
            'how_to_topup' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        GameContent::updateOrCreate(
            ['game_id' => $game->id],
            [
                'currency_name' => $request->currency_name,
                'about_text' => $request->about_text,
                'instructions_text' => $request->instructions_text,
                'id_format' => $request->id_format,
                'how_to_topup' => $request->how_to_topup,
            ]
        );

        return redirect()->route('admin.game-content.edit', $game)
            ->with('success', 'Game content saved successfully!');
    }

    /**
     * Store game image
     */
    public function storeImage(Request $request, Game $game)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
            'image_type' => 'required|string|in:about,instruction,how_to_topup',
            'alt_text' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Store image
        $imagePath = $request->file('image')->store('game-content-images', 'public');

        GameImage::create([
            'game_id' => $game->id,
            'image_path' => 'storage/' . $imagePath,
            'image_type' => $request->image_type,
            'display_order' => $request->display_order ?? 0,
            'alt_text' => $request->alt_text,
            'title' => $request->title,
        ]);

        return redirect()->route('admin.game-content.edit', $game)
            ->with('success', 'Image uploaded successfully!');
    }

    /**
     * Delete game image
     */
    public function deleteImage(Game $game, GameImage $image)
    {
        // Delete file from storage
        if ($image->image_path && Storage::disk('public')->exists(str_replace('storage/', '', $image->image_path))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $image->image_path));
        }

        $image->delete();

        return redirect()->route('admin.game-content.edit', $game)
            ->with('success', 'Image deleted successfully!');
    }

    /**
     * Update image order
     */
    public function updateImageOrder(Request $request, Game $game)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*.id' => 'required|exists:game_images,id',
            'images.*.display_order' => 'required|integer|min:0',
        ]);

        foreach ($request->images as $imageData) {
            GameImage::where('id', $imageData['id'])
                ->where('game_id', $game->id)
                ->update(['display_order' => $imageData['display_order']]);
        }

        return response()->json(['success' => true]);
    }
}
