@extends('layouts.library')
@section('title', $book->title)
@section('content')
<section class="detail-section wrap">
    <div class="breadcrumbs"><a href="{{ route('home') }}">Beranda</a><span>›</span><a href="{{ route('catalog') }}">Katalog</a><span>›</span><span>{{ $book->title }}</span></div>
    <div class="detail-layout"><div class="detail-cover"><img src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}"><span>{{ $book->category->name }}</span></div>
        <div class="detail-copy"><div class="eyebrow"><span></span> DETAIL KOLEKSI</div><h1>{{ $book->title }}</h1><p class="detail-author">Oleh <strong>{{ $book->author->name }}</strong></p><div class="detail-rating"><span>★★★★★</span> <small>Rekomendasi pembaca</small></div>
            <p class="detail-description">{{ $book->description }}</p>
            <div class="detail-facts"><div><small>PENERBIT</small><strong>{{ $book->publisher?->name ?? '—' }}</strong></div><div><small>TAHUN TERBIT</small><strong>{{ $book->published_year }}</strong></div><div><small>ISBN</small><strong>{{ $book->isbn }}</strong></div><div><small>JUMLAH HALAMAN</small><strong>{{ $book->pages ?? '—' }} halaman</strong></div></div>
            <div class="stock-row"><span class="stock-indicator {{ $book->stock ? '' : 'stock-empty' }}"></span><span>{{ $book->stock ? "Tersedia · {$book->stock} eksemplar" : 'Stok sedang habis' }}</span><span class="loan-length">Masa pinjam 14 hari</span></div>
            @if($book->stock > 0)@auth @if(auth()->user()->role === 'member')<form method="post" action="{{ route('borrowings.store', $book) }}">@csrf<button class="button button-navy" type="submit">Ajukan peminjaman <span>→</span></button></form>@endif @else<a class="button button-navy" href="{{ route('login', ['redirect' => url()->current()]) }}">Masuk untuk meminjam <span>→</span></a>@endauth @endif
        </div></div>
</section>
@if($recommendations->isNotEmpty())<section class="section section-books"><div class="wrap"><div class="section-heading"><div><div class="eyebrow"><span></span> MUNGKIN KAMU SUKA</div><h2>Jelajahi <em>lebih jauh.</em></h2></div></div><div class="book-grid">@foreach($recommendations as $book)@include('library.partials.book-card', ['book' => $book])@endforeach</div></div></section>@endif
@endsection
