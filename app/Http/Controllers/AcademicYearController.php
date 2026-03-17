<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('admin.academic_years.index', compact('academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'sometimes|boolean'
        ]);

        DB::beginTransaction();
        try {
            $isActive = $request->has('is_active');

            if ($isActive) {
                // Deactivate all others
                AcademicYear::query()->update(['is_active' => false]);
            }

            AcademicYear::create([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $isActive
            ]);

            DB::commit();
            return redirect()->route('academic-years.index')->with('success', 'Tahun Ajaran berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_years,name,' . $academicYear->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'sometimes|boolean'
        ]);

        DB::beginTransaction();
        try {
            $isActive = $request->has('is_active');

            if ($isActive && !$academicYear->is_active) {
                // If it's being set to active, deactivate all others
                AcademicYear::query()->update(['is_active' => false]);
            }

            $academicYear->update([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => $isActive
            ]);

            DB::commit();
            return redirect()->route('academic-years.index')->with('success', 'Tahun Ajaran berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memperbarui data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, AcademicYear $academicYear)
    {
        if ($request->user()->cannot('master_data.manage')) {
            abort(403);
        }

        if ($academicYear->is_active) {
            return back()->with('error', 'Tahun ajaran aktif tidak dapat dihapus. Nonaktifkan terlebih dahulu.');
        }

        $academicYear->delete();
        return redirect()->route('academic-years.index')->with('success', 'Tahun Ajaran berhasil dihapus.');
    }
}
