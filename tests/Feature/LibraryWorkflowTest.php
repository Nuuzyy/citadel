<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibraryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed();
    }

    public function test_public_pages_and_catalog_filters_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Jelajahi dunia');
        $this->get('/katalog?q=Atomic')->assertOk()->assertSee('Atomic Habits')->assertDontSee('Bumi Manusia');
        $this->get('/tentang')->assertOk();
        $this->get('/kontak')->assertOk();
        $this->get('/berita')->assertOk();
        $this->get('/masuk')->assertOk();
    }

    public function test_member_can_request_and_admin_can_approve_and_return_a_book(): void
    {
        $member = User::where('email', 'anggota@perpus.id')->firstOrFail();
        $admin = User::where('email', 'admin@perpus.id')->firstOrFail();
        $book = Book::where('isbn', '9786024125189')->firstOrFail();
        $initialStock = $book->stock;

        $this->actingAs($member)->post(route('borrowings.store', $book))->assertRedirect();
        $borrowing = Borrowing::with('details')->firstOrFail();
        $this->assertSame('pending', $borrowing->status);

        $this->actingAs($admin)->post(route('admin.borrowings.approve', $borrowing))->assertRedirect();
        $this->assertSame($initialStock - 1, $book->fresh()->stock);

        $borrowing->update(['due_at' => today()->subDay()]);
        $this->post(route('admin.borrowings.return', $borrowing))->assertRedirect();
        $this->assertSame($initialStock, $book->fresh()->stock);
        $this->assertDatabaseHas('fines', ['borrowing_id' => $borrowing->id, 'amount' => 1000]);
    }

    public function test_new_membership_is_validated_and_persisted(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Nadia Putri',
            'member_number' => 'PD-TEST-100',
            'email' => 'nadia@example.test',
            'phone' => '08123456789',
            'address' => 'Jl. Anggrek No. 1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('member.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'nadia@example.test', 'member_number' => 'PD-TEST-100']);
        $this->assertDatabaseHas('members', ['identity_number' => 'PD-TEST-100']);
    }

    public function test_admin_sign_in_and_role_protection_work(): void
    {
        $this->post(route('login.store'), ['email' => 'admin@perpus.id', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Ringkasan perpustakaan.');

        $member = User::where('email', 'anggota@perpus.id')->firstOrFail();
        $this->actingAs($member)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_contact_form_saves_a_valid_message(): void
    {
        $this->post(route('contact.send'), [
            'name' => 'Raka',
            'email' => 'raka@example.test',
            'subject' => 'Jadwal klub baca',
            'message' => 'Kapan klub baca berikutnya?',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', ['email' => 'raka@example.test', 'subject' => 'Jadwal klub baca']);
    }

    public function test_admin_can_export_an_excel_compatible_book_report(): void
    {
        $admin = User::where('email', 'admin@perpus.id')->firstOrFail();
        $response = $this->actingAs($admin)->get(route('admin.reports.export', 'books'));

        $response->assertDownload('laporan-books-'.today()->format('Y-m-d').'.csv');
        $this->assertStringContainsString('Atomic Habits', $response->streamedContent());
    }
}
