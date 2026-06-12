<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Older versions of devdojo/accounts stored the user class name in
     * keyvalue_type, but reads go through the morph map ('users'), which
     * made those rows invisible. Normalize them to the mapped alias.
     */
    public function up(): void
    {
        if (! Schema::hasTable('profile_key_values')) {
            return;
        }

        DB::table('profile_key_values')
            ->where('keyvalue_type', User::class)
            ->update(['keyvalue_type' => (new User)->getMorphClass()]);
    }

    public function down(): void
    {
        // One-way data normalization.
    }
};
