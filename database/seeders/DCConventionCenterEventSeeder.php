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

class DCConventionCenterEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Sports with accurate space requirements
        $sports = $this->createSports();

        // Create Venue (DC Convention Center)
        $venue = $this->createVenue();

        // Create Event
        $event = $this->createEvent($venue);

        // Create Time Slots for the event
        $timeSlots = $this->createTimeSlots($event);

        // Create Age Groups
        $ageGroups = $this->createAgeGroups();

        // Create Skill Levels
        $skillLevels = $this->createSkillLevels();

        // Create Divisions
        $divisions = $this->createDivisions($ageGroups, $skillLevels, $sports);

        // Attach Sports to Event with court counts
        $eventSports = $this->attachSportsToEvent($event, $sports);

        // Create Products for registrations
        $this->createRegistrationProducts($event, $sports, $divisions, $eventSports, $timeSlots);

        // Create Spectator Products
        $this->createSpectatorProducts($event);

        // Create Advertising Products
        $this->createAdvertisingProducts($event);
    }

    private function createSports(): array
    {
        $sportsData = [
            ['name' => 'Pickleball', 'default_space_required_sqft' => 880],  // 44' x 20'
            ['name' => 'Futsal', 'default_space_required_sqft' => 5600],     // 140' x 40'
            ['name' => 'Volleyball', 'default_space_required_sqft' => 2880], // 60' x 48'
            ['name' => 'Basketball', 'default_space_required_sqft' => 4700], // 94' x 50'
            ['name' => 'Dodgeball', 'default_space_required_sqft' => 3600],  // 60' x 60'
            ['name' => 'Cricket', 'default_space_required_sqft' => 8000],    // Indoor cricket
            ['name' => 'Tennis', 'default_space_required_sqft' => 2808],     // 78' x 36'
            ['name' => 'Badminton', 'default_space_required_sqft' => 880],   // 44' x 20'
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
            ['name' => 'Walter E. Washington Convention Center'],
            [
                'address' => '801 Mt Vernon Pl NW, Washington, DC 20001',
                'sports_space_sqft' => 120000,      // 120,000 sq ft usable space
                'spectator_space_sqft' => 30000,    // 30,000 sq ft for spectators
                'total_banner_spots' => 40,         // Banner advertising spots
                'total_booth_spots' => 20,          // Booth spaces
            ]
        );
    }

    private function createEvent(Venue $venue): Event
    {
        return Event::firstOrCreate(
            ['name' => 'DC Multi-Sport Invitational 2026'],
            [
                'venue_id' => $venue->id,
                'start_date' => '2026-01-17',
                'end_date' => '2026-01-18',
                'status' => 'draft',
                'refund_cutoff_date' => '2026-01-03', // 2 weeks before event
            ]
        );
    }

    private function createTimeSlots(Event $event): array
    {
        $timeSlotsData = [
            // Saturday Slots
            ['day' => 'Saturday', 'label' => '7am-11am', 'start' => '2026-01-17 07:00:00', 'end' => '2026-01-17 11:00:00', 'space' => 120000],
            ['day' => 'Saturday', 'label' => '12pm-4pm', 'start' => '2026-01-17 12:00:00', 'end' => '2026-01-17 16:00:00', 'space' => 120000],
            ['day' => 'Saturday', 'label' => '4pm-8pm', 'start' => '2026-01-17 16:00:00', 'end' => '2026-01-17 20:00:00', 'space' => 120000],
            ['day' => 'Saturday', 'label' => '8pm-11pm', 'start' => '2026-01-17 20:00:00', 'end' => '2026-01-17 23:00:00', 'space' => 120000],

            // Sunday Slots
            ['day' => 'Sunday', 'label' => '7am-11am', 'start' => '2026-01-18 07:00:00', 'end' => '2026-01-18 11:00:00', 'space' => 120000],
            ['day' => 'Sunday', 'label' => '12pm-4pm', 'start' => '2026-01-18 12:00:00', 'end' => '2026-01-18 16:00:00', 'space' => 120000],
            ['day' => 'Sunday', 'label' => '5pm-8pm', 'start' => '2026-01-18 17:00:00', 'end' => '2026-01-18 20:00:00', 'space' => 120000],
            ['day' => 'Sunday', 'label' => '8pm-11pm', 'start' => '2026-01-18 20:00:00', 'end' => '2026-01-18 23:00:00', 'space' => 120000],
        ];

        $timeSlots = [];
        foreach ($timeSlotsData as $slotData) {
            $timeSlots["{$slotData['day']} {$slotData['label']}"] = EventTimeSlot::firstOrCreate(
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
            // Youth
            ['name' => 'U10', 'category' => 'youth', 'min_age' => null, 'max_age' => 10, 'display_order' => 10],
            ['name' => 'U12', 'category' => 'youth', 'min_age' => null, 'max_age' => 12, 'display_order' => 20],
            ['name' => 'U14', 'category' => 'youth', 'min_age' => null, 'max_age' => 14, 'display_order' => 30],
            ['name' => 'U16', 'category' => 'youth', 'min_age' => null, 'max_age' => 16, 'display_order' => 40],
            ['name' => 'U18', 'category' => 'youth', 'min_age' => null, 'max_age' => 18, 'display_order' => 50],

            // Adult
            ['name' => 'Open', 'category' => 'adult', 'min_age' => 18, 'max_age' => null, 'display_order' => 100],

            // Senior
            ['name' => '35+', 'category' => 'senior', 'min_age' => 35, 'max_age' => null, 'display_order' => 200],
            ['name' => '50+', 'category' => 'senior', 'min_age' => 50, 'max_age' => null, 'display_order' => 210],
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
            ['name' => 'Beginner', 'description' => 'New to the sport, learning basics'],
            ['name' => 'Intermediate', 'description' => 'Regular player with solid fundamentals'],
            ['name' => 'Advanced', 'description' => 'Competitive player with tournament experience'],
            ['name' => 'Elite', 'description' => 'Top-tier competitive player'],
            ['name' => '3.0', 'description' => 'Pickleball rating: Beginner'],
            ['name' => '3.5', 'description' => 'Pickleball rating: Intermediate'],
            ['name' => '4.0', 'description' => 'Pickleball rating: Advanced'],
            ['name' => '4.5+', 'description' => 'Pickleball rating: Elite'],
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
            // Pickleball Divisions (Adult)
            ['name' => 'Pickleball Men\'s Singles 3.0', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.0', 'gender' => 'male', 'team_size' => 1, 'display_order' => 100],
            ['name' => 'Pickleball Men\'s Singles 3.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.5', 'gender' => 'male', 'team_size' => 1, 'display_order' => 110],
            ['name' => 'Pickleball Men\'s Singles 4.0', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.0', 'gender' => 'male', 'team_size' => 1, 'display_order' => 120],
            ['name' => 'Pickleball Men\'s Singles 4.5+', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.5+', 'gender' => 'male', 'team_size' => 1, 'display_order' => 130],
            ['name' => 'Pickleball Women\'s Singles 3.0', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.0', 'gender' => 'female', 'team_size' => 1, 'display_order' => 140],
            ['name' => 'Pickleball Women\'s Singles 3.5', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '3.5', 'gender' => 'female', 'team_size' => 1, 'display_order' => 150],
            ['name' => 'Pickleball Women\'s Singles 4.0', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.0', 'gender' => 'female', 'team_size' => 1, 'display_order' => 160],
            ['name' => 'Pickleball Women\'s Singles 4.5+', 'sport' => 'Pickleball', 'age_group' => 'Open', 'skill_level' => '4.5+', 'gender' => 'female', 'team_size' => 1, 'display_order' => 170],

            // Pickleball Divisions (Senior)
            ['name' => 'Pickleball Men\'s 50+ Singles', 'sport' => 'Pickleball', 'age_group' => '50+', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 1, 'display_order' => 200],
            ['name' => 'Pickleball Women\'s 50+ Singles', 'sport' => 'Pickleball', 'age_group' => '50+', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 1, 'display_order' => 210],

            // Futsal Divisions (Youth)
            ['name' => 'Futsal U14 Boys', 'sport' => 'Futsal', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 5, 'display_order' => 30],
            ['name' => 'Futsal U14 Girls', 'sport' => 'Futsal', 'age_group' => 'U14', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 5, 'display_order' => 40],

            // Futsal Divisions (Adult)
            ['name' => 'Futsal Men\'s Open', 'sport' => 'Futsal', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'male', 'team_size' => 5, 'display_order' => 100],
            ['name' => 'Futsal Women\'s Open', 'sport' => 'Futsal', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'female', 'team_size' => 5, 'display_order' => 110],
            ['name' => 'Futsal Coed', 'sport' => 'Futsal', 'age_group' => 'Open', 'skill_level' => 'Intermediate', 'gender' => 'coed', 'team_size' => 5, 'display_order' => 120],

            // Volleyball Divisions (Adult)
            ['name' => 'Volleyball Men\'s Open', 'sport' => 'Volleyball', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'male', 'team_size' => 6, 'display_order' => 100],
            ['name' => 'Volleyball Women\'s Open', 'sport' => 'Volleyball', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'female', 'team_size' => 6, 'display_order' => 110],
            ['name' => 'Volleyball Coed', 'sport' => 'Volleyball', 'age_group' => 'Open', 'skill_level' => 'Intermediate', 'gender' => 'coed', 'team_size' => 6, 'display_order' => 120],

            // Basketball Divisions (Youth)
            ['name' => 'Basketball U16 Boys', 'sport' => 'Basketball', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'male', 'team_size' => 5, 'display_order' => 40],
            ['name' => 'Basketball U16 Girls', 'sport' => 'Basketball', 'age_group' => 'U16', 'skill_level' => 'Intermediate', 'gender' => 'female', 'team_size' => 5, 'display_order' => 50],

            // Basketball Divisions (Adult)
            ['name' => 'Basketball Men\'s Open', 'sport' => 'Basketball', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'male', 'team_size' => 5, 'display_order' => 100],
            ['name' => 'Basketball Women\'s Open', 'sport' => 'Basketball', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'female', 'team_size' => 5, 'display_order' => 110],

            // Other Sports (Adult)
            ['name' => 'Dodgeball Coed', 'sport' => 'Dodgeball', 'age_group' => 'Open', 'skill_level' => 'Beginner', 'gender' => 'coed', 'team_size' => 6, 'display_order' => 100],
            ['name' => 'Cricket Men\'s Open', 'sport' => 'Cricket', 'age_group' => 'Open', 'skill_level' => 'Advanced', 'gender' => 'male', 'team_size' => 11, 'display_order' => 100],
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
            'Pickleball' => ['max_courts' => 60, 'space_allocated_sqft' => 52800],  // 60 courts
            'Futsal' => ['max_courts' => 4, 'space_allocated_sqft' => 22400],       // 4 courts
            'Volleyball' => ['max_courts' => 6, 'space_allocated_sqft' => 17280],   // 6 courts
            'Basketball' => ['max_courts' => 3, 'space_allocated_sqft' => 14100],   // 3 courts
            'Dodgeball' => ['max_courts' => 2, 'space_allocated_sqft' => 7200],     // 2 courts
            'Cricket' => ['max_courts' => 1, 'space_allocated_sqft' => 8000],       // 1 pitch
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

    private function createRegistrationProducts(Event $event, array $sports, array $divisions, array $eventSports, array $timeSlots): void
    {
        // Pickleball Singles Registration - Adult ($90/person) - Saturday & Sunday 7am-11am
        $pickleballAdultDivisions = [
            'Pickleball Men\'s Singles 3.0', 'Pickleball Men\'s Singles 3.5',
            'Pickleball Men\'s Singles 4.0', 'Pickleball Men\'s Singles 4.5+',
            'Pickleball Women\'s Singles 3.0', 'Pickleball Women\'s Singles 3.5',
            'Pickleball Women\'s Singles 4.0', 'Pickleball Women\'s Singles 4.5+',
        ];

        foreach ($pickleballAdultDivisions as $divisionName) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => $divisionName,
                ],
                [
                    'type' => 'individual_registration',
                    'category' => 'adult',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Saturday 7am-11am']->id,
                    'description' => "Round robin singles tournament - {$divisionName}",
                    'price' => 90.00,
                    'cash_prize' => 500.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 100,
                ]
            );
        }

        // Pickleball Singles Registration - Senior ($90/person) - Saturday 7am-11am
        $pickleballSeniorDivisions = [
            'Pickleball Men\'s 50+ Singles', 'Pickleball Women\'s 50+ Singles',
        ];

        foreach ($pickleballSeniorDivisions as $divisionName) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => $divisionName,
                ],
                [
                    'type' => 'individual_registration',
                    'category' => 'senior',
                    'sport_name' => 'Pickleball',
                    'event_time_slot_id' => $timeSlots['Saturday 7am-11am']->id,
                    'description' => "Round robin singles tournament - {$divisionName}",
                    'price' => 90.00,
                    'cash_prize' => 300.00,
                    'format' => 'round_robin',
                    'max_quantity' => 32,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 200,
                ]
            );
        }

        // Futsal - Adult Team Registration ($500/team) - Sunday 12pm-4pm
        $futsalAdultDivisions = [
            'Futsal Men\'s Open' => 8,
            'Futsal Women\'s Open' => 8,
            'Futsal Coed' => 8,
        ];

        foreach ($futsalAdultDivisions as $divisionName => $maxTeams) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Team Registration - {$divisionName}",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'adult',
                    'sport_name' => 'Futsal',
                    'event_time_slot_id' => $timeSlots['Sunday 12pm-4pm']->id,
                    'description' => "Futsal World Cup - {$divisionName} division (round robin)",
                    'price' => 500.00,
                    'cash_prize' => 2000.00,
                    'format' => 'round_robin',
                    'max_quantity' => $maxTeams,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 110,
                ]
            );
        }

        // Futsal - Youth Team Registration ($500/team) - Saturday 12pm-4pm
        $futsalYouthDivisions = [
            'Futsal U14 Boys' => 6,
            'Futsal U14 Girls' => 6,
        ];

        foreach ($futsalYouthDivisions as $divisionName => $maxTeams) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Team Registration - {$divisionName}",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'youth',
                    'sport_name' => 'Futsal',
                    'event_time_slot_id' => $timeSlots['Saturday 12pm-4pm']->id,
                    'description' => "Futsal World Cup - {$divisionName} division (round robin)",
                    'price' => 500.00,
                    'cash_prize' => 1000.00,
                    'format' => 'round_robin',
                    'max_quantity' => $maxTeams,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 10,
                ]
            );
        }

        // Volleyball - Adult Team Registration ($250/team) - Saturday 8pm-11pm
        $volleyballAdultDivisions = [
            'Volleyball Men\'s Open' => 12,
            'Volleyball Women\'s Open' => 12,
            'Volleyball Coed' => 12,
        ];

        foreach ($volleyballAdultDivisions as $divisionName => $maxTeams) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Team Registration - {$divisionName}",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'adult',
                    'sport_name' => 'Volleyball',
                    'event_time_slot_id' => $timeSlots['Saturday 8pm-11pm']->id,
                    'description' => "Team registration for {$divisionName} division (round robin)",
                    'price' => 250.00,
                    'cash_prize' => 1500.00,
                    'format' => 'round_robin',
                    'max_quantity' => $maxTeams,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 120,
                ]
            );
        }

        // Basketball - Adult Team Registration ($90/team) - Saturday & Sunday 8pm-11pm
        $basketballAdultDivisions = [
            'Basketball Men\'s Open' => 8,
            'Basketball Women\'s Open' => 8,
        ];

        foreach ($basketballAdultDivisions as $divisionName => $maxTeams) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Team Registration - {$divisionName}",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'adult',
                    'sport_name' => 'Basketball',
                    'event_time_slot_id' => $timeSlots['Saturday 8pm-11pm']->id,
                    'description' => "3 on 3 Basketball - {$divisionName} division (round robin)",
                    'price' => 90.00,
                    'cash_prize' => 1000.00,
                    'format' => 'round_robin',
                    'max_quantity' => $maxTeams,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 130,
                ]
            );
        }

        // Basketball - Youth Team Registration ($90/team) - Saturday 4pm-8pm
        $basketballYouthDivisions = [
            'Basketball U16 Boys' => 8,
            'Basketball U16 Girls' => 8,
        ];

        foreach ($basketballYouthDivisions as $divisionName => $maxTeams) {
            Product::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'name' => "Team Registration - {$divisionName}",
                ],
                [
                    'type' => 'team_registration',
                    'category' => 'youth',
                    'sport_name' => 'Basketball',
                    'event_time_slot_id' => $timeSlots['Saturday 4pm-8pm']->id,
                    'description' => "3 on 3 Basketball - {$divisionName} division (round robin)",
                    'price' => 90.00,
                    'cash_prize' => 500.00,
                    'format' => 'round_robin',
                    'max_quantity' => $maxTeams,
                    'current_quantity' => 0,
                    'division_id' => $divisions[$divisionName]->id,
                    'display_order' => 20,
                ]
            );
        }

        // Dodgeball - Adult Team Registration ($90/team) - Saturday 4pm-8pm
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Team Registration - Dodgeball Coed',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Dodgeball',
                'event_time_slot_id' => $timeSlots['Saturday 4pm-8pm']->id,
                'description' => 'Dodgeball Coed division (round robin)',
                'price' => 90.00,
                'cash_prize' => 500.00,
                'format' => 'round_robin',
                'max_quantity' => 12,
                'current_quantity' => 0,
                'division_id' => $divisions['Dodgeball Coed']->id,
                'display_order' => 140,
            ]
        );

        // Cricket - Adult Team Registration ($250/team) - Saturday 4pm-8pm & Sunday 8pm-11pm
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Team Registration - Cricket Men\'s Open',
            ],
            [
                'type' => 'team_registration',
                'category' => 'adult',
                'sport_name' => 'Cricket',
                'event_time_slot_id' => $timeSlots['Saturday 4pm-8pm']->id,
                'description' => 'Action Cricket - Round robin tournament with cash prizes',
                'price' => 250.00,
                'cash_prize' => 2000.00,
                'format' => 'round_robin',
                'max_quantity' => 4,
                'current_quantity' => 0,
                'division_id' => $divisions['Cricket Men\'s Open']->id,
                'display_order' => 150,
            ]
        );
    }

    private function createSpectatorProducts(Event $event): void
    {
        // Saturday Spectator Pass
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Saturday Spectator Pass',
            ],
            [
                'type' => 'spectator_ticket',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'All-day access to watch all sports on Saturday, January 17th, 2026',
                'price' => 29.00,
                'max_quantity' => 2500,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 1000,
            ]
        );

        // Sunday Spectator Pass
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Sunday Spectator Pass',
            ],
            [
                'type' => 'spectator_ticket',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'All-day access to watch all sports on Sunday, January 18th, 2026',
                'price' => 29.00,
                'max_quantity' => 2500,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 1001,
            ]
        );

        // Weekend Spectator Pass
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
                'price' => 49.00,
                'max_quantity' => 2000,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 1002,
            ]
        );
    }

    private function createAdvertisingProducts(Event $event): void
    {
        // Court Partition Branding + Booth Package
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Premium Court Branding + Booth Package',
            ],
            [
                'type' => 'advertising',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Court partition branding with logo visibility + 10x10 booth space + digital advertising',
                'price' => 5000.00,
                'max_quantity' => 20,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2000,
            ]
        );

        // Banner Advertising - Prime Location
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Banner Advertising - Prime Location',
            ],
            [
                'type' => 'advertising',
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
                'name' => 'Banner Advertising - Standard Location',
            ],
            [
                'type' => 'advertising',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Banner display in spectator seating areas',
                'price' => 1500.00,
                'max_quantity' => 30,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2002,
            ]
        );

        // Website Advertising - Homepage Hero
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Website Advertising - Homepage Hero',
            ],
            [
                'type' => 'advertising',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Premium placement on event website homepage with clickthrough tracking',
                'price' => 1000.00,
                'max_quantity' => 1,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2003,
            ]
        );

        // Website Advertising - Sidebar
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Website Advertising - Sidebar',
            ],
            [
                'type' => 'advertising',
                'category' => 'general',
                'sport_name' => null,
                'description' => 'Sidebar ad placement on all event website pages',
                'price' => 500.00,
                'max_quantity' => 5,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2004,
            ]
        );

        // Booth Only Package
        Product::firstOrCreate(
            [
                'event_id' => $event->id,
                'name' => 'Vendor Booth - 10x10 Space',
            ],
            [
                'type' => 'advertising',
                'category' => 'general',
                'sport_name' => null,
                'description' => '10x10 booth space for product/service promotion',
                'price' => 1500.00,
                'max_quantity' => 20,
                'current_quantity' => 0,
                'division_id' => null,
                'display_order' => 2005,
            ]
        );
    }
}
