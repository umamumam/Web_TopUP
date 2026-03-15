<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        
        // Ensure default values exist
        if (!isset($settings['profit_margin_type'])) $settings['profit_margin_type'] = 'flat';
        if (!isset($settings['profit_margin_value'])) $settings['profit_margin_value'] = '2000';

        // Add ENV values
        $settings['midtrans_server_key'] = env('MIDTRANS_SERVER_KEY', '');
        $settings['midtrans_client_key'] = env('MIDTRANS_CLIENT_KEY', '');
        $settings['midtrans_status'] = env('MIDTRANS_IS_PRODUCTION') ? 'production' : 'sandbox';
        
        $settings['digiflazz_username'] = env('DIGIFLAZZ_USERNAME', '');
        $settings['digiflazz_api_key'] = env('DIGIFLAZZ_API_KEY', '');
        $settings['digiflazz_webhook_secret'] = env('DIGIFLAZZ_WEBHOOK_SECRET', '');
        $settings['digiflazz_mode'] = env('DIGIFLAZZ_MODE', 'development');

        return Inertia::render('admin/settings/index', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'profit_margin_type' => 'nullable|in:flat,percent',
            'profit_margin_value' => 'nullable|numeric|min:0',
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_status' => 'nullable|in:sandbox,production',
            'digiflazz_username' => 'nullable|string',
            'digiflazz_api_key' => 'nullable|string',
            'digiflazz_webhook_secret' => 'nullable|string',
            'digiflazz_mode' => 'nullable|in:development,production',
        ]);

        // Save database settings
        if (array_key_exists('profit_margin_type', $data)) {
            Setting::updateOrCreate(['key' => 'profit_margin_type'], ['value' => $data['profit_margin_type']]);
        }
        if (array_key_exists('profit_margin_value', $data)) {
            Setting::updateOrCreate(['key' => 'profit_margin_value'], ['value' => $data['profit_margin_value']]);
        }

        // Save ENV settings
        $envUpdates = [];
        if (array_key_exists('midtrans_server_key', $data)) $envUpdates['MIDTRANS_SERVER_KEY'] = $data['midtrans_server_key'] ?? '';
        if (array_key_exists('midtrans_client_key', $data)) $envUpdates['MIDTRANS_CLIENT_KEY'] = $data['midtrans_client_key'] ?? '';
        if (array_key_exists('midtrans_status', $data)) {
            $isProd = $data['midtrans_status'] === 'production';
            $envUpdates['MIDTRANS_IS_PRODUCTION'] = $isProd ? 'true' : 'false';
        }
        
        if (array_key_exists('digiflazz_username', $data)) $envUpdates['DIGIFLAZZ_USERNAME'] = $data['digiflazz_username'] ?? '';
        if (array_key_exists('digiflazz_api_key', $data)) $envUpdates['DIGIFLAZZ_API_KEY'] = $data['digiflazz_api_key'] ?? '';
        if (array_key_exists('digiflazz_webhook_secret', $data)) $envUpdates['DIGIFLAZZ_WEBHOOK_SECRET'] = $data['digiflazz_webhook_secret'] ?? '';
        if (array_key_exists('digiflazz_mode', $data)) $envUpdates['DIGIFLAZZ_MODE'] = $data['digiflazz_mode'] ?? 'development';

        if (!empty($envUpdates)) {
            $this->updateEnv($envUpdates);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function updateEnv(array $data)
    {
        $envPath = base_path('.env');
        if (!file_exists($envPath)) {
            return;
        }

        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            // Check if key exists
            $pattern = "/^{$key}=.*/m";
            
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $content);
    }
}
