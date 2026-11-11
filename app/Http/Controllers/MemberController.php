<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function dashboard(Request $request): View
    {
        abort_unless($request->user()->role === 'member', 403);
        $borrowings = $request->user()->borrowings()->with(['details.book', 'fine'])->latest()->get();

        return view('member.dashboard', [
            'borrowings' => $borrowings,
            'activeCount' => $borrowings->whereIn('status', ['active', 'pending'])->count(),
            'returnedCount' => $borrowings->where('status', 'returned')->count(),
            'unpaidFines' => $borrowings->sum(fn ($borrowing) => $borrowing->fine && ! $borrowing->fine->is_paid ? $borrowing->fine->amount : 0),
        ]);
    }

    public function borrow(Request $request, Book $book): RedirectResponse
    {
        abort_if($request->user()->role !== 'member', 403);

        $hasOpenRequest = $request->user()->borrowings()
            ->whereIn('status', ['pending', 'active'])
            ->whereHas('details', fn ($query) => $query->where('book_id', $book->id))
            ->exists();
        $openCount = $request->user()->borrowings()->whereIn('status', ['pending', 'active'])->count();

        if ($hasOpenRequest) {
            return back()->withErrors(['borrow' => 'Anda sudah memiliki permintaan atau peminjaman aktif untuk buku ini.']);
        }
        if ($openCount >= 3) {
            return back()->withErrors(['borrow' => 'Maksimal 3 buku dapat dipinjam atau diajukan secara bersamaan.']);
        }
        if ($book->stock < 1) {
            return back()->withErrors(['borrow' => 'Maaf, stok buku sedang habis.']);
        }

        DB::transaction(function () use ($request, $book) {
            $borrowing = Borrowing::create(['user_id' => $request->user()->id, 'status' => 'pending']);
            BorrowingDetail::create(['borrowing_id' => $borrowing->id, 'book_id' => $book->id]);
        });

        return back()->with('success', 'Permintaan peminjaman dikirim. Admin akan segera memeriksanya.');
    }

    public function renew(Request $request, Borrowing $borrowing): RedirectResponse
    {
        abort_unless($borrowing->user_id === $request->user()->id, 403);

        if ($borrowing->status !== 'active' || $borrowing->renewed_at || $borrowing->due_at->lt(today())) {
            return back()->withErrors(['renew' => 'Perpanjangan hanya tersedia satu kali untuk peminjaman aktif yang belum terlambat.']);
        }

        $borrowing->update(['due_at' => $borrowing->due_at->copy()->addDays(7), 'renewed_at' => now()]);

        return back()->with('success', 'Masa pinjam berhasil diperpanjang selama 7 hari.');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
        ]);
        $request->user()->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
