<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('member');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
        });

        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_category_id')->constrained()->restrictOnDelete();
            $table->string('reference_no')->unique();
            $table->string('full_name', 150);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('notification_queued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
        Schema::table('members', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
