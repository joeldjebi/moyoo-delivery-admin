<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RolePermission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissions pour le rôle Admin
        RolePermission::updateOrCreate(
            ['role' => 'admin'],
            ['permissions' => [
                'users.create',
                'users.read',
                'users.update',
                'users.delete',
                'colis.create',
                'colis.read',
                'colis.update',
                'colis.delete',
                'livreurs.create',
                'livreurs.read',
                'livreurs.update',
                'livreurs.delete',
                'marchands.create',
                'marchands.read',
                'marchands.update',
                'marchands.delete',
                'reports.read',
                'settings.read',
                'settings.update'
            ]]
        );

        // Permissions pour le rôle Manager
        RolePermission::updateOrCreate(
            ['role' => 'manager'],
            ['permissions' => [
                'colis.create',
                'colis.read',
                'colis.update',
                'colis.delete',
                'livreurs.create',
                'livreurs.read',
                'livreurs.update',
                'livreurs.delete',
                'marchands.create',
                'marchands.read',
                'marchands.update',
                'marchands.delete',
                'reports.read'
            ]]
        );

        // Permissions pour le rôle User
        RolePermission::updateOrCreate(
            ['role' => 'user'],
            ['permissions' => [
                'colis.create',
                'colis.read',
                'livreurs.read',
                'marchands.read'
            ]]
        );
    }
}
