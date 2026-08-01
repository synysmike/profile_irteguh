<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jasa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JasaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $jasas = Jasa::query()->orderBy('name')->get();

        $usage = [];
        if (Schema::hasTable('assignment_letter_jasa_items')) {
            $usage = DB::table('assignment_letter_jasa_items')
                ->select('jasa_id', DB::raw('count(*) as aggregate'))
                ->whereNotNull('jasa_id')
                ->groupBy('jasa_id')
                ->pluck('aggregate', 'jasa_id')
                ->all();
        }

        foreach ($jasas as $jasa) {
            $jasa->assignment_letter_items_count = (int) ($usage[$jasa->id] ?? 0);
        }

        return view('admin.keuangan.jasa.index', compact('jasas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateJasa($request);
        Jasa::create($validated);

        return redirect()->route('admin.keuangan.jasa.index')
            ->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function update(Request $request, Jasa $jasa): RedirectResponse
    {
        $validated = $this->validateJasa($request, $jasa);
        $jasa->update($validated);

        return redirect()->route('admin.keuangan.jasa.index')
            ->with('success', 'Jasa berhasil diperbarui.');
    }

    public function destroy(Jasa $jasa): RedirectResponse
    {
        if (Schema::hasTable('assignment_letter_jasa_items')) {
            $inUse = DB::table('assignment_letter_jasa_items')
                ->where('jasa_id', $jasa->id)
                ->exists();

            if ($inUse) {
                return redirect()->route('admin.keuangan.jasa.index')
                    ->with('error', 'Jasa tidak bisa dihapus karena sudah dipakai pada surat tugas.');
            }
        }

        $jasa->delete();

        return redirect()->route('admin.keuangan.jasa.index')
            ->with('success', 'Jasa berhasil dihapus.');
    }

    private function validateJasa(Request $request, ?Jasa $jasa = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:30|unique:jasas,code' . ($jasa ? ',' . $jasa->id : ''),
            'unit_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
