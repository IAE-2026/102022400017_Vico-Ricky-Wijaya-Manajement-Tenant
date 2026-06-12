<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('sso_subject')->nullable()->comment('JWT sub claim dari SSO');
            $table->string('role')->default('warga')->comment('Role lokal: admin, warga, tenant');
            $table->text('jwt_payload')->nullable()->comment('Payload JWT terakhir');
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_users');
    }
};
