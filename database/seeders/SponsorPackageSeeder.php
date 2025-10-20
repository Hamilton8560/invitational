<?php

namespace Database\Seeders;

use App\Models\SponsorPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SponsorPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Gold Package - $5,000
        $gold = SponsorPackage::create([
            'event_id' => null, // Template - not tied to specific event
            'tier' => 'gold',
            'name' => 'Gold Sponsor Package',
            'description' => 'Our premier sponsorship package offering maximum visibility and engagement opportunities across all aspects of the event.',
            'price' => 5000.00,
            'max_quantity' => null, // Unlimited
            'current_quantity' => 0,
            'is_active' => DB::raw('true'),
            'is_template' => DB::raw('true'),
            'display_order' => 1,
        ]);

        // Gold Benefits
        $gold->benefits()->createMany([
            [
                'benefit_type' => 'jersey_logo',
                'name' => 'Logo on All Jerseys/Uniforms',
                'description' => 'Your company logo prominently displayed on all participant jerseys',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 1,
            ],
            [
                'benefit_type' => 'stage_banner',
                'name' => 'Main Stage Banners',
                'description' => 'Large banners displayed at 2 premium main stage locations',
                'quantity' => 2,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 2,
            ],
            [
                'benefit_type' => 'social_media',
                'name' => 'Social Media Shoutouts',
                'description' => 'Featured posts on our social media channels (Instagram, Facebook, Twitter)',
                'quantity' => 5,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 3,
            ],
            [
                'benefit_type' => 'website_logo',
                'name' => 'Website Homepage Logo',
                'description' => 'Prominent logo placement on event website homepage with link to your site',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 4,
            ],
            [
                'benefit_type' => 'email_marketing',
                'name' => 'Email Marketing Mention',
                'description' => 'Featured mention in 3 email campaigns sent to all participants',
                'quantity' => 3,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 5,
            ],
            [
                'benefit_type' => 'program_ad',
                'name' => 'Full-Page Program Ad',
                'description' => 'Full-page color advertisement in event program booklet',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 6,
            ],
            [
                'benefit_type' => 'booth_space',
                'name' => 'Booth Space (10x10)',
                'description' => 'Premium 10x10 booth space in high-traffic vendor area',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 7,
            ],
            [
                'benefit_type' => 'vip_tickets',
                'name' => 'VIP Tickets',
                'description' => 'Complimentary VIP access passes for your team',
                'quantity' => 10,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 8,
            ],
            [
                'benefit_type' => 'giveaway_rights',
                'name' => 'Giveaway Distribution Rights',
                'description' => 'Exclusive right to distribute promotional items and giveaways at the event',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 9,
            ],
            [
                'benefit_type' => 'pa_announcements',
                'name' => 'PA Announcements',
                'description' => 'Live announcements recognizing your sponsorship throughout the event',
                'quantity' => 5,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 10,
            ],
        ]);

        // Silver Package - $2,500
        $silver = SponsorPackage::create([
            'event_id' => null,
            'tier' => 'silver',
            'name' => 'Silver Sponsor Package',
            'description' => 'Great value package offering strong visibility and engagement with event participants.',
            'price' => 2500.00,
            'max_quantity' => null,
            'current_quantity' => 0,
            'is_active' => DB::raw('true'),
            'is_template' => DB::raw('true'),
            'display_order' => 2,
        ]);

        // Silver Benefits
        $silver->benefits()->createMany([
            [
                'benefit_type' => 'booth_space',
                'name' => 'Booth Space (10x10)',
                'description' => 'Premium 10x10 booth space in vendor area',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 1,
            ],
            [
                'benefit_type' => 'website_logo',
                'name' => 'Website Sponsors Page Logo',
                'description' => 'Logo placement on dedicated sponsors page with link to your site',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 2,
            ],
            [
                'benefit_type' => 'social_media',
                'name' => 'Social Media Posts',
                'description' => 'Featured posts on our social media channels',
                'quantity' => 2,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 3,
            ],
            [
                'benefit_type' => 'program_ad',
                'name' => 'Half-Page Program Ad',
                'description' => 'Half-page color advertisement in event program',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 4,
            ],
            [
                'benefit_type' => 'court_signage',
                'name' => 'Court Signage',
                'description' => 'Signage displayed at 1 premium court location',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 5,
            ],
            [
                'benefit_type' => 'vip_tickets',
                'name' => 'VIP Tickets',
                'description' => 'Complimentary VIP access passes',
                'quantity' => 4,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 6,
            ],
        ]);

        // Bronze Package - $1,000
        $bronze = SponsorPackage::create([
            'event_id' => null,
            'tier' => 'bronze',
            'name' => 'Bronze Sponsor Package',
            'description' => 'Entry-level sponsorship package perfect for local businesses looking to support the event.',
            'price' => 1000.00,
            'max_quantity' => null,
            'current_quantity' => 0,
            'is_active' => DB::raw('true'),
            'is_template' => DB::raw('true'),
            'display_order' => 3,
        ]);

        // Bronze Benefits
        $bronze->benefits()->createMany([
            [
                'benefit_type' => 'program_ad',
                'name' => 'Quarter-Page Program Ad',
                'description' => 'Quarter-page advertisement in event program',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 1,
            ],
            [
                'benefit_type' => 'giveaway_rights',
                'name' => 'Giveaway Distribution Rights',
                'description' => 'Right to distribute promotional items at the event',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 2,
            ],
            [
                'benefit_type' => 'website_logo',
                'name' => 'Website Listing',
                'description' => 'Company name and logo listed on sponsors page',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('true'),
                'display_order' => 3,
            ],
            [
                'benefit_type' => 'social_media',
                'name' => 'Social Media Mention',
                'description' => 'Thank you mention on social media',
                'quantity' => 1,
                'is_enabled' => DB::raw('true'),
                'requires_asset_upload' => DB::raw('false'),
                'display_order' => 4,
            ],
        ]);
    }
}
