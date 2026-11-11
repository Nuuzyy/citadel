<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Perpustakaan Digital') — Ruang untuk bertumbuh</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <div class="topbar"><div class="wrap topbar-inner"><span>Ruang inspirasi, pengetahuan, dan cerita.</span><span>Senin—Sabtu &nbsp;·&nbsp; 08.00—20.00 WIB</span></div></div>
        <nav class="navbar wrap" aria-label="Navigasi utama">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">B<span>+</span></span><span>Perpustakaan<small>digital</small></span></a>
            <button class="menu-toggle" aria-label="Buka navigasi" aria-expanded="false"><span></span><span></span><span></span></button>
            <div class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('catalog') }}" class="{{ request()->routeIs('catalog', 'books.show') ? 'active' : '' }}">Katalog</a>
                <a href="{{ route('home') }}#kategori">Kategori</a>
                <a href="{{ route('news') }}" class="{{ request()->routeIs('news') ? 'active' : '' }}">Berita</a>
                <a href="{{ route('about') }}">Tentang</a>
                <a href="{{ route('contact') }}">Kontak</a>
                @auth
                    <a class="nav-account" href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('member.dashboard') }}">Halo, {{ Str::before(auth()->user()->name, ' ') }}</a>
                    <form action="{{ route('logout') }}" method="post">@csrf<button class="nav-login" type="submit">Keluar</button></form>
                @else
                    <a class="nav-login" href="{{ route('login') }}">Masuk</a>
                    <a class="nav-register" href="{{ route('register') }}">Daftar <span>↗</span></a>
                @endauth
            </div>
        </nav>
    </header>
    @if(session('success') || $errors->any())
        <div class="wrap flash-area">
            @if(session('success'))<div class="flash success" role="status">{{ session('success') }}</div>@endif
            @foreach($errors->all() as $error)<div class="flash error" role="alert">{{ $error }}</div>@endforeach
        </div>
    @endif
    <main>@yield('content')</main>
    <footer class="footer">
        <div class="wrap footer-grid">
            <div class="footer-about"><a class="brand brand-light" href="{{ route('home') }}"><span class="brand-mark">B<span>+</span></span><span>Perpustakaan<small>digital</small></span></a><p>Tempat ide tumbuh dan cerita menemukan rumah. Akses pengetahuan, kapan saja dan di mana saja.</p><div class="socials"><a href="#" aria-label="Instagram">ig</a><a href="#" aria-label="Facebook">f</a><a href="#" aria-label="Youtube">▶</a></div></div>
            <div><h3>Jelajahi</h3><a href="{{ route('catalog') }}">Katalog buku</a><a href="{{ route('home') }}#kategori">Kategori</a><a href="{{ route('news') }}">Berita & artikel</a><a href="{{ route('about') }}">Tentang kami</a></div>
            <div><h3>Layanan</h3><a href="{{ route('login') }}">Keanggotaan</a><a href="{{ route('login') }}">Peminjaman buku</a><a href="{{ route('contact') }}">Bantuan</a><a href="{{ route('contact') }}">Kirim pesan</a></div>
            <div class="footer-contact"><h3>Kunjungi kami</h3><p>Jl. Pustaka No. 28, Menteng<br>Jakarta Pusat, Indonesia</p><p>hello@perpusdigital.id<br>(021) 555-0188</p><p>Senin—Sabtu, 08.00—20.00 WIB</p></div>
        </div>
        <div class="wrap footer-bottom"><span>© {{ date('Y') }} Perpustakaan Digital. Dibuat untuk para pembaca.</span><span>Kebijakan privasi &nbsp;·&nbsp; Syarat layanan</span></div>
    </footer>
</body>
</html>
