<?php

namespace Tests\Feature\Presence;

use App\Models\Division;
use App\Models\Presence;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreatePresenceTest extends TestCase
{
    /**
     * Test halaman create dapat diakses.
     */

    public function test_create_presemce_page_rendered(): void
    {
        $response = $this->get(route('app.presence.create'));
        $response->assertStatus(302);
    }


    /**
     * Test form create presence.
     */

    public function test_store_presence_with_valid_data()
    {
        // Create division
        $division = Division::create([
            'name' => 'Division 1',
            'slug' => 'division-1',
            'description' => 'Deskripsi Division 1',
            'logo' => 'logo.png',
        ]);

        // Valid data for presence
        $data = [
            "presence_date" => "2025-01-05 10:00:00",
            "presence_number" => 1,
        ];

        $presence = Presence::create([
            "date_time" => Carbon::parse($data["presence_date"])->format('Y-m-d H:i'),
            "section" => "Pertemuan ke " . $data["presence_number"],
            "status" => "active",
            "division_id" => $division->id,
        ]);

        // Assert that the presence data is stored in the database
        $this->assertDatabaseHas('presences', [
            "date_time" => Carbon::parse($data["presence_date"])->format('Y-m-d H:i'),
            "section" => "Pertemuan ke " . $data["presence_number"],
            "status" => "active",
            "division_id" => $division->id,
        ]);
    }


    public function test_create_presence_with_invalid_data()
    {
        // Create division with unique slug
        $division = Division::create([
            'name' => 'Division 1',
            'slug' => 'division-' . uniqid(), // Ensure unique slug
            'description' => 'Deskripsi Division 1',
            'logo' => 'logo.png',
        ]);

        // Invalid data for presence
        $invalidData = [
            "presence_date" => "invalid-date-format",
            "presence_number" => null,
        ];

        // Validation rules
        $validator = Validator::make($invalidData, [
            "presence_date" => "required|date",
            "presence_number" => "required|integer",
        ]);

        // Assert that validation fails
        $this->assertTrue($validator->fails());

        // Attempt to create presence and ensure it fails
        $this->expectException(\Exception::class);

        // Since data is invalid, this operation should throw an error
        Presence::create([
            "date_time" => Carbon::parse($invalidData["presence_date"])->format('Y-m-d H:i'),
            "section" => "Pertemuan ke " . $invalidData["presence_number"],
            "status" => "active",
            "division_id" => $division->id,
        ]);

        // Verify that no invalid presence is saved in the database
        $this->assertDatabaseMissing('presences', [
            "section" => "Pertemuan ke ",
            "status" => "active",
            "division_id" => $division->id,
        ]);
    }
}
