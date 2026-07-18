<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_comments', function (Blueprint $table): void {
            $table->uuid('id')->primary(); $table->foreignUuid('organization_id')->constrained()->restrictOnDelete(); $table->foreignUuid('incident_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('author_user_id')->nullable()->constrained('users')->nullOnDelete(); $table->text('body'); $table->string('visibility',30)->default('internal'); $table->timestamp('edited_at',6)->nullable();
            $table->timestamps(6); $table->softDeletes('deleted_at',6); $table->index(['incident_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_comments');
    }
};

