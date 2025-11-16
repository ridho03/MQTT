<?php

namespace App\Http\Controllers;

use App\Models\CarbonCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CarbonCreditController extends Controller
{
    /* ================================
     * LIST & CRUD DASAR
     * ================================ */

    public function index()
    {
        if (Auth::user()->role === 'admin') {
            $carbonCredits = CarbonCredit::with('owner')
                ->where('owner_id', '!=', Auth::id())
                ->latest()
                ->paginate(10);
        } else {
            $carbonCredits = CarbonCredit::where('owner_id', Auth::id())
                ->latest()
                ->paginate(10);
        }

        return view('carbon_credits.index', compact('carbonCredits'));
    }

    public function vehicles()
    {
        if (Auth::user()->role === 'admin') {
            $vehicles = CarbonCredit::with(['owner' => function ($query) {
                    $query->where('role', '!=', 'admin');
                }])
                ->whereHas('owner', function ($query) {
                    $query->where('role', '!=', 'admin');
                })
                ->latest()
                ->paginate(10);
        } else {
            $vehicles = CarbonCredit::where('owner_id', Auth::id())
                ->latest()
                ->paginate(10);
        }

        return view('carbon_credits.vehicles', compact('vehicles'));
    }

    public function create()
    {
        return view('carbon_credits.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pemilik_kendaraan'   => 'required|in:milik sendiri,milik keluarga satu kk',
            'nrkb'                => 'required|string|max:255',
            'nomor_rangka_5digit' => 'required|string|size:5',
            'vehicle_type'        => 'required|in:car,motorcycle',
        ]);

        $validated['owner_id']       = Auth::id();
        $validated['status']         = Auth::user()->role === 'admin' ? 'available' : 'pending';
        $validated['price_per_unit'] = 100; // harga per unit tetap

        // Set KK & NIK
        $user = Auth::user();
        if ($validated['pemilik_kendaraan'] === 'milik sendiri') {
            $validated['nomor_kartu_keluarga'] = $user->nomor_kartu_keluarga;
            $validated['nik_e_ktp']            = $user->nik_e_ktp;
        } elseif ($validated['pemilik_kendaraan'] === 'milik keluarga satu kk') {
            $validated['nomor_kartu_keluarga'] = $user->nomor_kartu_keluarga;
            $validated['nik_e_ktp']            = $request->input('nik_e_ktp');
        } else {
            $validated['nomor_kartu_keluarga'] = $request->input('nomor_kartu_keluarga');
            $validated['nik_e_ktp']            = $request->input('nik_e_ktp');
        }

        CarbonCredit::create($validated);

        return redirect()->route('carbon-credits.index')
            ->with('success', 'Kuota karbon berhasil dibuat dan tersedia untuk dijual.');
    }

    public function show(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('carbon_credits.show', compact('carbonCredit'));
    }

    public function edit(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('carbon_credits.edit', compact('carbonCredit'));
    }

    public function update(Request $request, CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'nomor_kartu_keluarga' => 'required|string|max:255',
            'pemilik_kendaraan'    => 'required|in:milik sendiri,milik keluarga satu kk',
            'nik_e_ktp'            => 'required|string|max:255',
            'nrkb'                 => 'required|string|max:255',
            'nomor_rangka_5digit'  => 'required|string|size:5',
            'vehicle_type'         => 'required|in:car,motorcycle',
        ]);

        $validated['price_per_unit'] = 100;

        if (Auth::user()->role === 'admin') {
            $validated['status'] = $request->status;
        }

        $carbonCredit->update($validated);

        return redirect()->route('carbon-credits.index')
            ->with('success', 'Kuota karbon berhasil diperbarui.');
    }

    public function approve(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Kuota awal berdasarkan jenis kendaraan
        $initialQuota = 0;
        if ($carbonCredit->vehicle_type === 'car') {
            $initialQuota = 800; // kg CO2eq
        } elseif ($carbonCredit->vehicle_type === 'motorcycle') {
            $initialQuota = 500; // kg CO2eq
        }

        $carbonCredit->update([
            'status' => 'available',
            'amount' => $initialQuota,
        ]);

        return redirect()->route('carbon-credits.index')
            ->with('success', 'Kuota karbon berhasil disetujui dan kuota awal telah diberikan.');
    }

    public function reject(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $carbonCredit->update(['status' => 'rejected']);

        return redirect()->route('carbon-credits.index')
            ->with('success', 'Kuota karbon ditolak.');
    }

    public function setAvailable(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $carbonCredit->update(['status' => 'available']);

        return redirect()->route('carbon-credits.index')
            ->with('success', 'Kuota karbon tersedia untuk dijual.');
    }

    /* ================================
     * USER – AJUKAN JUAL
     * ================================ */

    // Tampilkan form JUAL
    public function requestSale(CarbonCredit $carbonCredit)
    {
        if (auth()->id() !== $carbonCredit->owner_id) {
            abort(403, 'Anda tidak berhak mengajukan penjualan kuota ini.');
        }

        if ($carbonCredit->status !== 'available' && $carbonCredit->status !== 'approved') {
            return redirect()->route('carbon-credits.index')
                ->with('error', 'Kuota ini belum bisa dijual.');
        }

        return view('carbon_credits.request_sale', compact('carbonCredit'));
    }

    // Terima form JUAL
    public function submitSaleRequest(Request $request, CarbonCredit $carbonCredit)
    {
        if (auth()->id() !== $carbonCredit->owner_id) {
            abort(403, 'Anda tidak berhak mengajukan penjualan.');
        }

        $request->validate([
            'quantity_to_sell' => 'required|numeric|min:0.01',
        ]);

        $effective = $carbonCredit->effective_quota;

        if ($request->quantity_to_sell > $effective) {
            return back()->with('error', 'Jumlah yang ingin dijual melebihi kuota efektif yang tersedia.');
        }

        $carbonCredit->update([
            'status'              => 'pending_sale',
            'quantity_to_sell'    => $request->quantity_to_sell,
            'sale_price_per_unit' => $carbonCredit->price_per_unit ?? 100,
            'sale_requested_at'   => now(),
        ]);

        return redirect()
            ->route('carbon-credits.index')
            ->with('success', 'Permintaan penjualan berhasil diajukan. Menunggu persetujuan admin.');
    }

    /* ================================
     * USER – AJUKAN BELI
     * ================================ */

    // Tampilkan form BELI
    public function requestBuy(CarbonCredit $carbonCredit)
    {
        // Hanya pemilik kendaraan yang boleh beli kuota ekstra
        if (auth()->id() !== $carbonCredit->owner_id) {
            abort(403, 'Anda tidak berhak mengajukan pembelian untuk kendaraan ini.');
        }

        return view('carbon_credits.request_buy', compact('carbonCredit'));
    }

    // Terima form BELI
    public function submitBuyRequest(Request $request, CarbonCredit $carbonCredit)
    {
        if (auth()->id() !== $carbonCredit->owner_id) {
            abort(403, 'Anda tidak berhak mengajukan pembelian kuota ini.');
        }

        $request->validate([
            'quantity_to_buy' => 'required|numeric|min:0.01',
        ]);

        $carbonCredit->update([
            'status'           => 'pending_buy',
            'quantity_to_buy'  => $request->quantity_to_buy,
            'buy_requested_at' => now(),
        ]);

        return redirect()
            ->route('carbon-credits.index')
            ->with('success', 'Permintaan pembelian kuota telah dikirim ke admin.');
    }

    /* ================================
     * ADMIN – APPROVE PENJUALAN
     * ================================ */

    public function approveSaleRequest(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($carbonCredit->status !== 'pending_sale') {
            return redirect()->route('carbon-credits.index')
                ->with('error', 'Kuota karbon ini tidak dalam status menunggu persetujuan penjualan.');
        }

        // Hitung ulang kuota efektif & batasi quantity_to_sell
        $effectiveQuota = $carbonCredit->effective_quota;
        $cappedQuantity = min($carbonCredit->quantity_to_sell, $effectiveQuota);

        // Kurangi kuota pemilik
        $currentAmount = $carbonCredit->amount ?? 0;
        $newAmount     = max(0, $currentAmount - $cappedQuantity);

        $carbonCredit->update([
            'status'           => 'available',
            'sale_approved_at' => now(),
            'price_per_unit'   => $carbonCredit->sale_price_per_unit ?? $carbonCredit->price_per_unit,
            'quantity_to_sell' => 0,
            'amount'           => $newAmount,
        ]);

        Log::info('Pengajuan penjualan disetujui oleh admin', [
            'carbon_credit_id' => $carbonCredit->id,
            'admin_id'         => Auth::id(),
            'old_amount'       => $currentAmount,
            'sold_quantity'    => $cappedQuantity,
            'new_amount'       => $newAmount,
        ]);

        return redirect()->route('carbon-credits.index')
            ->with('success', 'Pengajuan penjualan disetujui dan kuota pemilik telah dikurangi.');
    }

    /* ================================
     * PANEL ADMIN JUAL & BELI
     * ================================ */

    public function adminTradePanel()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $pendingSales = CarbonCredit::with('owner')
            ->where('status', 'pending_sale')
            ->orderByDesc('sale_requested_at')
            ->get();

        $pendingBuys = CarbonCredit::with('owner')
            ->where('status', 'pending_buy')
            ->orderByDesc('buy_requested_at')
            ->get();

        return view('admin.trade_panel', compact('pendingSales', 'pendingBuys'));
    }

    /* ================================
     * ADMIN – TOLAK PENJUALAN
     * ================================ */

    public function rejectSaleRequest(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($carbonCredit->status !== 'pending_sale') {
            return redirect()->route('admin.trades.index')
                ->with('error', 'Kuota karbon ini tidak dalam status menunggu persetujuan penjualan.');
        }

        $carbonCredit->update([
            'status'              => 'available',
            'quantity_to_sell'    => null,
            'sale_price_per_unit' => null,
            'sale_requested_at'   => null,
            'sale_approved_at'    => null,
        ]);

        return redirect()->route('admin.trades.index')
            ->with('success', 'Pengajuan penjualan ditolak.');
    }

    /* ================================
     * ADMIN – SETUJUI / TOLAK PEMBELIAN
     * ================================ */

    public function approveBuyRequest(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($carbonCredit->status !== 'pending_buy') {
            return redirect()
                ->route('carbon-credits.index')
                ->with('error', 'Kuota karbon ini tidak dalam status menunggu persetujuan pembelian.');
        }

        $buyQty        = $carbonCredit->quantity_to_buy ?? 0;
        $currentAmount = $carbonCredit->amount ?? 0;
        $newAmount     = $currentAmount + $buyQty;

        $carbonCredit->update([
            'status'          => 'available',
            'buy_approved_at' => now(),
            'amount'          => $newAmount,
            'quantity_to_buy' => 0,
        ]);

        Log::info('Permintaan pembelian disetujui oleh admin', [
            'carbon_credit_id' => $carbonCredit->id,
            'admin_id'         => Auth::id(),
            'old_amount'       => $currentAmount,
            'buy_quantity'     => $buyQty,
            'new_amount'       => $newAmount,
        ]);

        return redirect()
            ->route('carbon-credits.index')
            ->with('success', 'Permintaan pembelian kuota disetujui dan kuota pemilik telah ditambah.');
    }

    public function rejectBuyRequest(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($carbonCredit->status !== 'pending_buy') {
            return redirect()
                ->route('carbon-credits.index')
                ->with('error', 'Kuota karbon ini tidak dalam status menunggu persetujuan pembelian.');
        }

        $carbonCredit->update([
            'status'           => 'available',
            'buy_rejected_at'  => now(),
            'quantity_to_buy'  => null,
            'buy_requested_at' => null,
        ]);

        Log::info('Permintaan pembelian ditolak oleh admin', [
            'carbon_credit_id' => $carbonCredit->id,
            'admin_id'         => Auth::id(),
        ]);

        return redirect()
            ->route('carbon-credits.index')
            ->with('success', 'Permintaan pembelian kuota ditolak.');
    }

    /* ================================
     * HAPUS KENDARAAN / KUOTA
     * ================================ */

    public function destroy(CarbonCredit $carbonCredit)
    {
        if (Auth::user()->role !== 'admin' && $carbonCredit->owner_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak menghapus kendaraan ini.');
        }

        $carbonCredit->delete();

        return redirect()
            ->route('carbon-credits.vehicles')
            ->with('success', 'Kendaraan berhasil dihapus.');
    }
}
