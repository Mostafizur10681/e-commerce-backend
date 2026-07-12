<?php

namespace Database\Seeders;

use App\Models\ContactSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ContactSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define directories
        $sourceFile = 'E:/e-commerce/frontend/src/assets/contact-hero.png';
        $destDir = storage_path('app/public/contact');
        $dbImagePath = 'contact/contact-hero.png';

        // Create destination directory if it doesn't exist
        if (!File::exists($destDir)) {
            File::makeDirectory($destDir, 0755, true);
        }

        // Copy image from frontend if it exists
        if (File::exists($sourceFile)) {
            File::copy($sourceFile, $destDir . '/contact-hero.png');
        }

        // Seed settings
        ContactSetting::updateOrCreate(
            ['id' => 1],
            [
                'phone' => '+880 1700-000000',
                'email' => 'support@freshmart.com',
                'address' => '42 Green Lane, Dhaka 1212, Bangladesh',
                'business_hours_weekday' => 'Mon – Sat: 9:00 AM – 8:00 PM',
                'business_hours_weekend' => 'Sunday: 11:00 AM – 5:00 PM',
                'support_title' => 'Need Help?',
                'support_desc' => 'Our support team is always ready to assist you.',
                'support_phone' => '+8801700000000',
                'support_image' => $dbImagePath,
            ]
        );
    }
}
