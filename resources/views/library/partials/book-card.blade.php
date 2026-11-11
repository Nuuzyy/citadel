<article class="book-card">
    <a class="book-cover" href="{{ route('books.show', $book) }}">
        <img src="{{ $book->cover_url }}" alt="Sampul {{ $book->title }}" loading="lazy">
        <span class="book-category">{{ $book->category->name }}</span>
        @if($book->stock < 1)<span class="availability unavailable">Dipinjam</span>@else<span class="availability">Tersedia</span>@endif
    </a>
    <div class="book-info"><span class="book-year">{{ $book->published_year }} &nbsp;·&nbsp; {{ $book->category->name }}</span><h3><a href="{{ route('books.show', $book) }}">{{ $book->title }}</a></h3><p>{{ $book->author->name }}</p><div class="book-foot"><span>{{ $book->stock }} eksemplar</span><a href="{{ route('books.show', $book) }}" aria-label="Lihat detail {{ $book->title }}">↗</a></div></div>
</article>
