<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Rate extends Model {
    protected $fillable = ['sport', 'day_type', 'price'];

    public const DEFAULT_RATES = [
        // Padel
        ['sport' => 'padel', 'day_type' => 'weekday_day',   'price' => 250000],
        ['sport' => 'padel', 'day_type' => 'weekday_night', 'price' => 300000],
        ['sport' => 'padel', 'day_type' => 'weekend_day',   'price' => 300000],
        ['sport' => 'padel', 'day_type' => 'weekend_night', 'price' => 350000],

        // Badminton (bulutangkis)
        ['sport' => 'bulutangkis', 'day_type' => 'weekday_day',   'price' => 60000],
        ['sport' => 'bulutangkis', 'day_type' => 'weekday_night', 'price' => 70000],
        ['sport' => 'bulutangkis', 'day_type' => 'weekend_day',   'price' => 70000],
        ['sport' => 'bulutangkis', 'day_type' => 'weekday_night_fallback', 'price' => 70000], // internal fallback not seeded
        ['sport' => 'bulutangkis', 'day_type' => 'weekend_night', 'price' => 80000],

        // Pickleball
        ['sport' => 'pickleball', 'day_type' => 'weekday_day',   'price' => 70000],
        ['sport' => 'pickleball', 'day_type' => 'weekday_night', 'price' => 80000],
        ['sport' => 'pickleball', 'day_type' => 'weekend_day',   'price' => 80000],
        ['sport' => 'pickleball', 'day_type' => 'weekend_night', 'price' => 90000],

        // Gym & Fitness Center
        ['sport' => 'gym', 'day_type' => 'weekday', 'price' => 35000],
        ['sport' => 'gym', 'day_type' => 'weekend', 'price' => 50000],
        ['sport' => 'gym', 'day_type' => 'member',  'price' => 200000],
        ['sport' => 'gym', 'day_type' => 'admin',   'price' => 50000],

        // Biliar, Tenis Meja & Playground
        ['sport' => 'biliar',     'day_type' => 'per_hour',  'price' => 35000],
        ['sport' => 'tenis_meja', 'day_type' => 'per_hour',  'price' => 35000],
        ['sport' => 'playground', 'day_type' => 'unlimited', 'price' => 50000],
        ['sport' => 'playground', 'day_type' => 'boom_car',  'price' => 15000],
    ];

    /**
     * Informasi tarif Biliar, Tenis Meja & Playground
     */
    public static function otherFacilitiesPricingInfo(): array {
        $biliarPrice     = (int)(self::where('sport', 'biliar')->where('day_type', 'per_hour')->value('price') ?? 35000);
        $tenisMejaPrice  = (int)(self::where('sport', 'tenis_meja')->where('day_type', 'per_hour')->value('price') ?? 35000);
        $pgRates         = self::where('sport', 'playground')->pluck('price', 'day_type')->toArray();
        $pgUnlimited     = (int)($pgRates['unlimited'] ?? 50000);
        $pgBoomCar       = (int)($pgRates['boom_car'] ?? 15000);

        return [
            'biliar' => [
                'per_hour'        => $biliarPrice,
                'price_formatted' => 'Rp' . number_format($biliarPrice, 0, ',', '.'),
                'unit'            => '/jam',
            ],
            'tenis_meja' => [
                'per_hour'        => $tenisMejaPrice,
                'price_formatted' => 'Rp' . number_format($tenisMejaPrice, 0, ',', '.'),
                'unit'            => '/jam',
            ],
            'playground' => [
                'unlimited' => [
                    'price'           => $pgUnlimited,
                    'price_formatted' => 'Rp' . number_format($pgUnlimited, 0, ',', '.'),
                    'unit'            => 'Sepuasnya',
                ],
                'boom_car' => [
                    'price'           => $pgBoomCar,
                    'price_formatted' => 'Rp' . number_format($pgBoomCar, 0, ',', '.'),
                    'unit'            => '/3 putaran',
                ],
            ],
        ];
    }

    /**
     * Informasi tarif Gym & Fitness Center (Tiket Harian, Membership & Biaya Administrasi)
     */
    public static function gymPricingInfo(): array {
        $rates = self::where('sport', 'gym')->pluck('price', 'day_type')->toArray();
        return [
            'weekday' => [
                'label'           => 'Senin – Jumat',
                'sublabel'        => 'Tiket Harian (Daily Pass)',
                'price'           => (int)($rates['weekday'] ?? 35000),
                'price_formatted' => 'Rp' . number_format((int)($rates['weekday'] ?? 35000), 0, ',', '.'),
                'unit'            => '/hari',
                'badge'           => '35k',
            ],
            'weekend' => [
                'label'           => 'Sabtu – Minggu',
                'sublabel'        => 'Tiket Harian (Daily Pass)',
                'price'           => (int)($rates['weekend'] ?? 50000),
                'price_formatted' => 'Rp' . number_format((int)($rates['weekend'] ?? 50000), 0, ',', '.'),
                'unit'            => '/hari',
                'badge'           => '50k',
            ],
            'member' => [
                'label'           => 'Member Bulanan',
                'sublabel'        => 'Akses Gym Tanpa Batas',
                'price'           => (int)($rates['member'] ?? 200000),
                'price_formatted' => 'Rp' . number_format((int)($rates['member'] ?? 200000), 0, ',', '.'),
                'unit'            => '/bulan',
                'badge'           => '200k',
            ],
            'admin' => [
                'label'           => 'Biaya Administrasi',
                'sublabel'        => 'Registrasi Member Baru',
                'price'           => (int)($rates['admin'] ?? 50000),
                'price_formatted' => 'Rp' . number_format((int)($rates['admin'] ?? 50000), 0, ',', '.'),
                'unit'            => '(sekali daftar)',
                'badge'           => '50k',
            ],
        ];
    }

    /**
     * Informasi sewa alat olahraga (offline di lokasi) per cabang olahraga
     */
    public static function sportEquipmentInfo(string $sport): array {
        return match (strtolower($sport)) {
            'padel' => [
                ['label' => 'Sewa Raket + Bola Padel', 'price' => 'Rp25.000/raket'],
                ['label' => 'Beli Bola Padel (1 Tube)', 'price' => 'Rp120.000'],
            ],
            'bulutangkis' => [
                ['label' => 'Sewa Raket Badminton', 'price' => 'Rp15.000/jam'],
                ['label' => 'Shuttlecock (Bola Bulu)', 'price' => 'Rp15.000/pcs'],
            ],
            'pickleball' => [
                ['label' => 'Sewa Raket + Bola Pickleball', 'price' => 'Rp15.000/raket'],
            ],
            default => [],
        };
    }

    /**
     * Label kategori hari & sesi jam berdasarkan day_type:
     * - weekday_day   => Senin - Jumat · Pukul 08.00 - 16.00
     * - weekday_night => Senin - Jumat · Pukul 16.00 - 23.00
     * - weekend_day   => Sabtu - Minggu · Pukul 08.00 - 16.00
     * - weekend_night => Sabtu - Minggu · Pukul 16.00 - 23.00
     */
    public function getSessionLabelAttribute(): string {
        if ($this->sport === 'gym') {
            return match ($this->day_type) {
                'weekday' => 'Senin – Jumat (Daily Pass / Harian)',
                'weekend' => 'Sabtu – Minggu (Daily Pass / Harian)',
                'member'  => 'Member Bulanan (Akses Penuh)',
                'admin'   => 'Biaya Administrasi Registrasi',
                default   => $this->day_type,
            };
        }

        if ($this->sport === 'biliar') {
            return match ($this->day_type) {
                'per_hour' => 'Sewa Meja Biliar (Per Jam)',
                default    => $this->day_type,
            };
        }

        if ($this->sport === 'tenis_meja') {
            return match ($this->day_type) {
                'per_hour' => 'Sewa Tenis Meja (Per Jam)',
                default    => $this->day_type,
            };
        }

        if ($this->sport === 'playground') {
            return match ($this->day_type) {
                'unlimited' => 'Bermain Sepuasnya (All Day)',
                'boom_car'  => 'Wahana Boom Car (Per 3 Putaran)',
                default     => $this->day_type,
            };
        }

        return match ($this->day_type) {
            'weekday_day'              => 'Senin – Jumat (08.00 – 16.00)',
            'weekday_night'            => 'Senin – Jumat (16.00 – 23.00)',
            'weekend_day'              => 'Sabtu – Minggu (08.00 – 16.00)',
            'weekend_night'            => 'Sabtu – Minggu (16.00 – 23.00)',
            'weekday'                  => 'Senin – Jumat (08.00 – 16.00)',
            'weekend'                  => 'Sabtu – Minggu (08.00 – 16.00)',
            'per_hour'                 => 'Tarif Sewa Per Jam',
            default                    => $this->day_type,
        };
    }

    /**
     * Tentukan kode day_type berdasarkan objek DateTime:
     * - Senin s/d Jumat (N = 1..5) vs Sabtu s/d Minggu (N = 6..7)
     * - Pukul 08:00 - 16:00 (jam mulai < 16) vs Pukul 16:00 - 23:00 (jam mulai >= 16)
     */
    public static function resolveDayType(\DateTimeInterface $date): string {
        $isWeekend = ((int)$date->format('N')) >= 6;
        $isNight   = ((int)$date->format('H')) >= 16;

        if ($isWeekend) {
            return $isNight ? 'weekend_night' : 'weekend_day';
        }
        return $isNight ? 'weekday_night' : 'weekday_day';
    }

    /**
     * Ambil seluruh tarif untuk suatu cabang olahraga dalam bentuk array terstruktur
     */
    public static function getSportRatesMap(string $sport): array {
        $rows = self::where('sport', $sport)->pluck('price', 'day_type')->toArray();

        $defaults = [
            'padel' => [
                'weekday_day' => 250000, 'weekday_night' => 300000,
                'weekend_day' => 300000, 'weekend_night' => 350000,
            ],
            'bulutangkis' => [
                'weekday_day' => 60000, 'weekday_night' => 70000,
                'weekend_day' => 70000, 'weekend_night' => 80000,
            ],
            'pickleball' => [
                'weekday_day' => 70000, 'weekday_night' => 80000,
                'weekend_day' => 80000, 'weekend_night' => 90000,
            ],
        ][$sport] ?? [
            'weekday_day' => 0, 'weekday_night' => 0,
            'weekend_day' => 0, 'weekend_night' => 0,
        ];

        return [
            'weekday_day'   => (int)($rows['weekday_day'] ?? $rows['weekday'] ?? $defaults['weekday_day']),
            'weekday_night' => (int)($rows['weekday_night'] ?? $rows['weekend'] ?? $defaults['weekday_night']),
            'weekend_day'   => (int)($rows['weekend_day'] ?? $defaults['weekend_day']),
            'weekend_night' => (int)($rows['weekend_night'] ?? $defaults['weekend_night']),
        ];
    }

    /**
     * Harga per jam untuk waktu mulai tertentu ($date).
     */
    public static function getPrice(string $sport, \DateTimeInterface $date): int {
        $map = self::getSportRatesMap($sport);
        $key = self::resolveDayType($date);
        return (int)($map[$key] ?? 0);
    }

    /**
     * Hitung total harga sewa lapangan untuk rentang waktu ($start s/d $end)
     * secara akurat per jam (mendukung booking lintas sesi, mis. 15:00 - 17:00).
     */
    public static function calculateCourtPrice(string $sport, \DateTime $start, \DateTime $end): int {
        $map = self::getSportRatesMap($sport);
        $cursor = clone $start;
        $total = 0;

        while ($cursor < $end) {
            $key = self::resolveDayType($cursor);
            $total += (int)($map[$key] ?? 0);
            $cursor->modify('+1 hour');
        }

        return $total;
    }

    /**
     * Sinkronisasi otomatis seluruh tarif standar ke tabel rates
     */
    public static function syncStandardRates(): void {
        $standard = [
            ['sport' => 'padel',       'day_type' => 'weekday_day',   'price' => 250000],
            ['sport' => 'padel',       'day_type' => 'weekday_night', 'price' => 300000],
            ['sport' => 'padel',       'day_type' => 'weekend_day',   'price' => 300000],
            ['sport' => 'padel',       'day_type' => 'weekend_night', 'price' => 350000],

            ['sport' => 'bulutangkis', 'day_type' => 'weekday_day',   'price' => 60000],
            ['sport' => 'bulutangkis', 'day_type' => 'weekday_night', 'price' => 70000],
            ['sport' => 'bulutangkis', 'day_type' => 'weekend_day',   'price' => 70000],
            ['sport' => 'bulutangkis', 'day_type' => 'weekend_night', 'price' => 80000],

            ['sport' => 'pickleball',  'day_type' => 'weekday_day',   'price' => 70000],
            ['sport' => 'pickleball',  'day_type' => 'weekday_night', 'price' => 80000],
            ['sport' => 'pickleball',  'day_type' => 'weekend_day',   'price' => 80000],
            ['sport' => 'pickleball',  'day_type' => 'weekend_night', 'price' => 90000],

            // Tarif Gym & Kebugaran
            ['sport' => 'gym',         'day_type' => 'weekday',       'price' => 35000],
            ['sport' => 'gym',         'day_type' => 'weekend',       'price' => 50000],
            ['sport' => 'gym',         'day_type' => 'member',        'price' => 200000],
            ['sport' => 'gym',         'day_type' => 'admin',         'price' => 50000],

            // Tarif Biliar, Tenis Meja & Playground
            ['sport' => 'biliar',      'day_type' => 'per_hour',      'price' => 35000],
            ['sport' => 'tenis_meja',  'day_type' => 'per_hour',      'price' => 35000],
            ['sport' => 'playground',  'day_type' => 'unlimited',     'price' => 50000],
            ['sport' => 'playground',  'day_type' => 'boom_car',      'price' => 15000],
        ];

        self::whereIn('day_type', ['weekday', 'weekend'])->where('sport', '!=', 'gym')->delete();

        foreach ($standard as $item) {
            self::updateOrCreate(
                ['sport' => $item['sport'], 'day_type' => $item['day_type']],
                ['price' => $item['price']]
            );
        }
    }
}
