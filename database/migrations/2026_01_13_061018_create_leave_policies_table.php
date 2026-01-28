<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->string('accrual_type')->nullable(); // accrual_type in screenshot
            $table->decimal('accrual_rate', 8, 2)->default(0);
            $table->integer('carry_forward_limit')->default(0);
            $table->integer('min_days')->default(1);
            $table->integer('max_days')->nullable();
            $table->boolean('requires_approval')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_policies');
    }
};
