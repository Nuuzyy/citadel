<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LibraryController::class, 'home'])->name('home');
Route::get('/katalog', [LibraryController::class, 'catalog'])->name('catalog');
Route::get('/buku/{book}', [LibraryController::class, 'show'])->name('books.show');
Route::get('/berita', [LibraryController::class, 'news'])->name('news');
Route::get('/berita/{article}', [LibraryController::class, 'showNews'])->name('news.show');
Route::get('/tentang', [LibraryController::class, 'about'])->name('about');
Route::get('/kontak', [LibraryController::class, 'contact'])->name('contact');
Route::post('/kontak', [LibraryController::class, 'sendMessage'])->name('contact.send');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->name('login.store');
    Route::get('/daftar', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->name('register.store');
    Route::get('/lupa-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/akun', [MemberController::class, 'dashboard'])->name('member.dashboard');
    Route::post('/buku/{book}/pinjam', [MemberController::class, 'borrow'])->name('borrowings.store');
    Route::post('/peminjaman/{borrowing}/perpanjang', [MemberController::class, 'renew'])->name('borrowings.renew');
    Route::put('/profil', [MemberController::class, 'updateProfile'])->name('profile.update');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/laporan/{type}', [AdminController::class, 'export'])->whereIn('type', ['books', 'members', 'borrowings', 'returns', 'fines'])->name('reports.export');
    Route::get('/buku', [AdminController::class, 'books'])->name('books');
    Route::get('/anggota', [AdminController::class, 'members'])->name('members');
    Route::post('/anggota', [AdminController::class, 'storeMember'])->name('members.store');
    Route::patch('/anggota/{user}/status', [AdminController::class, 'toggleMember'])->name('members.toggle');
    Route::post('/buku', [AdminController::class, 'storeBook'])->name('books.store');
    Route::put('/buku/{book}', [AdminController::class, 'updateBook'])->name('books.update');
    Route::delete('/buku/{book}', [AdminController::class, 'deleteBook'])->name('books.delete');
    Route::post('/peminjaman/{borrowing}/setujui', [AdminController::class, 'approve'])->name('borrowings.approve');
    Route::post('/peminjaman/{borrowing}/tolak', [AdminController::class, 'reject'])->name('borrowings.reject');
    Route::post('/peminjaman/{borrowing}/kembali', [AdminController::class, 'returnBook'])->name('borrowings.return');
});
