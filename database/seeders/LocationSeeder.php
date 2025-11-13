<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            ['name' => 'Main Building', 'code' => 'MB', 'description' => 'Main office building', 'building' => 'Main', 'floor' => null, 'room' => null, 'parent_id' => null],
            ['name' => 'Warehouse', 'code' => 'WH', 'description' => 'Storage warehouse', 'building' => 'Warehouse', 'floor' => null, 'room' => null, 'parent_id' => null],
            ['name' => 'Branch Office', 'code' => 'BR', 'description' => 'Branch office location', 'building' => 'Branch', 'floor' => null, 'room' => null, 'parent_id' => null],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['code' => $location['code']],
                $location
            );
        }

        // Add floor/room details for Main Building
        $mainBuilding = Location::where('code', 'MB')->first();
        if ($mainBuilding) {
            $floors = [
                ['name' => '1st Floor', 'code' => 'MB-1F', 'description' => 'First floor', 'building' => 'Main', 'floor' => '1', 'room' => null, 'parent_id' => $mainBuilding->id],
                ['name' => '2nd Floor', 'code' => 'MB-2F', 'description' => 'Second floor', 'building' => 'Main', 'floor' => '2', 'room' => null, 'parent_id' => $mainBuilding->id],
                ['name' => '3rd Floor', 'code' => 'MB-3F', 'description' => 'Third floor', 'building' => 'Main', 'floor' => '3', 'room' => null, 'parent_id' => $mainBuilding->id],
            ];

            foreach ($floors as $floor) {
                Location::firstOrCreate(
                    ['code' => $floor['code']],
                    $floor
                );
            }

            // Add rooms for 1st Floor
            $firstFloor = Location::where('code', 'MB-1F')->first();
            if ($firstFloor) {
                $rooms = [
                    ['name' => 'Room 101', 'code' => 'MB-1F-101', 'description' => 'Reception area', 'building' => 'Main', 'floor' => '1', 'room' => '101', 'parent_id' => $firstFloor->id],
                    ['name' => 'Room 102', 'code' => 'MB-1F-102', 'description' => 'Conference room', 'building' => 'Main', 'floor' => '1', 'room' => '102', 'parent_id' => $firstFloor->id],
                    ['name' => 'Room 103', 'code' => 'MB-1F-103', 'description' => 'IT Department', 'building' => 'Main', 'floor' => '1', 'room' => '103', 'parent_id' => $firstFloor->id],
                ];

                foreach ($rooms as $room) {
                    Location::firstOrCreate(
                        ['code' => $room['code']],
                        $room
                    );
                }
            }
        }

        $this->command->info('Locations seeded successfully!');
    }
}
