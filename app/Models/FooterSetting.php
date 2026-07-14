<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FooterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'store_icon',
        'store_description',
        'copyright_text',
        'social_links',
        'quick_links',
        'service_links',
        'contact_address',
        'contact_phone',
        'contact_email',
        'contact_hours',
    ];

    protected $casts = [
        'social_links'  => 'array',
        'quick_links'   => 'array',
        'service_links' => 'array',
    ];

    /**
     * Get or create the single settings row with sensible defaults.
     */
    public static function getOrCreate(): self
    {
        $settings = self::first();

        if (!$settings) {
            $settings = self::create([
                'store_name'        => 'FreshMart',
                'store_icon'        => '🥬',
                'store_description' => 'Your trusted online grocery store. We deliver the freshest produce, dairy, and everyday essentials right to your doorstep.',
                'copyright_text'    => 'FreshMart. All rights reserved.',
                'social_links'      => [
                    ['name' => 'Facebook',  'icon' => 'facebook',  'url' => '#'],
                    ['name' => 'Twitter',   'icon' => 'twitter',   'url' => '#'],
                    ['name' => 'Instagram', 'icon' => 'instagram', 'url' => '#'],
                    ['name' => 'YouTube',   'icon' => 'youtube',   'url' => '#'],
                ],
                'quick_links' => [
                    ['label' => 'Home',     'path' => '/'],
                    ['label' => 'Shop',     'path' => '/shop'],
                    ['label' => 'About Us', 'path' => '/about'],
                    ['label' => 'Contact',  'path' => '/contact'],
                ],
                'service_links' => [
                    ['label' => 'FAQ',                'path' => '/faq'],
                    ['label' => 'Shipping Info',      'path' => '/shipping-info'],
                    ['label' => 'Returns & Refunds',  'path' => '/returns-refunds'],
                    ['label' => 'Order Tracking',     'path' => '/track-order'],
                    ['label' => 'Payment Methods',    'path' => '/payment-methods'],
                ],
                'contact_address' => '123 Green Street, Dhaka 1205, Bangladesh',
                'contact_phone'   => '+880 1700-000000',
                'contact_email'   => 'info@freshmart.com',
                'contact_hours'   => 'Mon-Sat: 8AM - 10PM',
            ]);
        }

        return $settings;
    }
}
