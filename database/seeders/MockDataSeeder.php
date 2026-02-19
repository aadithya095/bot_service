<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Meetings
        \App\Models\Meeting::insert([
            [
                'title' => 'Sales Meeting',
                'meeting_url' => 'https://meet.example.com/sales',
                'meeting_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Tech Standup',
                'meeting_url' => 'https://meet.example.com/tech',
                'meeting_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Invoices
        \App\Models\Invoice::insert([
            [
                'invoice_number' => 'INV-1001',
                'pdf_url' => 'https://example.com/invoices/inv-1001.pdf',
                'amount' => 1500,
                'invoice_date' => now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_number' => 'INV-1002',
                'pdf_url' => 'https://example.com/invoices/inv-1002.pdf',
                'amount' => 2500,
                'invoice_date' => now()->subDays(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Forums
        $forum = \App\Models\Forum::create(['name' => 'General Discussion']);

        \App\Models\ForumPost::insert([
            [
                'forum_id' => $forum->id,
                'title' => 'Welcome Post',
                'content' => 'Welcome to the forum!',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'forum_id' => $forum->id,
                'title' => 'FAQ',
                'content' => 'Here are some common questions.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
