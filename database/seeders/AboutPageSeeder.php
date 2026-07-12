<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publicAboutPath = storage_path('app/public/about');
        if (!file_exists($publicAboutPath)) {
            mkdir($publicAboutPath, 0755, true);
        }

        // Copy from frontend src/assets if possible
        $frontendAssetsPath = 'E:/e-commerce/frontend/src/assets';
        $filesToCopy = [
            'about-story.png' => 'about-story.png',
            'team-ceo.png' => 'team-ceo.png',
            'team-founder.png' => 'team-founder.png',
            'team-support.png' => 'team-support.png',
        ];

        foreach ($filesToCopy as $src => $dest) {
            $srcPath = $frontendAssetsPath . '/' . $src;
            $destPath = $publicAboutPath . '/' . $dest;
            if (file_exists($srcPath)) {
                copy($srcPath, $destPath);
            }
        }

        $storyImage = file_exists($publicAboutPath . '/about-story.png')
            ? url('storage/about/about-story.png')
            : 'https://placehold.co/800x600?text=Our+Story';

        $team = [
            [
                'name' => 'Rafiul Islam',
                'role' => 'CEO & Founder',
                'bio' => 'Visionary leader with 10+ years in eCommerce and supply chain management.',
                'image' => file_exists($publicAboutPath . '/team-ceo.png')
                    ? url('storage/about/team-ceo.png')
                    : 'https://placehold.co/150?text=Rafiul+Islam',
            ],
            [
                'name' => 'Nazmun Nahar',
                'role' => 'Co-Founder & COO',
                'bio' => 'Operations expert passionate about logistics, sustainability and customer experience.',
                'image' => file_exists($publicAboutPath . '/team-founder.png')
                    ? url('storage/about/team-founder.png')
                    : 'https://placehold.co/150?text=Nazmun+Nahar',
            ],
            [
                'name' => 'Tanvir Ahmed',
                'role' => 'Head of Support',
                'bio' => 'Dedicated to making every customer interaction seamless and satisfying.',
                'image' => file_exists($publicAboutPath . '/team-support.png')
                    ? url('storage/about/team-support.png')
                    : 'https://placehold.co/150?text=Tanvir+Ahmed',
            ],
        ];

        AboutPage::updateOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'About Us',
                'hero_subtitle' => 'We are committed to providing the best online shopping experience with quality products and fast delivery — right to your doorstep.',
                'hero_badge' => 'Who We Are',
                'story_title' => 'From a Small Idea to a Trusted Platform',
                'story_badge' => 'Our Story',
                'story_description_1' => 'FreshMart started with a simple idea: everyone deserves access to fresh, healthy, and high-quality food without the hassle of navigating crowded supermarkets. What began as a small local delivery service has grown into a trusted online grocery destination serving thousands of happy customers.',
                'story_description_2' => 'We partner directly with local farmers and trusted suppliers to bring you the best seasonal produce, dairy, bakery items, and pantry staples. Our commitment is to quality, sustainability, and exceptional customer service.',
                'story_since' => '2023',
                'story_points' => [
                    'Partnered with 200+ local farmers & suppliers',
                    'Delivering to 50+ cities across Bangladesh',
                    'Over 10,000 happy customers and counting',
                    'Same-day and next-day delivery available'
                ],
                'story_image' => $storyImage,
                'mission_title' => 'Our Mission',
                'mission_description' => 'To deliver high-quality products at affordable prices with excellent customer service, making online grocery shopping simple, reliable, and enjoyable for every household.',
                'vision_title' => 'Our Vision',
                'vision_description' => 'To become one of the most trusted online shopping platforms in Bangladesh, empowering local farmers and delivering happiness to every home through technology and innovation.',
                'why_choose_badge' => 'Our Advantages',
                'why_choose_title' => 'Why Choose FreshMart?',
                'why_choose_subtitle' => 'We go the extra mile so you get the very best, every single time.',
                'features' => [
                    [
                        'icon' => '🚚',
                        'title' => 'Fast Delivery',
                        'desc' => 'Get your groceries delivered to your door in as little as 2 hours.',
                        'bgClass' => 'bg-blue-50 dark:bg-blue-900/30'
                    ],
                    [
                        'icon' => '🌱',
                        'title' => 'Quality Products',
                        'desc' => 'We handpick every item to ensure you receive only the freshest products.',
                        'bgClass' => 'bg-green-50 dark:bg-green-900/30'
                    ],
                    [
                        'icon' => '🔒',
                        'title' => 'Secure Payment',
                        'desc' => '100% secure checkout with multiple trusted payment methods.',
                        'bgClass' => 'bg-amber-50 dark:bg-amber-900/30'
                    ],
                    [
                        'icon' => '💬',
                        'title' => '24/7 Support',
                        'desc' => 'Our friendly support team is always ready to help you anytime.',
                        'bgClass' => 'bg-rose-50 dark:bg-rose-900/30'
                    ]
                ],
                'stats' => [
                    ['value' => '12K+', 'label' => 'Happy Customers'],
                    ['value' => '50K+', 'label' => 'Products Sold'],
                    ['value' => '98%', 'label' => 'Satisfaction Rate'],
                    ['value' => '200+', 'label' => 'Local Suppliers']
                ],
                'team_badge' => 'The Team',
                'team_title' => 'Meet Our Team',
                'team_subtitle' => 'Passionate people working every day to bring freshness to your door.',
                'team' => $team
            ]
        );
    }
}
