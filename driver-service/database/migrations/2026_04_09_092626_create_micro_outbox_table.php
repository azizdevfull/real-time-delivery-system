<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('micro_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('exchange');
            $table->string('routing_key');
            $table->json('payload');
            
            // 🛡 YANGI QO'SHILGAN USTUNLAR:
            $table->unsignedInteger('attempts')->default(0); // Urinishlar soni
            $table->text('error_message')->nullable();       // Nega o'tmadi?
            
            $table->timestamps();

            $table->index(['attempts', 'id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('micro_outbox');
    }
};
