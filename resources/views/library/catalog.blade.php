@extends('layouts.library')
@section('title', 'Katalog Buku')
@section('content')
<section class="page-hero"><div class="wrap"><div class="eyebrow"><span></span> PERPUSTAKAAN DIGITAL</div><h1>Temukan bacaan<br><em>berikutnya.</em></h1><p>Jelajahi koleksi pilihan dari berbagai kategori dan penulis.</p></div></section>
<section class="catalog-section wrap">
    <form method="get" action="{{ route('catalog') }}" class="filter-panel">
        <label class="catalog-search"><span>⌕</span><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari judul buku..."></label>
        <select name="category" aria-label="Filter kategori"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select>
        <select name="author" aria-label="Filter penulis"><option value="">Semua penulis</option>@foreach($authors as $author)<option value="{{ $author->name }}" @selected(($filters['author'] ?? '') === $author->name)>{{ $author->name }}</option>@endforeach</select>
        <input class="year-filter" type="number" name="year" min="1000" max="{{ date('Y') }}" value="{{ $filters['year'] ?? '' }}" placeholder="Tahun" aria-label="Tahun terbit">
        <select name="availability" aria-label="Ketersediaan"><option value="">Semua status</option><option value="available" @selected(($filters['availability'] ?? '') === 'available')>Tersedia</option><option value="unavailable" @selected(($filters['availability'] ?? '') === 'unavailable')>Sedang dipinjam</option></select>
        <select name="sort" aria-label="Urutkan"><option value="newest">Terbaru</option><option value="popular" @selected(($filters['sort'] ?? '') === 'popular')>Terpopuler</option><option value="title" @selected(($filters['sort'] ?? '') === 'title')>Judul A–Z</option></select>
        <button type="submit" class="button button-navy">Terapkan</button>
    </form>
    <div class="catalog-result"><span><strong>{{ $books->total() }}</strong> koleksi ditemukan</span><a href="{{ route('catalog') }}">Atur ulang ↺</a></div>
    @if($books->count())<div class="book-grid catalog-grid">@foreach($books as $book)@include('library.partials.book-card', ['book' => $book])@endforeach</div>@else<div class="no-results"><span>⌕</span><h2>Buku belum ditemukan</h2><p>Coba ubah kata kunci atau filter pencarianmu.</p><a class="button button-navy" href="{{ route('catalog') }}">Lihat semua koleksi</a></div>@endif
    <div class="pagination-wrap">{{ $books->links() }}</div>
</section>
@endsection
