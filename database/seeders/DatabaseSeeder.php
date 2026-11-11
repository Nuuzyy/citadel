<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Member;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categoryData = [
            ['Pendidikan', 'pendidikan', '✦'],
            ['Teknologi', 'teknologi', '⌘'],
            ['Sejarah', 'sejarah', '◷'],
            ['Novel', 'novel', '♡'],
            ['Agama', 'agama', '☼'],
            ['Sains', 'sains', '⚛'],
            ['Ekonomi', 'ekonomi', '↗'],
            ['Kesehatan', 'kesehatan', '✚'],
        ];

        foreach ($categoryData as [$name, $slug, $icon]) {
            Category::updateOrCreate(['slug' => $slug], ['name' => $name, 'icon' => $icon]);
        }

        $books = [
            ['Filosofi Teras', 'Henry Manampiring', 'Kompas', 'Novel', '9786024125189', 2018, 320, 8, 'Filsafat Stoa untuk kehidupan modern. Sebuah panduan praktis untuk mengelola emosi dan menjalani hidup dengan lebih tenang.', 'photo-1544947950-fa07a98d237f'],
            ['Atomic Habits', 'James Clear', 'Gramedia Pustaka Utama', 'Pendidikan', '9786020633176', 2019, 352, 5, 'Panduan praktis tentang bagaimana perubahan kecil membentuk hasil yang luar biasa dalam hidup kita.', 'photo-1543002588-bfa74002ed7e'],
            ['Laut Bercerita', 'Leila S. Chudori', 'Kepustakaan Populer Gramedia', 'Sejarah', '9786024246945', 2017, 379, 3, 'Novel yang mengisahkan kehilangan, persahabatan, dan perjuangan para aktivis pada masa Orde Baru.', 'photo-1512820790803-83ca734da794'],
            ['Sapiens', 'Yuval Noah Harari', 'Kepustakaan Populer Gramedia', 'Sejarah', '9786024244163', 2017, 528, 7, 'Sebuah penelusuran besar tentang sejarah umat manusia dari zaman batu hingga era modern.', 'photo-1519682337058-a94d519337bc'],
            ['Deep Work', 'Cal Newport', 'Gramedia Pustaka Utama', 'Teknologi', '9786020633213', 2019, 296, 2, 'Cara bekerja lebih fokus di tengah dunia yang penuh distraksi untuk menghasilkan karya berkualitas.', 'photo-1532012197267-da84d127e765'],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Lentera Dipantara', 'Novel', '9789799731234', 2015, 535, 6, 'Kisah Minke, seorang pribumi terpelajar yang berjuang menghadapi ketidakadilan kolonial.', 'photo-1516979187457-637abb4f9353'],
            ['Filosofi untuk Hidup', 'Ryan Holiday', 'Gramedia Pustaka Utama', 'Agama', '9786020633305', 2021, 288, 4, 'Kebijaksanaan klasik untuk menemukan ketenangan, ketangguhan, dan tujuan hidup.', 'photo-1519682337058-a94d519337bc'],
            ['Berani Tidak Disukai', 'Ichiro Kishimi', 'Gramedia Pustaka Utama', 'Pendidikan', '9786020633212', 2019, 320, 9, 'Dialog filosofis tentang kebebasan, penerimaan diri, dan keberanian untuk berubah.', 'photo-1544947950-fa07a98d237f'],
        ];

        foreach ($books as [$title, $author, $publisher, $category, $isbn, $year, $pages, $stock, $description, $image]) {
            $authorId = Author::firstOrCreate(['name' => $author])->id;
            $publisherId = Publisher::firstOrCreate(['name' => $publisher])->id;
            Book::updateOrCreate(['isbn' => $isbn], [
                'title' => $title,
                'author_id' => $authorId,
                'publisher_id' => $publisherId,
                'category_id' => Category::where('name', $category)->value('id'),
                'published_year' => $year,
                'pages' => $pages,
                'stock' => $stock,
                'borrow_count' => rand(8, 46),
                'description' => $description,
                'cover_url' => "https://images.unsplash.com/{$image}?auto=format&fit=crop&w=640&q=85",
            ]);
        }

        $admin = User::updateOrCreate(['email' => 'admin@perpus.id'], [
            'name' => 'Admin Perpustakaan',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        $member = User::updateOrCreate(['email' => 'anggota@perpus.id'], [
            'name' => 'Dina Prameswari',
            'password' => Hash::make('password'),
            'role' => 'member',
            'member_number' => 'PD-2026-001',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 10, Jakarta',
        ]);
        Member::updateOrCreate(['user_id' => $member->id], ['identity_number' => 'PD-2026-001', 'is_active' => true]);
        Member::where('user_id', $admin->id)->delete();

        $articles = [
            ['Klub Baca Bulan Ini: Membaca untuk Bertumbuh', 'Kegiatan', 'Tim Perpustakaan', 'photo-1519682337058-a94d519337bc', 'Bergabung dalam diskusi santai tentang buku pilihan bulan ini.', 'Mari bertumbuh bersama melalui bacaan dan percakapan yang bermakna. Klub baca terbuka untuk seluruh anggota.', now()->subDays(2)],
            ['Koleksi Baru Telah Hadir di Katalog', 'Koleksi', 'Rani Putri', 'photo-1512820790803-83ca734da794', 'Puluhan judul pilihan terbaru kini dapat dipinjam oleh anggota.', 'Kami menghadirkan pilihan buku baru dari berbagai kategori. Temukan bacaan favoritmu melalui katalog digital kami.', now()->subDays(5)],
            ['Jam Layanan Khusus Akhir Pekan', 'Pengumuman', 'Tim Perpustakaan', 'photo-1507842217343-583bb7270b66', 'Nikmati waktu membaca lebih lama dengan jadwal layanan akhir pekan.', 'Perpustakaan buka setiap Sabtu pukul 09.00–15.00. Kunjungi kami dan temukan ruang nyaman untuk belajar.', now()->subDays(8)],
        ];
        foreach ($articles as [$title, $category, $author, $image, $excerpt, $body, $publishedAt]) {
            DB::table('news')->updateOrInsert(['title' => $title], [
                'category' => $category, 'author' => $author,
                'image_url' => "https://images.unsplash.com/{$image}?auto=format&fit=crop&w=1000&q=85",
                'excerpt' => $excerpt, 'body' => $body, 'published_at' => $publishedAt,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('announcements')->updateOrInsert(['title' => 'Selamat datang di Perpustakaan Digital'], [
            'body' => 'Jelajahi koleksi, ajukan peminjaman, dan nikmati pengalaman membaca yang lebih mudah.',
            'is_published' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
