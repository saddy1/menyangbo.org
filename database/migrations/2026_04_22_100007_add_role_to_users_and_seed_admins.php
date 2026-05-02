<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Add role column to users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['member', 'admin', 'super_admin'])
                  ->default('member')
                  ->after('email');
        });

        // 2. Migrate existing admins → users with role='admin'
        if (Schema::hasTable('admins')) {
            $admins = DB::table('admins')->get();

            foreach ($admins as $admin) {
                $existing = DB::table('users')->where('email', $admin->email)->first();

                if ($existing) {
                    // Promote existing user to admin
                    DB::table('users')
                        ->where('email', $admin->email)
                        ->update(['role' => 'admin', 'updated_at' => now()]);
                } else {
                    // Create user account — random password (admin must reset via email)
                    DB::table('users')->insert([
                        'name'              => $admin->name,
                        'email'             => $admin->email,
                        'password'          => $admin->password, // already hashed in admins table
                        'role'              => 'admin',
                        'email_verified_at' => now(),
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }

            // Make the very first admin a super_admin
            $firstAdmin = DB::table('admins')->orderBy('id')->first();
            if ($firstAdmin) {
                DB::table('users')
                    ->where('email', $firstAdmin->email)
                    ->update(['role' => 'super_admin']);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
