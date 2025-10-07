<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Créer les rôles d'abord
        $this->call(RoleSeeder::class);

        // Récupérer les rôles
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        $agentRole = \App\Models\Role::where('name', 'agent')->first();
        $signataireRole = \App\Models\Role::where('name', 'signataire')->first();

        // Créer l'admin
        User::create([
            'name' => 'Admin GEDEPS',
            'email' => 'admin@gedeps.com',
            'password' => \Hash::make('12345678'),
            'email_verified_at' => now(),
            'role_id' => $adminRole->id,
        ]);

        // Créer l'agent Ahmed Mohamed
        User::create([
            'name' => 'Ahmed Mohamed',
            'email' => 'ahmed.mohamed@gedeps.com',
            'password' => \Hash::make('12345678'),
            'email_verified_at' => now(),
            'role_id' => $agentRole->id,
        ]);

        // Créer le signataire Abdellahi Sidi
        User::create([
            'name' => 'Abdellahi Sidi',
            'email' => 'abdellahi.sidi@gedeps.com',
            'password' => \Hash::make('12345678'),
            'email_verified_at' => now(),
            'role_id' => $signataireRole->id,
        ]);

        $this->command->info('Utilisateurs créés avec succès :');
        $this->command->info('- Admin: admin@gedeps.com (12345678)');
        $this->command->info('- Agent: Ahmed Mohamed (ahmed.mohamed@gedeps.com) (12345678)');
        $this->command->info('- Signataire: Abdellahi Sidi (abdellahi.sidi@gedeps.com) (12345678)');
    }
}
