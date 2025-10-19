<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Division;
use App\Models\Event;
use App\Models\EventSport;
use App\Models\EventTimeSlot;
use App\Models\Product;
use App\Models\SkillLevel;
use App\Models\Sport;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class BryantInvitationalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Sports
        $sports = $this->createSports();

        // Create Venue
        $venue = $this->createVenue();

        // Create Event
        $event = $this->createEvent($venue);

        // Create Time Slots
        $timeSlots = $this->createTimeSlots($event);

        // Create Age Groups
        $ageGroups = $this->createAgeGroups();

        // Create Skill Levels
        $skillLevels = $this->createSkillLevels();

        // Create Divisions
        $divisions = $this->createDivisions($ageGroups, $skillLevels, $sports);

        // Attach Sports to Event
        $eventSports = $this->attachSportsToEvent($event, $sports);

        // Create Registration Products
        $this->createRegistrationProducts($event, $sports, $divisions, $timeSlots);

        // Create Spectator Products
        $this->createSpectatorProducts($event);

        // Create Advertising Products
        $this->createAdvertisingProducts($event);
    }

    private function createSports(): array
    {
        $sportsData = [
            ['name' => 'Pickleball', 'default_space_required_sqft' => 880],
            ['name' => 'Futsal', 'default_space_required_sqft' => 2400],  // 60ft x 40ft
            ['name' => 'Wiffle Ball', 'default_space_required_sqft' => 3600],  // 60ft x 60ft
            ['name' => 'Volleyball', 'default_space_required_sqft' => 2880],
            ['name' => 'Basketball', 'default_space_required_sqft' => 4700],
            ['name' => 'Rollerhockey', 'default_space_required_sqft' => 8500],  // 85ft x 100ft rink
            ['name' => 'Lacrosse', 'default_space_required_sqft' => 9000],  // Field lacrosse indoor
            ['name' => 'Field Hockey', 'default_space_required_sqft' => 9000],
            ['name' => 'Cricket', 'default_space_required_sqft' => 8000],  // Action cricket
            ['name' => 'Handball', 'default_space_required_sqft' => 5400],  // 60ft x 90ft
            ['name' => 'Ping Pong', 'default_space_required_sqft' => 200],  // Table tennis
        ];

        $sports = [];
        foreach ($sportsData as $sportData) {
            $sports[$sportData['name']] = Sport::firstOrCreate(
                ['name' => $sportData['name']],
                ['default_space_required_sqft' => $sportData['default_space_required_sqft']]
            );
        }

        return $sports;
    }

    private function createVenue(): Venue
    {
        return Venue::firstOrCreate(
            ['name' => 'The Bryant Sports Complex'],
            [
                'address' => 'To Be Announced',
                'sports_space_sqft' => 300000,  // Large facility to accommodate 130 pickleball courts
                'spectator_space_sqft' => 50000,
                'total_banner_spots' => 50,
                'total_booth_spots' => 30,
            ]
        );
    }

    private function createEvent(Venue $venue): Event
    {
        return Event::firstOrCreate(
            ['name' => 'The Bryant Invitational'],
            [
                'venue_id' => $venue->id,
                'start_date' => '2025-01-17',
                'end_date' => '2025-01-18',
                'status' => 'open',
                'refund_cutoff_date' => '2025-01-10',
            ]
        );
    }

    private function createTimeSlots(Event $event): array
    {
        $timeSlotsData = [
            // Saturday Slots
            ['key' => 'Sat 7am-11am', 'start' => '2025-01-17 07:00:00', 'end' => '2025-01-17 11:00:00', 'space' => 300000],
            ['key' => 'Sat 12pm-4pm', 'start' => '2025-01-17 12:00:00', 'end' => '2025-01-17 16:00:00', 'space' => 300000],
            ['key' => 'Sat 4pm-8pm', 'start' => '2025-01-17 16:00:00', 'end' => '2025-01-17 20:00:00', 'space' => 300000],
            ['key' => 'Sat 8pm-11pm', 'start' => '2025-01-17 20:00:00', 'end' => '2025-01-17 23:00:00', 'space' => 300000],

            // Sunday Slots
            ['key' => 'Sun 7am-11am', 'start' => '2025-01-18 07:00:00', 'end' => '2025-01-18 11:00:00', 'space' => 300000],
            ['key' => 'Sun 12pm-4pm', 'start' => '2025-01-18 12:00:00', 'end' => '2025-01-18 16:00:00', 'space' => 300000],
            ['key' => 'Sun 5pm-8pm', 'start' => '2025-01-18 17:00:00', 'end' => '2025-01-18 20:00:00', 'space' => 300000],
            ['key' => 'Sun 8pm-11pm', 'start' => '2025-01-18 20:00:00', 'end' => '2025-01-18 23:00:00', 'space' => 300000],
        ];

        $timeSlots = [];
        foreach ($timeSlotsData as $slotData) {
            $timeSlots[$slotData['key']] = EventTimeSlot::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'start_time' => $slotData['start'],
                ],
                [
                    'end_time' => $slotData['end'],
                    'available_space_sqft' => $slotData['space'],
                ]
            );
        }

        return $timeSlots;
    }

    private function createAgeGroups(): array
    {
        $ageGroupsData = [
            ['name' => 'U10', 'category' => 'youth', 'min_age' => null, 'max_age' => 10, 'display_order' => 10],
            ['name' => 'U12', 'category' => 'youth', 'min_age' => null, 'max_age' => 12, 'display_order' => 20],
            ['name' => 'U14', 'category' => 'youth', 'min_age' => null, 'max_age' => 14, 'display_order' => 30],
            ['name' => 'U16', 'category' => 'youth', 'min_age' => null, 'max_age' => 16, 'display_order' => 40],
            ['name' => 'U18', 'category' => 'youth', 'min_age' => null, 'max_age' => 18, 'display_order' => 50],
            ['name' => 'Open', 'category' => 'adult', 'min_age' => 18, 'max_age' => null, 'display_order' => 100],
        ];

        $ageGroups = [];
        foreach ($ageGroupsData as $ageGroupData) {
            $ageGroups[$ageGroupData['name']] = AgeGroup::firstOrCreate(
                ['name' => $ageGroupData['name']],
                [
                    'category' => $ageGroupData['category'],
                    'min_age' => $ageGroupData['min_age'],
                    'max_age' => $ageGroupData['max_age'],
                    'display_order' => $ageGroupData['display_order'],
                ]
            );
        }

        return $ageGroups;
    }

    private function createSkillLevels(): array
    {
        $skillLevelsData = [
            ['name' => '2.0-2.5', 'description' => 'Pickleball: Beginner'],
            ['name' => '3.0-3.5', 'description' => 'Pickleball: Intermediate'],
            ['name' => '4.0-4.5', 'description' => 'Pickleball: Advanced'],
            ['name' => '5.0-5.5', 'description' => 'Pickleball: Elite'],
            ['name' => 'Beginner', 'description' => 'New to the sport'],
            ['name' => 'Intermediate', 'description' => 'Regular player'],
            ['name' => 'Advanced', 'description' => 'Competitive player'],
            ['name' => 'Elite', 'description' => 'Top-tier player'],
        ];

        $skillLevels = [];
        foreach ($skillLevelsData as $skillLevelData) {
            $skillLevels[$skillLevelData['name']] = SkillLevel::firstOrCreate(
                ['name' => $skillLevelData['name']],
                ['description' => $skillLevelData['description']]
            );
        }

        return $skillLevels;
    }

    private function createDivisions(array $ageGroups, array $skillLevels, array $sports): array
    {
        $divisionsData = [
            // Pickleball Singles
            ['name' => 'Pickleball Singles 2.0-2.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '2.0-2.5', 'gender' => 'open', 'team_size' => 1, 'display_order' => 100],
            ['name' => 'Pickleball Singles 3.0-3.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.0-3.5', 'gender' => 'open', 'team_size' => 1, 'display_order' => 110],
            ['name' => 'Pickleball Singles 4.0-4.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.0-4.5', 'gender' => 'open', 'team_size' => 1, 'display_order' => 120],
            ['name' => 'Pickleball Singles 5.0-5.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '5.0-5.5', 'gender' => 'open', 'team_size' => 1, 'display_order' => 130],

            // Pickleball Doubles
            ['name' => 'Pickleball Doubles 2.0-2.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '2.0-2.5', 'gender' => 'open', 'team_size' => 2, 'display_order' => 140],
            ['name' => 'Pickleball Doubles 3.0-3.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.0-3.5', 'gender' => 'open', 'team_size' => 2, 'display_order' => 150],
            ['name' => 'Pickleball Doubles 4.0-4.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.0-4.5', 'gender' => 'open', 'team_size' => 2, 'display_order' => 160],
            ['name' => 'Pickleball Doubles 5.0-5.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '5.0-5.5', 'gender' => 'open', 'team_size' => 2, 'display_order' => 170],

            // Pickleball Mixed Doubles
            ['name' => 'Pickleball Mixed Doubles 2.0-2.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '2.0-2.5', 'gender' => 'coed', 'team_size' => 2, 'display_order' => 180],
            ['name' => 'Pickleball Mixed Doubles 3.0-3.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.0-3.5', 'gender' => 'coed', 'team_size' => 2, 'display_order' => 190],
            ['name' => 'Pickleball Mixed Doubles 4.0-4.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.0-4.5', 'gender' => 'coed', 'team_size' => 2, 'display_order' => 200],
            ['name' => 'Pickleball Mixed Doubles 5.0-5.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '5.0-5.5', 'gender' => 'coed', 'team_size' => 2, 'display_order' => 210],

            // Futsal Youth
            ['name' => 'Futsal U10', 'sport' => 'Futsal', 'age_group' => 'U10', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 5, 'display_order' => 10],
            ['name' => 'Futsal U12', 'sport' => 'Futsal', 'age_group' => 'U12', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 5, 'display_order' => 20],
            ['name' => 'Futsal U14', 'sport' => 'Futsal', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 5, 'display_order' => 30],
            ['name' => 'Futsal U16', 'sport' => 'Futsal', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 5, 'display_order' => 40],
            ['name' => 'Futsal U18', 'sport' => 'Futsal', 'age_group' => 'U18', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 5, 'display_order' => 50],

            // Futsal Adults World Cup
            ['name' => 'Futsal Open World Cup', 'sport' => 'Futsal', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'open', 'team_size' => 5, 'display_order' => 100],

            // Wiffle Ball Youth
            ['name' => 'Wiffle Ball U10', 'sport' => 'Wiffle Ball', 'age_group' => 'U10', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 9, 'display_order' => 10],
            ['name' => 'Wiffle Ball U12', 'sport' => 'Wiffle Ball', 'age_group' => 'U12', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 9, 'display_order' => 20],
            ['name' => 'Wiffle Ball U14', 'sport' => 'Wiffle Ball', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 9, 'display_order' => 30],
            ['name' => 'Wiffle Ball U16', 'sport' => 'Wiffle Ball', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 9, 'display_order' => 40],
            ['name' => 'Wiffle Ball U18', 'sport' => 'Wiffle Ball', 'age_group' => 'U18', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 9, 'display_order' => 50],

            // Volleyball Youth
            ['name' => 'Volleyball U12', 'sport' => 'Volleyball', 'age_group' => 'U12', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 6, 'display_order' => 20],
            ['name' => 'Volleyball U14', 'sport' => 'Volleyball', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 6, 'display_order' => 30],
            ['name' => 'Volleyball U16', 'sport' => 'Volleyball', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 6, 'display_order' => 40],
            ['name' => 'Volleyball U18', 'sport' => 'Volleyball', 'age_group' => 'U18', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 6, 'display_order' => 50],

            // Volleyball Adults
            ['name' => 'Volleyball Open', 'sport' => 'Volleyball', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'open', 'team_size' => 6, 'display_order' => 100],

            // Basketball 3-on-3
            ['name' => 'Basketball 3v3 Open', 'sport' => 'Basketball', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'open', 'team_size' => 3, 'display_order' => 100],

            // Rollerhockey (Adults Only)
            ['name' => 'Rollerhockey Open', 'sport' => 'Rollerhockey', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'open', 'team_size' => 5, 'display_order' => 100],

            // Lacrosse (Boys Only)
            ['name' => 'Lacrosse U10 Boys', 'sport' => 'Lacrosse', 'age_group' => 'U10', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 10, 'display_order' => 10],
            ['name' => 'Lacrosse U12 Boys', 'sport' => 'Lacrosse', 'age_group' => 'U12', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 10, 'display_order' => 20],
            ['name' => 'Lacrosse U14 Boys', 'sport' => 'Lacrosse', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 10, 'display_order' => 30],
            ['name' => 'Lacrosse U16 Boys', 'sport' => 'Lacrosse', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 10, 'display_order' => 40],
            ['name' => 'Lacrosse U18 Boys', 'sport' => 'Lacrosse', 'age_group' => 'U18', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 10, 'display_order' => 50],

            // Field Hockey (Girls Only)
            ['name' => 'Field Hockey U10 Girls', 'sport' => 'Field Hockey', 'age_group' => 'U10', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 11, 'display_order' => 10],
            ['name' => 'Field Hockey U12 Girls', 'sport' => 'Field Hockey', 'age_group' => 'U12', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 11, 'display_order' => 20],
            ['name' => 'Field Hockey U14 Girls', 'sport' => 'Field Hockey', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 11, 'display_order' => 30],
            ['name' => 'Field Hockey U16 Girls', 'sport' => 'Field Hockey', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 11, 'display_order' => 40],
            ['name' => 'Field Hockey U18 Girls', 'sport' => 'Field Hockey', 'age_group' => 'U18', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 11, 'display_order' => 50],
            ['name' => 'Field Hockey Open', 'sport' => 'Field Hockey', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'female', 'team_size' => 11, 'display_order' => 100],

            // Cricket (Action Cricket)
            ['name' => 'Cricket Open', 'sport' => 'Cricket', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'open', 'team_size' => 11, 'display_order' => 100],

            // Handball
            ['name' => 'Handball Open', 'sport' => 'Handball', 'age_group' => 'Open', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 7, 'display_order' => 100],

            // Ping Pong
            ['name' => 'Ping Pong Open', 'sport' => 'Ping Pong', 'age_group' => 'Open', 'skill_level' => 'Intermediate', 'gender' => 'open', 'team_size' => 1, 'display_order' => 100],
        ];

        $divisions = [];
        foreach ($divisionsData as $divisionData) {
            $divisions[$divisionData['name']] = Division::firstOrCreate(
                ['name' => $divisionData['name']],
                [
                    'sport_id' => $sports[$divisionData['sport']]->id,
                    'age_group_id' => $ageGroups[$divisionData['age_group']]->id,
                    'skill_level_id' => $skillLevels[$divisionData['skill_level']]->id,
                    'gender' => $divisionData['gender'],
                    'team_size' => $divisionData['team_size'],
                    'display_order' => $divisionData['display_order'],
                ]
            );
        }

        return $divisions;
    }

    private function attachSportsToEvent(Event $event, array $sports): array
    {
        $eventSportsData = [
            'Pickleball' => ['max_courts' => 130, 'space_allocated_sqft' => 114400],
            'Futsal' => ['max_courts' => 10, 'space_allocated_sqft' => 24000],
            'Wiffle Ball' => ['max_courts' => 6, 'space_allocated_sqft' => 21600],
            'Volleyball' => ['max_courts' => 12, 'space_allocated_sqft' => 34560],
            'Basketball' => ['max_courts' => 8, 'space_allocated_sqft' => 37600],
            'Rollerhockey' => ['max_courts' => 2, 'space_allocated_sqft' => 17000],
            'Lacrosse' => ['max_courts' => 3, 'space_allocated_sqft' => 27000],
            'Field Hockey' => ['max_courts' => 3, 'space_allocated_sqft' => 27000],
            'Cricket' => ['max_courts' => 2, 'space_allocated_sqft' => 16000],
            'Handball' => ['max_courts' => 4, 'space_allocated_sqft' => 21600],
            'Ping Pong' => ['max_courts' => 20, 'space_allocated_sqft' => 4000],
        ];

        $eventSports = [];
        foreach ($eventSportsData as $sportName => $data) {
            $eventSports[$sportName] = EventSport::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'sport_id' => $sports[$sportName]->id,
                ],
                [
                    'max_courts' => $data['max_courts'],
                    'space_allocated_sqft' => $data['space_allocated_sqft'],
                ]
            );
        }

        return $eventSports;
    }

    private function createRegistrationProducts(Event $event, array $sports, array $divisions, array $timeSlots): void
    {
        // SATURDAY 7am-11am: Pickleball Singles ($90) + Wiffle Ball Kids
        foreach (['2.0-2.5', '3.0-3.5', '4.0-4.5', '5.0-5.5'] as $level) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Pickleball Singles {$level} (Saturday 7am-11am)",
                ],
                [
                    'type' => 'individual_registration',
                    'category' => 'adult',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Sat 7am-11am']->id,
                    'description' => "Round robin singles tournament - {$level} level",
                    'price' => 90.00,
                    'cash_prize' => 1000.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Pickleball Singles {$level}"]->id,
                    'display_order' => 100,
                ]
            );
        }

        // Wiffle Ball - Kids Only (Saturday 7am-11am)
        foreach (['U10', 'U12', 'U14', 'U16', 'U18'] as $age) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Wiffle Ball {$age} (Saturday 7am-11am)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'youth',
                    'sport_name' => 'Wiffle Ball',
                    'event_time_slot_id' => $timeSlots['Sat 7am-11am']->id,
                    'description' => "Round robin Wiffle Ball - {$age} division",
                    'price' => 500.00,
                    'cash_prize' => 500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 8,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Wiffle Ball {$age}"]->id,
                    'display_order' => 10,
                ]
            );
        }

        // SATURDAY 12pm-4pm: Futsal Kids + Pickleball Doubles + Pickleball Mixed Doubles
        foreach (['U10', 'U12', 'U14', 'U16', 'U18'] as $age) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Futsal {$age} World Cup (Saturday 12pm-4pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'youth',
                    'sport_name' => 'Futsal',
                    'event_time_slot_id' => $timeSlots['Sat 12pm-4pm']->id,
                    'description' => "Futsal World Cup - {$age} division (round robin)",
                    'price' => 500.00,
                    'cash_prize' => 1000.00,
                    'format' => 'round_robin',
                    'max_quantity' => 8,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Futsal {$age}"]->id,
                    'display_order' => 20,
                ]
            );
        }

        foreach (['2.0-2.5', '3.0-3.5', '4.0-4.5', '5.0-5.5'] as $level) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Pickleball Doubles {$level} (Saturday 12pm-4pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'adult',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Sat 12pm-4pm']->id,
                    'description' => "Round robin doubles tournament - {$level} level",
                    'price' => 125.00,
                    'cash_prize' => 1500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Pickleball Doubles {$level}"]->id,
                    'display_order' => 110,
                ]
            );

            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Pickleball Mixed Doubles {$level} (Saturday 12pm-4pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'adult',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Sat 12pm-4pm']->id,
                    'description' => "Round robin mixed doubles tournament - {$level} level",
                    'price' => 125.00,
                    'cash_prize' => 1500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Pickleball Mixed Doubles {$level}"]->id,
                    'display_order' => 120,
                ]
            );
        }

        // SATURDAY 4pm-8pm: Rollerhockey, Lacrosse, Field Hockey, Cricket
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Rollerhockey Open (Saturday 4pm-8pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Rollerhockey',
                'event_time_slot_id' => $timeSlots['Sat 4pm-8pm']->id,
                'description' => 'Adults only rollerhockey with cash prizes',
                'price' => 250.00,
                'cash_prize' => 2000.00,
                'format' => 'round_robin',
                'max_quantity' => 8,
                'current_quantity' => 0,
                'division_id' => $divisions['Rollerhockey Open']->id,
                'display_order' => 130,
            ]
        );

        foreach (['U10', 'U12', 'U14', 'U16', 'U18'] as $age) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Lacrosse {$age} Boys (Saturday 4pm-8pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'youth',
                    'sport_name' => 'Lacrosse',
                    'event_time_slot_id' => $timeSlots['Sat 4pm-8pm']->id,
                    'description' => "Lacrosse boys only - {$age} division",
                    'price' => 250.00,
                    'cash_prize' => 500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 8,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Lacrosse {$age} Boys"]->id,
                    'display_order' => 30,
                ]
            );
        }

        foreach (['U10', 'U12', 'U14', 'U16', 'U18', 'Open'] as $age) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Field Hockey {$age} Girls (Saturday 4pm-8pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => $age === 'Open' ? 'adult' : 'youth',
                    'sport_name' => 'Field Hockey',
                    'event_time_slot_id' => $timeSlots['Sat 4pm-8pm']->id,
                    'description' => "Field hockey girls only - {$age} division",
                    'price' => 250.00,
                    'cash_prize' => $age === 'Open' ? 1000.00 : 500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 8,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Field Hockey {$age}".($age === 'Open' ? '' : ' Girls')]->id,
                    'display_order' => $age === 'Open' ? 140 : 40,
                ]
            );
        }

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Cricket Open (Saturday 4pm-8pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Cricket',
                'event_time_slot_id' => $timeSlots['Sat 4pm-8pm']->id,
                'description' => 'Action Cricket - Round robin with cash prizes',
                'price' => 250.00,
                'cash_prize' => 2000.00,
                'format' => 'round_robin',
                'max_quantity' => 8,
                'current_quantity' => 0,
                'division_id' => $divisions['Cricket Open']->id,
                'display_order' => 150,
            ]
        );

        // SATURDAY 8pm-11pm: Volleyball, Basketball 3v3, Ping Pong
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Volleyball Open (Saturday 8pm-11pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Volleyball',
                'event_time_slot_id' => $timeSlots['Sat 8pm-11pm']->id,
                'description' => 'Adults only volleyball with cash prizes',
                'price' => 250.00,
                'cash_prize' => 2000.00,
                'format' => 'round_robin',
                'max_quantity' => 12,
                'current_quantity' => 0,
                'division_id' => $divisions['Volleyball Open']->id,
                'display_order' => 160,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Basketball 3v3 Open (Saturday 8pm-11pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Basketball',
                'event_time_slot_id' => $timeSlots['Sat 8pm-11pm']->id,
                'description' => '3 on 3 Basketball with cash prizes',
                'price' => 90.00,
                'cash_prize' => 1000.00,
                'format' => 'round_robin',
                'max_quantity' => 16,
                'current_quantity' => 0,
                'division_id' => $divisions['Basketball 3v3 Open']->id,
                'display_order' => 170,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Ping Pong Open (Saturday 8pm-11pm)',
            ],
            [
                'type' => 'individual_registration',
                'category' => 'adult',
                'sport_name' => 'Ping Pong',
                'event_time_slot_id' => $timeSlots['Sat 8pm-11pm']->id,
                'description' => 'Ping pong singles tournament',
                'price' => 45.00,
                'cash_prize' => 500.00,
                'format' => 'round_robin',
                'max_quantity' => 32,
                'current_quantity' => 0,
                'division_id' => $divisions['Ping Pong Open']->id,
                'display_order' => 180,
            ]
        );

        // SUNDAY 7am-11am: Pickleball Singles
        foreach (['2.0-2.5', '3.0-3.5', '4.0-4.5', '5.0-5.5'] as $level) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Pickleball Singles {$level} (Sunday 7am-11am)",
                ],
                [
                    'type' => 'individual_registration',
                    'category' => 'adult',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Sun 7am-11am']->id,
                    'description' => "Round robin singles tournament - {$level} level",
                    'price' => 90.00,
                    'cash_prize' => 1000.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Pickleball Singles {$level}"]->id,
                    'display_order' => 200,
                ]
            );
        }

        // SUNDAY 12pm-4pm: Futsal World Cup (Adults) + Pickleball Doubles
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Futsal Open World Cup (Sunday 12pm-4pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Futsal',
                'event_time_slot_id' => $timeSlots['Sun 12pm-4pm']->id,
                'description' => 'Futsal World Cup - Adults division (round robin)',
                'price' => 500.00,
                'cash_prize' => 3000.00,
                'format' => 'round_robin',
                'max_quantity' => 12,
                'current_quantity' => 0,
                'division_id' => $divisions['Futsal Open World Cup']->id,
                'display_order' => 210,
            ]
        );

        foreach (['2.0-2.5', '3.0-3.5', '4.0-4.5', '5.0-5.5'] as $level) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Pickleball Doubles {$level} (Sunday 12pm-4pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'adult',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Sun 12pm-4pm']->id,
                    'description' => "Round robin doubles tournament - {$level} level",
                    'price' => 125.00,
                    'cash_prize' => 1500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Pickleball Doubles {$level}"]->id,
                    'display_order' => 220,
                ]
            );
        }

        // SUNDAY 5pm-8pm: Handball, Volleyball (kids only), Basketball 3v3, Cricket, Ping Pong
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Handball Open (Sunday 5pm-8pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Handball',
                'event_time_slot_id' => $timeSlots['Sun 5pm-8pm']->id,
                'description' => 'Handball tournament with cash prizes',
                'price' => 500.00,
                'cash_prize' => 1500.00,
                'format' => 'round_robin',
                'max_quantity' => 8,
                'current_quantity' => 0,
                'division_id' => $divisions['Handball Open']->id,
                'display_order' => 230,
            ]
        );

        foreach (['U12', 'U14', 'U16', 'U18'] as $age) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Volleyball {$age} (Sunday 5pm-8pm)",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'youth',
                    'sport_name' => 'Volleyball',
                    'event_time_slot_id' => $timeSlots['Sun 5pm-8pm']->id,
                    'description' => "Volleyball kids only - {$age} division",
                    'price' => 500.00,
                    'cash_prize' => 500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 8,
                    'current_quantity' => 0,
                    'division_id' => $divisions["Volleyball {$age}"]->id,
                    'display_order' => 50,
                ]
            );
        }

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Basketball 3v3 Open (Sunday 5pm-8pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Basketball',
                'event_time_slot_id' => $timeSlots['Sun 5pm-8pm']->id,
                'description' => '3 on 3 Basketball with cash prizes',
                'price' => 90.00,
                'cash_prize' => 1000.00,
                'format' => 'round_robin',
                'max_quantity' => 16,
                'current_quantity' => 0,
                'division_id' => $divisions['Basketball 3v3 Open']->id,
                'display_order' => 240,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Cricket Open (Sunday 5pm-8pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Cricket',
                'event_time_slot_id' => $timeSlots['Sun 5pm-8pm']->id,
                'description' => 'Action Cricket - Round robin with cash prizes',
                'price' => 250.00,
                'cash_prize' => 2000.00,
                'format' => 'round_robin',
                'max_quantity' => 8,
                'current_quantity' => 0,
                'division_id' => $divisions['Cricket Open']->id,
                'display_order' => 250,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Ping Pong Open (Sunday 5pm-8pm)',
            ],
            [
                'type' => 'individual_registration',
                'category' => 'adult',
                'sport_name' => 'Ping Pong',
                'event_time_slot_id' => $timeSlots['Sun 5pm-8pm']->id,
                'description' => 'Ping pong singles tournament',
                'price' => 45.00,
                'cash_prize' => 500.00,
                'format' => 'round_robin',
                'max_quantity' => 32,
                'current_quantity' => 0,
                'division_id' => $divisions['Ping Pong Open']->id,
                'display_order' => 260,
            ]
        );

        // SUNDAY 8pm-11pm: Cricket, Basketball 3v3
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Cricket Open (Sunday 8pm-11pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Cricket',
                'event_time_slot_id' => $timeSlots['Sun 8pm-11pm']->id,
                'description' => 'Action Cricket - Round robin with cash prizes',
                'price' => 250.00,
                'cash_prize' => 2000.00,
                'format' => 'round_robin',
                'max_quantity' => 8,
                'current_quantity' => 0,
                'division_id' => $divisions['Cricket Open']->id,
                'display_order' => 270,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Basketball 3v3 Open (Sunday 8pm-11pm)',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Basketball',
                'event_time_slot_id' => $timeSlots['Sun 8pm-11pm']->id,
                'description' => '3 on 3 Basketball with cash prizes',
                'price' => 90.00,
                'cash_prize' => 1000.00,
                'format' => 'round_robin',
                'max_quantity' => 16,
                'current_quantity' => 0,
                'division_id' => $divisions['Basketball 3v3 Open']->id,
                'display_order' => 280,
            ]
        );
    }

    private function createSpectatorProducts(Event $event): void
    {
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Saturday Spectator Pass',
            ],
            [
                'type' => 'spectator_ticket',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'All-day access to watch all sports on Saturday, January 17th, 2025',
                'price' => 25.00,
                'max_quantity' => 3000,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 1000,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Sunday Spectator Pass',
            ],
            [
                'type' => 'spectator_ticket',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'All-day access to watch all sports on Sunday, January 18th, 2025',
                'price' => 25.00,
                'max_quantity' => 3000,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 1001,
            ]
        );

        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Weekend Spectator Pass',
            ],
            [
                'type' => 'spectator_ticket',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Full weekend access to watch all sports on both days',
                'price' => 40.00,
                'max_quantity' => 2500,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 1002,
            ]
        );
    }

    private function createAdvertisingProducts(Event $event): void
    {
        // Vendor Booth
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Vendor Booth - 10x10 Space',
            ],
            [
                'type' => 'booth',
                'category' => 'general',
                'sport_name' => null,
                'description' => '10x10 booth space for product/service promotion throughout the weekend',
                'price' => 1500.00,
                'max_quantity' => 30,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2000,
            ]
        );

        // Banner Advertising - Prime Location
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Banner Advertisement - Prime Location',
            ],
            [
                'type' => 'banner',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Large banner display in high-traffic area (entrance/main court)',
                'price' => 2500.00,
                'max_quantity' => 10,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2001,
            ]
        );

        // Banner Advertising - Standard Location
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Banner Advertisement - Standard Location',
            ],
            [
                'type' => 'banner',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Banner display in spectator seating areas',
                'price' => 1500.00,
                'max_quantity' => 40,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2002,
            ]
        );

        // Premium Package
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Premium Sponsor Package - Booth + Banner',
            ],
            [
                'type' => 'booth',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Premium package including booth space and prime banner location',
                'price' => 3500.00,
                'max_quantity' => 15,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2003,
            ]
        );
    }
}
