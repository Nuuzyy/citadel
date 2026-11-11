@extends('layouts.library')
@section('title', 'Lupa Kata Sandi')
@section('content')
<section class="auth-section wrap"><div class="auth-art"><div class="eyebrow eyebrow-light"><span></span> BANTUAN AKUN</div><h1>Kami bantu kamu<br>kembali <em>membaca.</em></h1><p>Masukkan alamat email akunmu. Kami akan mengirim tautan untuk mengatur ulang kata sandi.</p></div><div class="auth-form"><div class="eyebrow"><span></span> RESET KATA SANDI</div><h2>Lupa kata sandi?</h2><p>Ingat kata sandi? <a href="{{ route('login') }}">Kembali masuk</a></p><form method="post" action="{{ route('password.email') }}">@csrf<label>Email akun<input name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com"></label><button class="button button-navy" type="submit">Kirim tautan reset <span>→</span></button></form></div></section>
@endsection
