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
        Schema::table('project_invitations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('project_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending')->after('token'); // pending, accepted, rejected
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->dropColumn('accepted'); // Remove the old accepted column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_invitations', function (Blueprint $table) {
            $table->boolean('accepted')->default(false);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'status', 'accepted_at']);
        });
    }
};
