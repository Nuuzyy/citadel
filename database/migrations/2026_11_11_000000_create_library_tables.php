<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member')->after('email');
            $table->string('member_number')->nullable()->unique()->after('role');
            $table->string('phone')->nullable()->after('member_number');
            $table->text('address')->nullable()->after('phone');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('icon')->default('✦');
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('publishers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();
            $table->foreignId('publisher_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('isbn')->unique();
            $table->unsignedSmallInteger('published_year');
            $table->unsignedSmallInteger('pages')->nullable();
            $table->text('description');
            $table->string('cover_url');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('borrow_count')->default(0);
            $table->timestamps();
            $table->index(['published_year', 'borrow_count']);
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('identity_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->date('borrowed_at')->nullable();
            $table->date('due_at')->nullable();
            $table->date('returned_at')->nullable();
            $table->date('renewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('borrowing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->timestamps();
            $table->unique(['borrowing_id', 'book_id']);
        });

        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('returned_at');
            $table->timestamps();
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('author');
            $table->string('image_url');
            $table->text('excerpt');
            $table->longText('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('news');
        Schema::dropIfExists('fines');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('borrowing_details');
        Schema::dropIfExists('borrowings');
        Schema::dropIfExists('members');
        Schema::dropIfExists('books');
        Schema::dropIfExists('publishers');
        Schema::dropIfExists('authors');
        Schema::dropIfExists('categories');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'member_number', 'phone', 'address']);
        });
    }
};
