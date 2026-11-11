<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\BorrowingDetail;
use App\Models\Category;
use App\Models\Fine;
use App\Models\Publisher;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'books' => Book::count(),
                'members' => User::where('role', 'member')->count(),
                'borrowings' => Borrowing::count(),
                'returned' => Borrowing::where('status', 'returned')->count(),
                'late' => Borrowing::where('status', 'active')->whereDate('due_at', '<', today())->count(),
            ],
            'borrowings' => Borrowing::with(['user', 'details.book'])->latest()->take(12)->get(),
            'books' => Book::with(['author', 'category'])->latest()->take(5)->get(),
            'monthlyBorrowings' => collect(range(5, 0))->map(function (int $monthsAgo) {
                $month = now()->startOfMonth()->subMonths($monthsAgo);

                return [
                    'label' => $month->translatedFormat('M'),
                    'count' => Borrowing::whereBetween('created_at', [$month, $month->copy()->endOfMonth()])->count(),
                ];
            }),
        ]);
    }

    public function export(string $type): StreamedResponse
    {
        $columns = [
            'books' => ['ISBN', 'Judul', 'Penulis', 'Kategori', 'Tahun', 'Stok', 'Total dipinjam'],
            'members' => ['Nomor anggota', 'Nama', 'Email', 'Telepon', 'Status'],
            'borrowings' => ['ID', 'Anggota', 'Buku', 'Status', 'Tanggal pinjam', 'Batas kembali', 'Tanggal kembali'],
            'returns' => ['ID peminjaman', 'Anggota', 'Buku', 'Tanggal kembali'],
            'fines' => ['ID peminjaman', 'Anggota', 'Jumlah denda', 'Status pembayaran'],
        ];
        abort_unless(array_key_exists($type, $columns), 404);

        return response()->streamDownload(function () use ($type, $columns) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns[$type]);
            $writeRow = static function (array $values) use ($output): void {
                $values = array_map(
                    fn ($value) => is_string($value) && preg_match('/^[=+\-@]/', $value) ? "'".$value : $value,
                    $values,
                );
                fputcsv($output, $values);
            };

            if ($type === 'books') {
                Book::with(['author', 'category'])->orderBy('title')->each(fn (Book $book) => $writeRow([$book->isbn, $book->title, $book->author->name, $book->category->name, $book->published_year, $book->stock, $book->borrow_count]));
            } elseif ($type === 'members') {
                User::with('member')->where('role', 'member')->orderBy('name')->each(fn (User $user) => $writeRow([$user->member_number, $user->name, $user->email, $user->phone, $user->member?->is_active ? 'Aktif' : 'Nonaktif']));
            } elseif ($type === 'borrowings') {
                Borrowing::with(['user', 'details.book'])->orderBy('id')->each(fn (Borrowing $borrowing) => $writeRow([$borrowing->id, $borrowing->user->name, $borrowing->details->first()?->book?->title, $borrowing->status, $borrowing->borrowed_at?->format('Y-m-d'), $borrowing->due_at?->format('Y-m-d'), $borrowing->returned_at?->format('Y-m-d')]));
            } elseif ($type === 'returns') {
                DB::table('returns')->join('borrowings', 'returns.borrowing_id', '=', 'borrowings.id')->join('users', 'borrowings.user_id', '=', 'users.id')->join('borrowing_details', 'borrowings.id', '=', 'borrowing_details.borrowing_id')->join('books', 'borrowing_details.book_id', '=', 'books.id')->orderBy('returns.returned_at')->select('borrowings.id', 'users.name', 'books.title', 'returns.returned_at')->cursor()->each(fn ($row) => $writeRow((array) $row));
            } else {
                DB::table('fines')->join('borrowings', 'fines.borrowing_id', '=', 'borrowings.id')->join('users', 'borrowings.user_id', '=', 'users.id')->orderBy('fines.id')->select('borrowings.id', 'users.name', 'fines.amount', 'fines.is_paid')->cursor()->each(fn ($row) => $writeRow([$row->id, $row->name, $row->amount, $row->is_paid ? 'Lunas' : 'Belum lunas']));
            }

            fclose($output);
        }, "laporan-{$type}-".today()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function books(): View
    {
        return view('admin.books', ['books' => Book::with(['author', 'category', 'publisher'])->latest()->paginate(12), 'categories' => Category::orderBy('name')->get()]);
    }

    public function members(): View
    {
        return view('admin.members', ['members' => User::with('member')->where('role', 'member')->latest()->paginate(15)]);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'member_number' => ['required', 'string', 'max:40', 'unique:users,member_number'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        DB::transaction(function () use ($data) {
            $user = User::create([...$data, 'password' => Hash::make($data['password']), 'role' => 'member']);
            Member::create(['user_id' => $user->id, 'identity_number' => $data['member_number']]);
        });

        return back()->with('success', 'Akun anggota berhasil dibuat.');
    }

    public function toggleMember(User $user): RedirectResponse
    {
        abort_unless($user->role === 'member' && $user->member, 404);
        $user->member->update(['is_active' => ! $user->member->is_active]);

        return back()->with('success', 'Status akun anggota berhasil diperbarui.');
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $data = $this->validatedBook($request);
        $data['author_id'] = Author::firstOrCreate(['name' => $data['author']])->id;
        $data['publisher_id'] = Publisher::firstOrCreate(['name' => $data['publisher']])->id;
        unset($data['author'], $data['publisher']);
        Book::create($data);

        return back()->with('success', 'Buku berhasil ditambahkan ke katalog.');
    }

    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        $data = $this->validatedBook($request, $book);
        $data['author_id'] = Author::firstOrCreate(['name' => $data['author']])->id;
        $data['publisher_id'] = Publisher::firstOrCreate(['name' => $data['publisher']])->id;
        unset($data['author'], $data['publisher']);
        $book->update($data);

        return back()->with('success', 'Data buku berhasil diperbarui.');
    }

    public function deleteBook(Book $book): RedirectResponse
    {
        if ($book->borrowingDetails()->exists()) {
            return back()->withErrors(['book' => 'Buku yang memiliki riwayat transaksi tidak dapat dihapus.']);
        }
        $book->delete();

        return back()->with('success', 'Buku berhasil dihapus.');
    }

    public function approve(Borrowing $borrowing): RedirectResponse
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing = Borrowing::query()->lockForUpdate()->findOrFail($borrowing->id);
            abort_unless($borrowing->status === 'pending', 422, 'Permintaan ini sudah diproses.');
            $detail = BorrowingDetail::query()->where('borrowing_id', $borrowing->id)->firstOrFail();
            $book = Book::query()->lockForUpdate()->findOrFail($detail->book_id);
            abort_if($book->stock < $detail->quantity, 422, 'Stok buku tidak mencukupi.');
            $book->decrement('stock', $detail->quantity);
            $book->increment('borrow_count');
            $borrowing->update(['status' => 'active', 'borrowed_at' => today(), 'due_at' => today()->addDays(14)]);
        });

        return back()->with('success', 'Peminjaman disetujui. Batas pengembalian ditetapkan 14 hari.');
    }

    public function reject(Borrowing $borrowing): RedirectResponse
    {
        abort_unless($borrowing->status === 'pending', 422, 'Hanya permintaan yang menunggu dapat ditolak.');
        $borrowing->update(['status' => 'rejected']);

        return back()->with('success', 'Permintaan peminjaman ditolak.');
    }

    public function returnBook(Borrowing $borrowing): RedirectResponse
    {
        DB::transaction(function () use ($borrowing) {
            $borrowing = Borrowing::query()->lockForUpdate()->findOrFail($borrowing->id);
            abort_unless($borrowing->status === 'active', 422, 'Hanya peminjaman aktif yang dapat dikembalikan.');
            $detail = BorrowingDetail::query()->where('borrowing_id', $borrowing->id)->firstOrFail();
            Book::query()->whereKey($detail->book_id)->increment('stock', $detail->quantity);
            $returnedAt = today();
            $borrowing->update(['status' => 'returned', 'returned_at' => $returnedAt]);
            DB::table('returns')->insert(['borrowing_id' => $borrowing->id, 'returned_at' => $returnedAt, 'created_at' => now(), 'updated_at' => now()]);

            $daysLate = max(0, $borrowing->due_at->diffInDays($returnedAt, false));
            if ($daysLate > 0) {
                Fine::create(['borrowing_id' => $borrowing->id, 'amount' => $daysLate * 1000]);
            }
        });

        return back()->with('success', 'Pengembalian berhasil diproses. Denda keterlambatan dihitung Rp1.000 per hari.');
    }

    private function validatedBook(Request $request, ?Book $book = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'category_id' => ['required', 'exists:categories,id'],
            'author' => ['required', 'string', 'max:120'],
            'publisher' => ['required', 'string', 'max:120'],
            'isbn' => ['required', 'string', 'max:30', 'unique:books,isbn'.($book ? ','.$book->id : '')],
            'published_year' => ['required', 'integer', 'between:1000,'.date('Y')],
            'pages' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'description' => ['required', 'string', 'max:10000'],
            'cover_url' => ['required', 'url', 'max:500'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
    }
}
