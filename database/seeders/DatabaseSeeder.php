<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        User::create([
            'name'      => 'Super Admin',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('password'),
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        // Create sample admins
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name'      => "Admin User $i",
                'email'     => "admin$i@example.com",
                'password'  => Hash::make('password'),
                'role'      => User::ROLE_ADMIN,
                'phone'     => "+1-555-000$i",
                'is_active' => true,
            ]);
        }

        // Create sample customers
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name'      => "Customer $i",
                'email'     => "customer$i@example.com",
                'password'  => Hash::make('password'),
                'role'      => User::ROLE_CUSTOMER,
                'phone'     => "+1-555-100$i",
                'address'   => "$i Main Street, City, Country",
                'is_active' => true,
            ]);
        }

        // Create sample products
        $categories = ['Electronics', 'Clothing', 'Books', 'Food', 'Sports'];
        $productNames = [
            'Wireless Headphones', 'Smart Watch', 'Laptop Stand', 'Mechanical Keyboard',
            'Running Shoes', 'Cotton T-Shirt', 'Denim Jeans', 'Winter Jacket',
            'PHP Cookbook', 'Laravel in Action', 'Clean Code', 'Design Patterns',
            'Protein Bar', 'Green Tea', 'Coffee Beans', 'Energy Drink',
            'Yoga Mat', 'Resistance Bands', 'Water Bottle', 'Jump Rope',
        ];

        foreach ($productNames as $index => $name) {
            $slug = Str::slug($name);
            Product::create([
                'name'        => $name,
                'slug'        => $slug,
                'description' => "High quality $name for everyday use. Premium materials and excellent craftsmanship.",
                'price'       => rand(10, 500) + 0.99,
                'stock'       => rand(0, 200),
                'category'    => $categories[$index % count($categories)],
                'is_active'   => rand(0, 10) > 1,
            ]);
        }
    }
}
