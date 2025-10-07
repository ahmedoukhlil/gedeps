<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer l'agent Ahmed Mohamed
        $agent = User::create([
            'name' => 'Ahmed Mohamed',
            'email' => 'ahmed.mohamed@gedeps.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        // Créer le DG Abdellahi Sidi
        $dg = User::create([
            'name' => 'Abdellahi Sidi',
            'email' => 'abdellahi.sidi@gedeps.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Utilisateurs créés avec succès :');
        $this->command->info('- Agent: Ahmed Mohamed (ahmed.mohamed@gedeps.com)');
        $this->command->info('- DG: Abdellahi Sidi (abdellahi.sidi@gedeps.com)');
        $this->command->info('Mot de passe pour les deux: 12345678');
    }
}
