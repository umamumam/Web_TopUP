<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Game;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class LandingPageController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/landing-page/index', [
            'banners' => Banner::orderBy('order')->get(),
            'popularGames' => Game::where('is_popular', true)->get(),
            'allGames' => Game::where('is_active', true)->get(),
            'settings' => Setting::whereIn('key', [
                'social_facebook', 
                'social_instagram', 
                'social_youtube', 
                'social_whatsapp',
                'footer_description',
                'site_name',
                'site_logo'
            ])->get()->pluck('value', 'key')
        ]);
    }

    public function storeBanner(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'image_url' => 'nullable|string',
            'link' => 'nullable|string',
            'order' => 'integer'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        Banner::create([
            'title' => $request->title,
            'image' => $imagePath,
            'image_url' => $request->image_url,
            'link' => $request->link,
            'order' => $request->order ?? 0
        ]);

        return back()->with('success', 'Banner berhasil ditambahkan');
    }

    public function deleteBanner(Banner $banner)
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();
        return back()->with('success', 'Banner berhasil dihapus');
    }

    public function updatePopularGames(Request $request)
    {
        $request->validate([
            'game_ids' => 'nullable|array'
        ]);

        Game::query()->update(['is_popular' => false]);
        Game::whereIn('id', $request->game_ids)->update(['is_popular' => true]);

        return back()->with('success', 'Game populer berhasil diperbarui');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:1024'
        ]);

        $settings = $request->only([
            'social_facebook', 
            'social_instagram', 
            'social_youtube', 
            'social_whatsapp',
            'footer_description',
            'site_name'
        ]);

        if ($request->hasFile('site_logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::where('key', 'site_logo')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }
            
            $path = $request->file('site_logo')->store('logo', 'public');
            Setting::updateOrCreate(['key' => 'site_logo'], ['value' => $path]);
        }

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui');
    }
}
