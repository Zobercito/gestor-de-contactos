<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 40)->nullable()->unique();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->string('contact_type', 10)->nullable();
            $table->string('business_type')->nullable();
            $table->string('gender', 30)->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('source')->nullable();
            $table->string('associated_brand')->nullable();
            $table->string('associated_company')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('name');
            $table->index('phone');
            $table->index('birthdate');
            $table->index('contact_type');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
