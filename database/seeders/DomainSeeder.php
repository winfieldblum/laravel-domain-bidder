<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $agentic = Domain::query()->updateOrCreate(
            ['hostname' => 'agentic.io'],
            [
                'display_name' => 'Agentic.io',
                'tagline' => 'The perfect identity for your AI agency, autonomous agent platform, or next-gen software company.',
                'description' => 'Own the authoritative domain for the autonomous agent revolution.',
                'is_active' => true,
                'mail_from_address' => 'noreply@agentic.io',
                'mail_from_name' => 'Agentic.io',
                'notification_email' => config('bids.notification_email'),
            ],
        );

        $agentic->features()->delete();
        $agentic->features()->createMany([
            [
                'icon' => 'Globe',
                'title' => 'Category Defining',
                'description' => 'Own the authoritative domain for the autonomous agent revolution.',
                'color' => 'text-blue-500',
                'sort_order' => 1,
            ],
            [
                'icon' => 'TrendingUp',
                'title' => 'High Valuation',
                'description' => '.io domains are the gold standard for tech startups and AI platforms.',
                'color' => 'text-green-500',
                'sort_order' => 2,
            ],
            [
                'icon' => 'Shield',
                'title' => 'Brand Authority',
                'description' => 'Instant credibility with a name that defines the future of software.',
                'color' => 'text-purple-500',
                'sort_order' => 3,
            ],
        ]);

        $agentic->sellingPoints()->delete();
        $agentic->sellingPoints()->createMany([
            ['text' => 'Short, memorable, and easy to spell', 'sort_order' => 1],
            ['text' => "Directly relates to 'Agentic AI' - the hottest trend in tech", 'sort_order' => 2],
            ['text' => '.io extension is preferred by developer tools and SaaS platforms', 'sort_order' => 3],
            ['text' => 'Instant SEO advantage for agent-related keywords', 'sort_order' => 4],
        ]);

        $onlinescrums = Domain::query()->updateOrCreate(
            ['hostname' => 'onlinescrums.com'],
            [
                'display_name' => 'OnlineScrums.com',
                'tagline' => 'The perfect domain for remote agile teams, standup tools, and scrum platforms.',
                'description' => 'A memorable domain for the future of online collaboration and agile delivery.',
                'is_active' => true,
                'mail_from_address' => 'noreply@onlinescrums.com',
                'mail_from_name' => 'OnlineScrums.com',
                'notification_email' => config('bids.notification_email'),
            ],
        );

        $onlinescrums->features()->delete();
        $onlinescrums->features()->createMany([
            [
                'icon' => 'Users',
                'title' => 'Built for Teams',
                'description' => 'A clear, memorable name for products that keep agile teams aligned.',
                'color' => 'text-blue-500',
                'sort_order' => 1,
            ],
            [
                'icon' => 'Zap',
                'title' => 'Category Clarity',
                'description' => 'Instantly communicates standups, sprints, and remote collaboration.',
                'color' => 'text-amber-500',
                'sort_order' => 2,
            ],
            [
                'icon' => 'Globe',
                'title' => 'Global Ready',
                'description' => 'A .com brand that works for SaaS tools selling worldwide.',
                'color' => 'text-green-500',
                'sort_order' => 3,
            ],
        ]);

        $onlinescrums->sellingPoints()->delete();
        $onlinescrums->sellingPoints()->createMany([
            ['text' => 'Exact-match domain for online scrums and remote standups', 'sort_order' => 1],
            ['text' => 'Easy to spell, say, and remember', 'sort_order' => 2],
            ['text' => 'Strong fit for agile tooling and collaboration SaaS', 'sort_order' => 3],
            ['text' => 'Premium .com available for a category-defining brand', 'sort_order' => 4],
        ]);
    }
}
