<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DocumentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Créer les permissions
        $permissions = [
            'documents.upload',
            'documents.view',
            'documents.view-own',
            'documents.approve',
            'documents.sign',
            'documents.refuse',
            'documents.download',
            'documents.history',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles
        $agentRole = Role::firstOrCreate(['name' => 'Agent']);
        $dgRole = Role::firstOrCreate(['name' => 'DG']);
        $dafRole = Role::firstOrCreate(['name' => 'DAF']);

        // Assigner les permissions aux rôles
        
        // Agent : peut uploader, voir ses propres documents, télécharger
        $agentRole->givePermissionTo([
            'documents.upload',
            'documents.view-own',
            'documents.download',
        ]);

        // DG et DAF : toutes les permissions
        $dgRole->givePermissionTo($permissions);
        $dafRole->givePermissionTo($permissions);
    }
}
