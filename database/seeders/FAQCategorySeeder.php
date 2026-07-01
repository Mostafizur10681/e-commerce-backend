<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FAQCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'General',
                'description' => 'General frequently asked questions and guides.',
                'status' => true,
            ],
            [
                'name' => 'Payments',
                'description' => 'FAQs related to payment methods, card security, and billing.',
                'status' => true,
            ],
            [
                'name' => 'Orders & Shipping',
                'description' => 'Questions on tracking orders, shipment methods, and delivery times.',
                'status' => true,
            ],
            [
                'name' => 'Returns & Refunds',
                'description' => 'Policies and steps on returning products and processing refunds.',
                'status' => true,
            ],
        ];

        foreach ($categories as $cat) {
            \App\Models\FAQCategory::create($cat);
        }
    }
}
