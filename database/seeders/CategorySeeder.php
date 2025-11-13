<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and equipment', 'parent_id' => null],
            ['name' => 'Furniture', 'description' => 'Office and workspace furniture', 'parent_id' => null],
            ['name' => 'Vehicles', 'description' => 'Company vehicles and transport', 'parent_id' => null],
            ['name' => 'Equipment', 'description' => 'General equipment and tools', 'parent_id' => null],
            ['name' => 'Office Supplies', 'description' => 'Office supplies and stationery', 'parent_id' => null],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }

        // Add subcategories
        $electronics = Category::where('name', 'Electronics')->first();
        if ($electronics) {
            $subCategories = [
                ['name' => 'Computers', 'description' => 'Desktop and laptop computers', 'parent_id' => $electronics->id],
                ['name' => 'Monitors', 'description' => 'Display monitors and screens', 'parent_id' => $electronics->id],
                ['name' => 'Printers', 'description' => 'Printers and scanners', 'parent_id' => $electronics->id],
                ['name' => 'Networking', 'description' => 'Network equipment and routers', 'parent_id' => $electronics->id],
            ];

            foreach ($subCategories as $subCategory) {
                Category::firstOrCreate(
                    ['name' => $subCategory['name'], 'parent_id' => $subCategory['parent_id']],
                    $subCategory
                );
            }
        }

        $furniture = Category::where('name', 'Furniture')->first();
        if ($furniture) {
            $subCategories = [
                ['name' => 'Desks', 'description' => 'Office desks and workstations', 'parent_id' => $furniture->id],
                ['name' => 'Chairs', 'description' => 'Office chairs and seating', 'parent_id' => $furniture->id],
                ['name' => 'Storage', 'description' => 'Cabinets and storage units', 'parent_id' => $furniture->id],
            ];

            foreach ($subCategories as $subCategory) {
                Category::firstOrCreate(
                    ['name' => $subCategory['name'], 'parent_id' => $subCategory['parent_id']],
                    $subCategory
                );
            }
        }

        $this->command->info('Categories seeded successfully!');
    }
}
