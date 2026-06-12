<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soap_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('team_id')->default('TEAM-08');
            $table->string('activity_name');
            $table->text('log_content')->comment('Data transaksi dalam format JSON');
            $table->string('receipt_number')->nullable()->comment('ReceiptNumber dari IAE Central');
            $table->enum('status', ['success', 'failed', 'error'])->default('success');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soap_audit_logs');
    }
};
