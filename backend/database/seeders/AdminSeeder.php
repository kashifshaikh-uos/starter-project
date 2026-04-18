<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\PrivilegeGroup;
use App\Models\Privilege;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role
        $adminRole = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full system access',
            'is_active' => true,
        ]);

        // Create privilege groups
        $userMgmt = PrivilegeGroup::create(['name' => 'User Management', 'slug' => 'user-management']);
        $roleMgmt = PrivilegeGroup::create(['name' => 'Role Management', 'slug' => 'role-management']);
        $privMgmt = PrivilegeGroup::create(['name' => 'Privilege Management', 'slug' => 'privilege-management']);

        // Create privileges
        $privileges = [];

        // Settings parent menu
        $settingsMenu = Privilege::create([
            'name' => 'Settings',
            'slug' => 'settings-menu',
            'menu_type' => 'menu',
            'icon' => 'fa-cog',
            'sort_order' => 1,
            'show_in_menu' => true,
        ]);
        $privileges[] = $settingsMenu;

        // User Management menu (child of Settings)
        $userMenu = Privilege::create([
            'privilege_group_id' => $userMgmt->id,
            'parent_id' => $settingsMenu->id,
            'name' => 'User Management',
            'slug' => 'user-management-menu',
            'frontend_route' => '/users',
            'menu_type' => 'submenu',
            'icon' => 'fa-users',
            'sort_order' => 1,
            'show_in_menu' => true,
        ]);
        $privileges[] = $userMenu;

        $privileges[] = Privilege::create([
            'privilege_group_id' => $userMgmt->id,
            'parent_id' => $userMenu->id,
            'name' => 'User List',
            'slug' => 'user-list',
            'frontend_route' => '/users',
            'api_route' => '/api/users',
            'method' => 'GET',
            'menu_type' => 'submenu',
            'icon' => 'fa-list',
            'sort_order' => 1,
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $userMgmt->id,
            'parent_id' => $userMenu->id,
            'name' => 'Create User',
            'slug' => 'create-user',
            'frontend_route' => '/users/create',
            'api_route' => '/api/users',
            'method' => 'POST',
            'menu_type' => 'submenu',
            'icon' => 'fa-user-plus',
            'sort_order' => 2,
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $userMgmt->id,
            'name' => 'Update User',
            'slug' => 'update-user',
            'api_route' => '/api/users/{id}',
            'method' => 'PUT',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $userMgmt->id,
            'name' => 'Assign Roles',
            'slug' => 'assign-roles',
            'api_route' => '/api/users/{id}/assign-roles',
            'method' => 'POST',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        // Role Management menu (child of Settings)
        $roleMenu = Privilege::create([
            'privilege_group_id' => $roleMgmt->id,
            'parent_id' => $settingsMenu->id,
            'name' => 'Role Management',
            'slug' => 'role-management-menu',
            'frontend_route' => '/roles',
            'menu_type' => 'submenu',
            'icon' => 'fa-shield',
            'sort_order' => 2,
            'show_in_menu' => true,
        ]);
        $privileges[] = $roleMenu;

        $privileges[] = Privilege::create([
            'privilege_group_id' => $roleMgmt->id,
            'parent_id' => $roleMenu->id,
            'name' => 'Role List',
            'slug' => 'role-list',
            'frontend_route' => '/roles',
            'api_route' => '/api/roles',
            'method' => 'GET',
            'menu_type' => 'submenu',
            'icon' => 'fa-list',
            'sort_order' => 1,
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $roleMgmt->id,
            'parent_id' => $roleMenu->id,
            'name' => 'Create Role',
            'slug' => 'create-role',
            'frontend_route' => '/roles/create',
            'api_route' => '/api/roles',
            'method' => 'POST',
            'menu_type' => 'submenu',
            'icon' => 'fa-plus',
            'sort_order' => 2,
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $roleMgmt->id,
            'name' => 'Update Role',
            'slug' => 'update-role',
            'api_route' => '/api/roles/{id}',
            'method' => 'PUT',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $roleMgmt->id,
            'name' => 'Assign Privileges',
            'slug' => 'assign-privileges',
            'api_route' => '/api/roles/{id}/assign-privileges',
            'method' => 'POST',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        // Privilege Management menu (child of Settings)
        $privMenu = Privilege::create([
            'privilege_group_id' => $privMgmt->id,
            'parent_id' => $settingsMenu->id,
            'name' => 'Privilege Management',
            'slug' => 'privilege-management-menu',
            'frontend_route' => '/privileges',
            'menu_type' => 'submenu',
            'icon' => 'fa-key',
            'sort_order' => 3,
            'show_in_menu' => true,
        ]);
        $privileges[] = $privMenu;

        $privileges[] = Privilege::create([
            'privilege_group_id' => $privMgmt->id,
            'parent_id' => $privMenu->id,
            'name' => 'Privilege Management',
            'slug' => 'privilege-management',
            'frontend_route' => '/privileges',
            'api_route' => '/api/privileges',
            'method' => null,
            'menu_type' => 'submenu',
            'icon' => 'fa-key',
            'sort_order' => 1,
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $userMgmt->id,
            'name' => 'View User',
            'slug' => 'view-user',
            'api_route' => '/api/users/{id}',
            'method' => 'GET',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $roleMgmt->id,
            'name' => 'View Role',
            'slug' => 'view-role',
            'api_route' => '/api/roles/{id}',
            'method' => 'GET',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        $privileges[] = Privilege::create([
            'privilege_group_id' => $privMgmt->id,
            'name' => 'View Privilege',
            'slug' => 'view-privilege',
            'api_route' => '/api/privileges/{id}',
            'method' => 'GET',
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        // Privilege Group Management
        $privileges[] = Privilege::create([
            'privilege_group_id' => $privMgmt->id,
            'name' => 'Privilege Group Management',
            'slug' => 'privilege-group-management',
            'api_route' => '/api/privilege-groups',
            'method' => null,  // Allows all HTTP methods
            'menu_type' => 'none',
            'show_in_menu' => false,
        ]);

        // Assign all privileges to admin role
        $adminRole->privileges()->attach(collect($privileges)->pluck('id'));

        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'cnic_no' => '1234567890123',
            'password' => 'password',
            'is_active' => true,
            'active_role_id' => $adminRole->id,
        ]);

        $admin->roles()->attach($adminRole->id);
    }
}
