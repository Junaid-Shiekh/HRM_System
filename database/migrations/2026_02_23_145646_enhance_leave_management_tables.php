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
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('gender')->default('all')->after('is_paid'); // all, male, female
            $table->boolean('allow_carry_forward')->default(false)->after('gender');
        });

        Schema::table('leave_policies', function (Blueprint $table) {
            $table->integer('max_consecutive_days')->nullable()->after('max_days');
            $table->integer('notice_period_days')->default(0)->after('max_consecutive_days');
            $table->boolean('allow_carry_forward')->default(false)->after('notice_period_days');
            $table->integer('probation_restriction_days')->default(0)->after('allow_carry_forward');
            $table->boolean('allow_half_day')->default(true)->after('probation_restriction_days');
            $table->boolean('allow_encashment')->default(false)->after('allow_half_day');
            $table->string('applicability_type')->default('all')->after('allow_encashment'); // all, department, designation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['gender', 'allow_carry_forward']);
        });

        Schema::table('leave_policies', function (Blueprint $table) {
            $table->dropColumn([
                'max_consecutive_days', 
                'notice_period_days', 
                'allow_carry_forward', 
                'probation_restriction_days', 
                'allow_half_day', 
                'allow_encashment', 
                'applicability_type'
            ]);
        });
    }
};
