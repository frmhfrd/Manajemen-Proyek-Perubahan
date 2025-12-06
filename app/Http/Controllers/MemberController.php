<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query();

        // Fitur Search (Cari Nama atau NIS/NIP)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('kode_anggota', 'like', "%{$search}%");
        }

        $members = $query->latest()->paginate(10);
        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_anggota' => 'required|unique:members,kode_anggota|max:20',
            'nama_lengkap' => 'required|string|max:100',
            'tipe_anggota' => 'required|in:siswa,guru,staf',
            'kelas'        => 'nullable|string|max:10', // Boleh kosong kalau Guru
            'no_telepon'   => 'nullable|string|max:15',
            'alamat'       => 'nullable|string',
        ]);

        // Set default aktif
        $validated['status_aktif'] = true;

        Member::create($validated);

        return redirect()->route('members.index')->with('success', 'Anggota berhasil didaftarkan!');
    }

    public function edit(string $id)
    {
        $member = Member::findOrFail($id);
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'kode_anggota' => 'required|max:20|unique:members,kode_anggota,'.$id,
            'nama_lengkap' => 'required|string|max:100',
            'tipe_anggota' => 'required|in:siswa,guru,staf',
            'kelas'        => 'nullable|string|max:10',
            'no_telepon'   => 'nullable|string|max:15',
            'alamat'       => 'nullable|string',
            'status_aktif' => 'required|boolean',
        ]);

        $member = Member::findOrFail($id);
        $member->update($validated);

        return redirect()->route('members.index')->with('success', 'Data anggota berhasil diperbarui!');
    }

    public function destroy(string $id)
    {
        $member = Member::findOrFail($id);

        // Cek relasi dulu, kalau pernah pinjam buku, jangan dihapus fisik
        if($member->loans()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus anggota ini karena memiliki riwayat peminjaman. Non-aktifkan saja statusnya.');
        }

        $member->delete();
        return redirect()->route('members.index')->with('success', 'Anggota berhasil dihapus!');
    }

    // 1. Halaman Sampah
    public function trash()
    {
        $members = Member::onlyTrashed()->latest()->paginate(10);
        return view('members.trash', compact('members'));
    }

    // 2. Pulihkan (Restore)
    public function restore($id)
    {
        $member = Member::withTrashed()->findOrFail($id);
        $member->restore();
        return redirect()->route('members.trash')->with('success', 'Data anggota berhasil dipulihkan.');
    }

    // 3. Hapus Permanen
    public function forceDelete($id)
    {
        $member = Member::withTrashed()->findOrFail($id);

        // Cek dulu apakah anggota ini punya hutang buku? (Optional Safety)
        if($member->loans()->count() > 0) {
            return back()->with('error', 'GAGAL: Anggota ini memiliki riwayat peminjaman di database. Hapus data transaksinya dulu jika ingin menghapus permanen.');
        }

        $member->forceDelete();
        return redirect()->route('members.trash')->with('success', 'Data anggota dihapus permanen.');
    }
}
