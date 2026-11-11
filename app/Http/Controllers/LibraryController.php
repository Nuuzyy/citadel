<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function home(): View
    {
        return view('library.home', [
            'latestBooks' => Book::with(['author', 'category'])->latest()->take(4)->get(),
            'popularBooks' => Book::with(['author', 'category'])->orderByDesc('borrow_count')->take(4)->get(),
            'categories' => Category::withCount('books')->orderBy('name')->get(),
            'news' => DB::table('news')->whereNotNull('published_at')->latest('published_at')->take(3)->get(),
            'stats' => [
                'books' => Book::count(),
                'members' => User::where('role', 'member')->count(),
                'borrowed' => DB::table('borrowings')->where('status', 'active')->count(),
                'categories' => Category::count(),
            ],
        ]);
    }

    public function catalog(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'integer', 'exists:categories,id'],
            'author' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'digits:4'],
            'availability' => ['nullable', 'in:available,unavailable'],
            'sort' => ['nullable', 'in:newest,popular,title'],
        ]);

        $books = Book::with(['author', 'category', 'publisher'])
            ->when($validated['q'] ?? null, fn ($query, $term) => $query->where('title', 'like', "%{$term}%"))
            ->when($validated['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($validated['author'] ?? null, fn ($query, $author) => $query->whereHas('author', fn ($query) => $query->where('name', 'like', "%{$author}%")))
            ->when($validated['year'] ?? null, fn ($query, $year) => $query->where('published_year', $year))
            ->when(($validated['availability'] ?? null) === 'available', fn ($query) => $query->where('stock', '>', 0))
            ->when(($validated['availability'] ?? null) === 'unavailable', fn ($query) => $query->where('stock', 0));

        match ($validated['sort'] ?? 'newest') {
            'popular' => $books->orderByDesc('borrow_count'),
            'title' => $books->orderBy('title'),
            default => $books->orderByDesc('published_year')->orderByDesc('created_at'),
        };

        return view('library.catalog', [
            'books' => $books->paginate(9)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'authors' => DB::table('authors')->orderBy('name')->get(),
            'filters' => $validated,
        ]);
    }

    public function show(Book $book): View
    {
        $book->load(['author', 'category', 'publisher']);

        return view('library.book', [
            'book' => $book,
            'recommendations' => Book::with(['author', 'category'])
                ->where('category_id', $book->category_id)
                ->where('id', '!=', $book->id)
                ->take(4)
                ->get(),
        ]);
    }

    public function news(): View
    {
        return view('library.news', [
            'news' => DB::table('news')->whereNotNull('published_at')->latest('published_at')->paginate(9),
        ]);
    }

    public function showNews(int $article): View
    {
        $article = DB::table('news')->whereNotNull('published_at')->where('id', $article)->first();
        abort_if(! $article, 404);

        return view('library.article', [
            'article' => $article,
            'relatedNews' => DB::table('news')->whereNotNull('published_at')->where('id', '!=', $article->id)->latest('published_at')->take(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('library.about');
    }

    public function contact(): View
    {
        return view('library.contact');
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $message = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        DB::table('messages')->insert([...$message, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Pesan Anda telah terkirim. Tim kami akan segera menghubungi Anda.');
    }
}
