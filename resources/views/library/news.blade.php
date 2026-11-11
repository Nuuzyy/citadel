@extends('layouts.library')
@section('title', 'Berita & Artikel')
@section('content')
<section class="page-hero"><div class="wrap"><div class="eyebrow"><span></span> CERITA DARI PERPUSTAKAAN</div><h1>Kabar baik,<br><em>selalu dibagikan.</em></h1><p>Berita, agenda, dan inspirasi terbaru dari komunitas pembaca.</p></div></section>
<section class="section wrap"><div class="news-grid news-list">@forelse($news as $article)<a href="{{ route('news.show', $article->id) }}" class="news-card"><img src="{{ $article->image_url }}" alt="{{ $article->title }}" loading="lazy"><div class="news-info"><div class="news-meta"><span>{{ $article->category }}</span><time>{{ \Illuminate\Support\Carbon::parse($article->published_at)->translatedFormat('d M Y') }}</time></div><h3>{{ $article->title }}</h3><p>{{ $article->excerpt }}</p><span class="read-more">Baca selengkapnya <b>↗</b></span></div></a>@empty<p class="empty-note">Belum ada artikel.</p>@endforelse</div><div class="pagination-wrap">{{ $news->links() }}</div></section>
@endsection
