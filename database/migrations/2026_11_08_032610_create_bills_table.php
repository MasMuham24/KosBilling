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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('bill_month', 7);
            $table->decimal('base_amount', 12, 2);
            $table->decimal('additional_charge', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['unpaid','pending_verification','paid','overdue'])->default('unpaid');
            $table->date('due_date');
            $table->string('snap_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
