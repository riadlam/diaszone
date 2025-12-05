<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiamondPack;

class FreeFireDiamondPackSeeder extends Seeder
{
    /**
     * Pricing Formula:
     * 1. price_special (from JSON) ÷ 16600 = USD cost
     * 2. USD cost × 250 = DZD base price
     * 3. DZD base price + 100 DZD margin = Final DZD price
     * 4. USD cost + $0.50 margin = USD selling price
     */
    
    const IDR_TO_USD = 16600;      // 1 USD = 16600 IDR (price_special currency)
    const USD_TO_DZD = 250;        // 1 USD = 250 DZD
    const USD_MARGIN = 0.50;       // $0.50 USD profit margin
    const DZD_MARGIN = 100;        // 100 DZD profit margin

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // All Free Fire packs from VIP Reseller API
        $packs = [
            ['code' => 'FF5-S13', 'name' => '5 Diamonds', 'diamonds' => 5, 'price_special' => 759],
            ['code' => 'FF10-S13', 'name' => '10 Diamonds', 'diamonds' => 10, 'price_special' => 1515],
            ['code' => 'FF12-S13', 'name' => '12 Diamonds', 'diamonds' => 12, 'price_special' => 1751],
            ['code' => 'FF15-S13', 'name' => '15 Diamonds', 'diamonds' => 15, 'price_special' => 2271],
            ['code' => 'FF20-S13', 'name' => '20 Diamonds', 'diamonds' => 20, 'price_special' => 3026],
            ['code' => 'FF25-S13', 'name' => '25 Diamonds', 'diamonds' => 25, 'price_special' => 3781],
            ['code' => 'FF30-S13', 'name' => '30 Diamonds', 'diamonds' => 30, 'price_special' => 4573],
            ['code' => 'FF40-S13', 'name' => '40 Diamonds', 'diamonds' => 40, 'price_special' => 5513],
            ['code' => 'FF50-S13', 'name' => '50 Diamonds', 'diamonds' => 50, 'price_special' => 6095],
            ['code' => 'FF55-S13', 'name' => '55 Diamonds', 'diamonds' => 55, 'price_special' => 6856],
            ['code' => 'FF60-S13', 'name' => '60 Diamonds', 'diamonds' => 60, 'price_special' => 7557],
            ['code' => 'FF70-S13', 'name' => '70 Diamonds', 'diamonds' => 70, 'price_special' => 7968],
            ['code' => 'FF75-S13', 'name' => '75 Diamonds', 'diamonds' => 75, 'price_special' => 8711],
            ['code' => 'FF80-S13', 'name' => '80 Diamonds', 'diamonds' => 80, 'price_special' => 9461],
            ['code' => 'FF90-S13', 'name' => '90 Diamonds', 'diamonds' => 90, 'price_special' => 10946],
            ['code' => 'FF100-S13', 'name' => '100 Diamonds', 'diamonds' => 100, 'price_special' => 12205],
            ['code' => 'FF120-S13', 'name' => '120 Diamonds', 'diamonds' => 120, 'price_special' => 13625],
            ['code' => 'FF130-S13', 'name' => '130 Diamonds', 'diamonds' => 130, 'price_special' => 15100],
            ['code' => 'FF140-S13', 'name' => '140 Diamonds', 'diamonds' => 140, 'price_special' => 16778],
            ['code' => 'FF145-S13', 'name' => '145 Diamonds', 'diamonds' => 145, 'price_special' => 17519],
            ['code' => 'FF150-S13', 'name' => '150 Diamonds', 'diamonds' => 150, 'price_special' => 17196],
            ['code' => 'FF160-S13', 'name' => '160 Diamonds', 'diamonds' => 160, 'price_special' => 18906],
            ['code' => 'FF170-S13', 'name' => '170 Diamonds', 'diamonds' => 170, 'price_special' => 20564],
            ['code' => 'FF180-S13', 'name' => '180 Diamonds', 'diamonds' => 180, 'price_special' => 22086],
            ['code' => 'FF190-S13', 'name' => '190 Diamonds', 'diamonds' => 190, 'price_special' => 21746],
            ['code' => 'FF200-S13', 'name' => '200 Diamonds', 'diamonds' => 200, 'price_special' => 23231],
            ['code' => 'FFMM-S13', 'name' => 'Weekly Membership', 'diamonds' => 0, 'price_special' => 25060],
            ['code' => 'FF210-S13', 'name' => '210 Diamonds', 'diamonds' => 210, 'price_special' => 25162],
            ['code' => 'FF250-S13', 'name' => '250 Diamonds', 'diamonds' => 250, 'price_special' => 28750],
            ['code' => 'FF260-S13', 'name' => '260 Diamonds', 'diamonds' => 260, 'price_special' => 29802],
            ['code' => 'FF280-S13', 'name' => '280 Diamonds', 'diamonds' => 280, 'price_special' => 33561],
            ['code' => 'FF300-S13', 'name' => '300 Diamonds', 'diamonds' => 300, 'price_special' => 34409],
            ['code' => 'FF355-S13', 'name' => '355 Diamonds', 'diamonds' => 355, 'price_special' => 41945],
            ['code' => 'FF360-S13', 'name' => '360 Diamonds', 'diamonds' => 360, 'price_special' => 42640],
            ['code' => 'FF375-S13', 'name' => '375 Diamonds', 'diamonds' => 375, 'price_special' => 42788],
            ['code' => 'FF400-S13', 'name' => '400 Diamonds', 'diamonds' => 400, 'price_special' => 45468],
            ['code' => 'FF405-S13', 'name' => '405 Diamonds', 'diamonds' => 405, 'price_special' => 45468],
            ['code' => 'FF420-S13', 'name' => '420 Diamonds', 'diamonds' => 420, 'price_special' => 47913],
            ['code' => 'FF425-S13', 'name' => '425 Diamonds', 'diamonds' => 425, 'price_special' => 50329],
            ['code' => 'FF475-S13', 'name' => '475 Diamonds', 'diamonds' => 475, 'price_special' => 53428],
            ['code' => 'FF495-S13', 'name' => '495 Diamonds', 'diamonds' => 495, 'price_special' => 58994],
            ['code' => 'FF500-S13', 'name' => '500 Diamonds', 'diamonds' => 500, 'price_special' => 59388],
            ['code' => 'FF510-S13', 'name' => '510 Diamonds', 'diamonds' => 510, 'price_special' => 57743],
            ['code' => 'FF515-S13', 'name' => '515 Diamonds', 'diamonds' => 515, 'price_special' => 58306],
            ['code' => 'FF520-S13', 'name' => '520 Diamonds', 'diamonds' => 520, 'price_special' => 59256],
            ['code' => 'FF545-S13', 'name' => '545 Diamonds', 'diamonds' => 545, 'price_special' => 61388],
            ['code' => 'FF565-S13', 'name' => '565 Diamonds', 'diamonds' => 565, 'price_special' => 67097],
            ['code' => 'FF600-S13', 'name' => '600 Diamonds', 'diamonds' => 600, 'price_special' => 67803],
            ['code' => 'FF635-S13', 'name' => '635 Diamonds', 'diamonds' => 635, 'price_special' => 75842],
            ['code' => 'FF645-S13', 'name' => '645 Diamonds', 'diamonds' => 645, 'price_special' => 76896],
            ['code' => 'FF655-S13', 'name' => '655 Diamonds', 'diamonds' => 655, 'price_special' => 74786],
            ['code' => 'FFMB-S13', 'name' => 'Monthly Membership', 'diamonds' => 0, 'price_special' => 75129],
            ['code' => 'FF720-S13', 'name' => '720 Diamonds', 'diamonds' => 720, 'price_special' => 79678],
            ['code' => 'FF740-S13', 'name' => '740 Diamonds', 'diamonds' => 740, 'price_special' => 89598],
            ['code' => 'FF770-S13', 'name' => '770 Diamonds', 'diamonds' => 770, 'price_special' => 86047],
            ['code' => 'FF790-S13', 'name' => '790 Diamonds', 'diamonds' => 790, 'price_special' => 88508],
            ['code' => 'FF800-S13', 'name' => '800 Diamonds', 'diamonds' => 800, 'price_special' => 89954],
            ['code' => 'FF860-S13', 'name' => '860 Diamonds', 'diamonds' => 860, 'price_special' => 94471],
            ['code' => 'FF925-S13', 'name' => '925 Diamonds', 'diamonds' => 925, 'price_special' => 103860],
            ['code' => 'FF930-S13', 'name' => '930 Diamonds', 'diamonds' => 930, 'price_special' => 109539],
            ['code' => 'FF1000-S13', 'name' => '1000 Diamonds', 'diamonds' => 1000, 'price_special' => 112132],
            ['code' => 'FF1050-S13', 'name' => '1050 Diamonds', 'diamonds' => 1050, 'price_special' => 117512],
            ['code' => 'FF1075-S13', 'name' => '1075 Diamonds', 'diamonds' => 1075, 'price_special' => 120176],
            ['code' => 'FF1080-S13', 'name' => '1080 Diamonds', 'diamonds' => 1080, 'price_special' => 120684],
            ['code' => 'FF1200-S13', 'name' => '1200 Diamonds', 'diamonds' => 1200, 'price_special' => 134310],
            ['code' => 'FF1215-S13', 'name' => '1215 Diamonds', 'diamonds' => 1215, 'price_special' => 135782],
            ['code' => 'FF1300-S13', 'name' => '1300 Diamonds', 'diamonds' => 1300, 'price_special' => 146033],
            ['code' => 'FF1440-S13', 'name' => '1440 Diamonds', 'diamonds' => 1440, 'price_special' => 168506],
            ['code' => 'FF1450-S13', 'name' => '1450 Diamonds', 'diamonds' => 1450, 'price_special' => 161131],
            ['code' => 'FF1490-S13', 'name' => '1490 Diamonds', 'diamonds' => 1490, 'price_special' => 165521],
            ['code' => 'FF1510-S13', 'name' => '1510 Diamonds', 'diamonds' => 1510, 'price_special' => 167703],
            ['code' => 'FF1580-S13', 'name' => '1580 Diamonds', 'diamonds' => 1580, 'price_special' => 176001],
            ['code' => 'FF1800-S13', 'name' => '1800 Diamonds', 'diamonds' => 1800, 'price_special' => 206285],
            ['code' => 'FF1875-S13', 'name' => '1875 Diamonds', 'diamonds' => 1875, 'price_special' => 215254],
            ['code' => 'FF1975-S13', 'name' => '1975 Diamonds', 'diamonds' => 1975, 'price_special' => 220610],
            ['code' => 'FF2000-S13', 'name' => '2000 Diamonds', 'diamonds' => 2000, 'price_special' => 234775],
            ['code' => 'FF2100-S13', 'name' => '2100 Diamonds', 'diamonds' => 2100, 'price_special' => 242161],
            ['code' => 'FF2140-S13', 'name' => '2140 Diamonds', 'diamonds' => 2140, 'price_special' => 239388],
            ['code' => 'FF2190-S13', 'name' => '2190 Diamonds', 'diamonds' => 2190, 'price_special' => 242203],
            ['code' => 'FF2200-S13', 'name' => '2200 Diamonds', 'diamonds' => 2200, 'price_special' => 257313],
            ['code' => 'FF2210-S13', 'name' => '2210 Diamonds', 'diamonds' => 2210, 'price_special' => 252624],
            ['code' => 'FF2225-S13', 'name' => '2225 Diamonds', 'diamonds' => 2225, 'price_special' => 245587],
            ['code' => 'FF2280-S13', 'name' => '2280 Diamonds', 'diamonds' => 2280, 'price_special' => 253268],
            ['code' => 'FF2355-S13', 'name' => '2355 Diamonds', 'diamonds' => 2355, 'price_special' => 269814],
            ['code' => 'FF2400-S13', 'name' => '2400 Diamonds', 'diamonds' => 2400, 'price_special' => 275047],
            ['code' => 'FF2575-S13', 'name' => '2575 Diamonds', 'diamonds' => 2575, 'price_special' => 295974],
            ['code' => 'FF2720-S13', 'name' => '2720 Diamonds', 'diamonds' => 2720, 'price_special' => 312417],
            ['code' => 'FF2750-S13', 'name' => '2750 Diamonds', 'diamonds' => 2750, 'price_special' => 304818],
            ['code' => 'FF3000-S13', 'name' => '3000 Diamonds', 'diamonds' => 3000, 'price_special' => 349424],
            ['code' => 'FF3310-S13', 'name' => '3310 Diamonds', 'diamonds' => 3310, 'price_special' => 378936],
            ['code' => 'FF3640-S13', 'name' => '3640 Diamonds', 'diamonds' => 3640, 'price_special' => 398388],
            ['code' => 'FF3675-S13', 'name' => '3675 Diamonds', 'diamonds' => 3675, 'price_special' => 420044],
            ['code' => 'FF3800-S13', 'name' => '3800 Diamonds', 'diamonds' => 3800, 'price_special' => 435740],
            ['code' => 'FF4000-S13', 'name' => '4000 Diamonds', 'diamonds' => 4000, 'price_special' => 458162],
            ['code' => 'FF4050-S13', 'name' => '4050 Diamonds', 'diamonds' => 4050, 'price_special' => 464141],
            ['code' => 'FF4340-S13', 'name' => '4340 Diamonds', 'diamonds' => 4340, 'price_special' => 496279],
            ['code' => 'FF4450-S13', 'name' => '4450 Diamonds', 'diamonds' => 4450, 'price_special' => 509733],
            ['code' => 'FF4720-S13', 'name' => '4720 Diamonds', 'diamonds' => 4720, 'price_special' => 540377],
            ['code' => 'FF4800-S13', 'name' => '4800 Diamonds', 'diamonds' => 4800, 'price_special' => 559529],
            ['code' => 'FF4850-S13', 'name' => '4850 Diamonds', 'diamonds' => 4850, 'price_special' => 555325],
            ['code' => 'FF5500-S13', 'name' => '5500 Diamonds', 'diamonds' => 5500, 'price_special' => 629318],
            ['code' => 'FF5600-S13', 'name' => '5600 Diamonds', 'diamonds' => 5600, 'price_special' => 608594],
            ['code' => 'FF6000-S13', 'name' => '6000 Diamonds', 'diamonds' => 6000, 'price_special' => 686122],
            ['code' => 'FF6480-S13', 'name' => '6480 Diamonds', 'diamonds' => 6480, 'price_special' => 739935],
            ['code' => 'FF6550-S13', 'name' => '6550 Diamonds', 'diamonds' => 6550, 'price_special' => 748157],
            ['code' => 'FF6900-S13', 'name' => '6900 Diamonds', 'diamonds' => 6900, 'price_special' => 789264],
            ['code' => 'FF7290-S13', 'name' => '7290 Diamonds', 'diamonds' => 7290, 'price_special' => 798286],
            ['code' => 'FF7310-S13', 'name' => '7310 Diamonds', 'diamonds' => 7310, 'price_special' => 825139],
            ['code' => 'FF7340-S13', 'name' => '7340 Diamonds', 'diamonds' => 7340, 'price_special' => 828129],
            ['code' => 'FF7360-S13', 'name' => '7360 Diamonds', 'diamonds' => 7360, 'price_special' => 830372],
            ['code' => 'FF7430-S13', 'name' => '7430 Diamonds', 'diamonds' => 7430, 'price_special' => 838593],
            ['code' => 'FF7645-S13', 'name' => '7645 Diamonds', 'diamonds' => 7645, 'price_special' => 863258],
            ['code' => 'FF7650-S13', 'name' => '7650 Diamonds', 'diamonds' => 7650, 'price_special' => 864005],
            ['code' => 'FF8010-S13', 'name' => '8010 Diamonds', 'diamonds' => 8010, 'price_special' => 904365],
            ['code' => 'FF9290-S13', 'name' => '9290 Diamonds', 'diamonds' => 9290, 'price_special' => 1023602],
            ['code' => 'FF9800-S13', 'name' => '9800 Diamonds', 'diamonds' => 9800, 'price_special' => 1109903],
            ['code' => 'FF14580-S13', 'name' => '14580 Diamonds', 'diamonds' => 14580, 'price_special' => 1624000],
            ['code' => 'FF36500-S13', 'name' => '36500 Diamonds', 'diamonds' => 36500, 'price_special' => 4105675],
            ['code' => 'FF37050-S13', 'name' => '37050 Diamonds', 'diamonds' => 37050, 'price_special' => 4252353],
            ['code' => 'FF73100-S13', 'name' => '73100 Diamonds', 'diamonds' => 73100, 'price_special' => 8373760],
        ];

        $sortOrder = 1;
        foreach ($packs as $pack) {
            // Calculate prices using the formula
            $usdCost = $pack['price_special'] / self::IDR_TO_USD;
            $dzdBase = $usdCost * self::USD_TO_DZD;
            $dzdFinal = ceil($dzdBase + self::DZD_MARGIN); // Round up
            $usdFinal = round($usdCost + self::USD_MARGIN, 2);

            DiamondPack::updateOrCreate(
                [
                    'game_type' => 'freefire',
                    'code' => $pack['code'],
                ],
                [
                    'name' => $pack['name'],
                    'diamonds' => $pack['diamonds'],
                    'bonus_diamonds' => 0,
                    'price' => $usdFinal,           // USD price with margin (legacy field)
                    'price_usd' => $usdFinal,       // USD selling price
                    'price_dzd' => $dzdFinal,       // DZD selling price
                    'discount_percentage' => 0,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]
            );
        }

        $this->command->info('Free Fire diamond packs seeded successfully! Total: ' . count($packs) . ' packs');
    }
}
