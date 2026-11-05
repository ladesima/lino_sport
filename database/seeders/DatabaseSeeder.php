<?php
namespace Database\Seeders;
use App\Models\Court;
use App\Models\Rate;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Slider;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@lino.com',
            'password' => bcrypt('admin123'),
            'phone' => '628123456789',
            'is_admin' => true,
            'role' => 'super_admin',
        ]);

        // 1 Padel, 2 Bulutangkis, 2 Pickleball
        $courts = [
            ['name' => 'Padel Court', 'sport' => 'padel', 'image' => null],
            ['name' => 'Badminton Court 1', 'sport' => 'bulutangkis', 'image' => null],
            ['name' => 'Badminton Court 2', 'sport' => 'bulutangkis', 'image' => null],
            ['name' => 'Pickleball Court 1', 'sport' => 'pickleball', 'image' => null],
            ['name' => 'Pickleball Court 2', 'sport' => 'pickleball', 'image' => null],
        ];
        foreach ($courts as $c) Court::create($c);

        Rate::syncStandardRates();

        $equipment = [
            ['name' => 'Sewa Raket Padel (/raket)', 'price' => 25000],
            ['name' => 'Sewa Bola Padel 1 Tube (3 bola)', 'price' => 120000],
            ['name' => 'Sewa Bulu Badminton (/pcs)', 'price' => 15000],
            ['name' => 'Sewa Raket + Bola Pickleball (/raket)', 'price' => 15000],
        ];
        foreach ($equipment as $e) Equipment::create($e);

        // Seed Default Sliders
        $sliders = [
            [
                'title' => 'Main Padel Makin Seru & Berkelas',
                'subtitle' => 'Karpet standar turnamen, kaca panorama bening, dan pencahayaan LED ultra-bright.',
                'badge' => 'Lapangan Padel Eksklusif',
                'image' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1600&q=80',
                'button_text' => 'Booking Lapangan Padel',
                'button_url' => '/booking/create',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Karpet Vinyl Badminton Premium',
                'subtitle' => 'Lantai empuk mengurangi resiko cedera, sirkulasi udara adem, siap untuk sparing dan turnamen.',
                'badge' => '2 Lapangan Bulutangkis',
                'image' => 'https://images.unsplash.com/photo-1626224583764-f87db24ac4ea?auto=format&fit=crop&w=1600&q=80',
                'button_text' => 'Booking Badminton',
                'button_url' => '/booking/create',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Olahraga Tren Terpopuler',
                'subtitle' => 'Ajak teman & komunitas Anda menikmati olahraga seru, sehat, dan menyenangkan!',
                'badge' => '2 Lapangan Pickleball',
                'image' => 'https://images.unsplash.com/photo-1599586120429-48281b6f0ece?auto=format&fit=crop&w=1600&q=80',
                'button_text' => 'Booking Pickleball',
                'button_url' => '/booking/create',
                'order' => 3,
                'is_active' => true,
            ],
        ];
        foreach ($sliders as $s) Slider::create($s);
    }
}
