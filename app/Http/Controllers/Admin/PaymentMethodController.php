<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $payments = PaymentMethod::latest()->get();
        return Inertia::render('admin/payments/index', [
            'payments' => $payments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:payment_methods,code',
            'type' => 'required|string',
            'fee_flat' => 'required|numeric|min:0',
            'fee_percent' => 'required|numeric|min:0',
            'image_file' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->only(['name', 'code', 'type', 'fee_flat', 'fee_percent', 'is_active']);

        if ($request->hasFile('image_file')) {
            $data['image'] = $request->file('image_file')->store('payments', 'public');
        }

        PaymentMethod::create($data);

        return back()->with('success', 'Metode pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentMethod $payment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:payment_methods,code,' . $payment->id,
            'type' => 'required|string',
            'fee_flat' => 'required|numeric|min:0',
            'fee_percent' => 'required|numeric|min:0',
            'image_file' => 'nullable|image|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $data = $request->only(['name', 'code', 'type', 'fee_flat', 'fee_percent', 'is_active']);

        if ($request->hasFile('image_file')) {
            // Delete old image if exists
            if ($payment->image) {
                Storage::disk('public')->delete($payment->image);
            }
            $data['image'] = $request->file('image_file')->store('payments', 'public');
        }

        $payment->update($data);

        return back()->with('success', 'Metode pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentMethod $payment)
    {
        if ($payment->image) {
            Storage::disk('public')->delete($payment->image);
        }
        $payment->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    public function toggle(PaymentMethod $payment)
    {
        $payment->update(['is_active' => !$payment->is_active]);
        return back()->with('success', 'Status metode pembayaran berhasil diubah.');
    }
}
