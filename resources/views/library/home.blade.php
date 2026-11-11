@extends('layouts.library')

@section('title', 'Beranda')

@section('content')
<section class="hero">
    <div class="hero-image"></div>
    <div class="wrap hero-content">
        <div class="eyebrow eyebrow-light"><span></span> PERPUSTAKAAN DIGITAL</div>
        <h1>Jelajahi dunia,<br>mulai dari <em>halamanmu.</em></h1>
        <p>Temukan bacaan yang membuka perspektif baru. Ribuan cerita dan pengetahuan menunggumu di sini.</p>
        <form class="hero-search" action="{{ route('catalog') }}" method="get">
            <span class="search-icon">⌕</span>
            <input type="search" name="q" placeholder="Cari judul, penulis, atau ISBN..." aria-label="Cari buku">
            <button type="submit">Cari buku <span>↗</span></button>
        </form>
        <div class="hero-actions"><a class="button button-gold" href="{{ route('catalog') }}">Jelajahi koleksi <span>→</span></a><a class="button button-outline" href="{{ route('register') }}">Daftar anggota</a></div>
        <div class="hero-note"><span class="avatars"><i>R</i><i>A</i><i>D</i></span><span>Temukan bacaan favoritmu,<br><strong>bersama komunitas pembaca.</strong></span></div>
    </div>
    <div class="hero-caption">01 — RUANG BACA UTAMA</div>
</section>

<section class="stats wrap" aria-label="Statistik perpustakaan">
    <div><strong>{{ number_format($stats['books']) }}<i>+</i></strong><span>Koleksi buku</span></div>
    <div><strong>{{ number_format($stats['members']) }}<i>+</i></strong><span>Anggota aktif</span></div>
    <div><strong>{{ number_format($stats['borrowed']) }}</strong><span>Buku dipinjam</span></div>
    <div><strong>{{ number_format($stats['categories']) }}</strong><span>Kategori pilihan</span></div>
</section>

<section class="section section-books">
    <div class="wrap">
        <div class="section-heading"><div><div class="eyebrow"><span></span> PILIHAN MINGGU INI</div><h2>Baru di rak <em>kami.</em></h2></div><a class="text-link" href="{{ route('catalog') }}">Lihat semua koleksi <span>↗</span></a></div>
        <div class="book-grid">
            @foreach($latestBooks as $book)
                @include('library.partials.book-card', ['book' => $book])
            @endforeach
        </div>
    </div>
</section>

<section class="popular-section">
    <div class="wrap popular-inner"><div class="popular-copy"><div class="eyebrow eyebrow-light"><span></span> PALING DISUKAI</div><h2>Cerita yang<br><em>terus dibagikan.</em></h2><p>Bacaan pilihan yang paling sering menemani perjalanan anggota kami. Mungkin salah satunya akan jadi favoritmu berikutnya.</p><a class="button button-gold" href="{{ route('catalog', ['sort' => 'popular']) }}">Temukan buku populer <span>→</span></a></div>
        <div class="popular-books">@foreach($popularBooks->take(3) as $index => $book)<a href="{{ route('books.show', $book) }}" class="popular-book"><span class="popular-number">0{{ $index + 1 }}</span><img src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}"><span class="popular-meta"><strong>{{ $book->title }}</strong><small>{{ $book->author->name }} · {{ $book->borrow_count }} kali dipinjam</small></span><span class="popular-arrow">↗</span></a>@endforeach</div>
    </div>
</section>

<section class="section categories-section" id="kategori">
    <div class="wrap"><div class="section-heading"><div><div class="eyebrow"><span></span> TEMUKAN MINATMU</div><h2>Ruang untuk setiap <em>rasa ingin tahu.</em></h2></div><a class="text-link" href="{{ route('catalog') }}">Jelajahi kategori <span>↗</span></a></div>
        <div class="category-grid">@foreach($categories as $category)<a href="{{ route('catalog', ['category' => $category->id]) }}" class="category-card"><span class="category-icon">{{ $category->icon }}</span><strong>{{ $category->name }}</strong><small>{{ $category->books_count }} buku</small><span class="category-arrow">↗</span></a>@endforeach</div>
    </div>
</section>

<section class="reading-banner"><div class="reading-photo"></div><div class="wrap reading-content"><div class="eyebrow eyebrow-light"><span></span> LEBIH DARI SEKADAR BUKU</div><h2>Temukan ruang<br>untuk <em>bertumbuh.</em></h2><p>Jadi bagian dari komunitas pembaca yang percaya bahwa setiap halaman bisa mengubah cara kita melihat dunia.</p><a class="button button-gold" href="{{ route('register') }}">Mulai keanggotaan <span>→</span></a></div></section>

<section class="section news-section"><div class="wrap"><div class="section-heading"><div><div class="eyebrow"><span></span> CERITA DARI PERPUSTAKAAN</div><h2>Kabar & <em>inspirasi.</em></h2></div><a class="text-link" href="{{ route('news') }}">Lihat semua berita <span>↗</span></a></div>
    <div class="news-grid">@forelse($news as $article)<a href="{{ route('news.show', $article->id) }}" class="news-card"><img src="{{ $article->image_url }}" alt=""><div class="news-info"><div class="news-meta"><span>{{ $article->category }}</span><time>{{ \Illuminate\Support\Carbon::parse($article->published_at)->translatedFormat('d M Y') }}</time></div><h3>{{ $article->title }}</h3><p>{{ $article->excerpt }}</p><span class="read-more">Baca selengkapnya <b>↗</b></span></div></a>@empty<p class="empty-note">Berita terbaru akan segera hadir.</p>@endforelse</div>
</div></section>
<section class="quote-strip"><span>“</span><p>Sebuah buku adalah mimpi yang kamu genggam di tanganmu.</p><small>— Neil Gaiman</small></section>
@endsection
