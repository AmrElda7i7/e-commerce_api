<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $permissions = [
            'create_user' ,
            'update_user' ,
            'show_users' ,
            'delete_user',
            'create_category' ,
            'update_category' ,
            'show_categories' ,
            'delete_category',
            'create_product' ,
            'update_product' ,
            'show_products' ,
            'delete_product',
            'update_review' ,
            'show_reviews' ,
            'delete_review',
            'update_order' ,
            'show_orders' ,
            'delete_order',
            'create_role' ,
            'update_role' ,
            'show_roles' ,
            'delete_role' ,
        ];
        foreach ($permissions as $permission) {

            Permission::create(['name'=>$permission]);
        }
        $permissions = Permission::all() ;
        $role  = Role::create(
            ['name' => 'admin' 
            ]
        ) ;
        $role->syncPermissions($permissions) ;
    }
}