<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Administrateur de l\'application - peut créer des comptes et gérer les signatures'
            ],
            [
                'name' => 'agent',
                'display_name' => 'Agent',
                'description' => 'Agent - peut uploader des documents et choisir les signataires'
            ],
            [
                'name' => 'signataire',
                'display_name' => 'Signataire',
                'description' => 'Signataire - peut signer les documents qui lui sont assignés'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        $this->command->info('Rôles créés avec succès :');
        $this->command->info('- Admin');
        $this->command->info('- Agent');
        $this->command->info('- Signataire');
    }
}
