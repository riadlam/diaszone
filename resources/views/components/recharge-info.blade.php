<div class="bg-white py-12">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 {{ isset($gameType) ? 'lg:grid-cols-2' : 'lg:grid-cols-1' }} gap-8">
            @if(isset($gameType))
            <!-- Left Column: Recharge Information (Only on Game Pages) -->
            <div class="space-y-8">
                @if($gameType === 'bloodstrike')
                    <!-- Blood Strike Specific Content -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">About Blood Strike</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Blood Strike is a fast-paced FPS Battle Royale mobile game optimized for low-end devices and limited storage. Experience 100-player Battle Royale with fully customizable weapons!
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            Choose from a wide range of Operators, each with a unique skillset and specialty — Parkour across the battlefield by parachuting, gliding, free running, and zip-lining — Turn the tide of battle with unlimited respawns.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            Keep on running and gunning! Do you have what it takes to be the last one standing?
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Where to top up Blood Strike Golds?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Want to top up Blood Strike Golds easily? Get it at DiasZone - trusted by gamers in Global! We provide many payment options like Visa/Master Credit Card, Online Banking, 7-11, Boost, Bank ATM Transfer, bank cash deposit machine, Paysafecard, Razer Gold and many more. Enjoy fast delivery, secure transactions, and the best price for Blood Strike Golds Top up.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            Top up now at DiasZone and make your Blood Strike experience even better!
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Why Choose DiasZone to Top Up Blood Strike Golds?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-2">
                            Our dedicated customer support team is ready to assist you anytime. Reach out to us!
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li><strong>Fast and Hassle-Free:</strong> Get your Blood Strike Golds on DiasZone in quick and easy.</li>
                            <li><strong>{{ __('recharge.payment_options_title') }}:</strong> {{ __('recharge.secure_payment_methods') }}.</li>
                            <li><strong>Instant and Secure Delivery:</strong> Receive your purchase securely and instantly.</li>
                            <li><strong>Exciting Offers and Promotions:</strong> Take advantage of incredible deals, giveaways, and exclusive offers only on DiasZone.</li>
                        </ul>
                        <p class="text-sm text-gray-700 leading-relaxed mt-4">
                            DiasZone is a trusted global platform offering secure, fast, and professional top-up services with exceptional customer support. Beyond Blood Strike Golds, DiasZone also supports a wide range of popular games like Zenless Zone Zero, Genshin Impact, and Whiteout Survival - making it the ultimate destination for all your gaming recharge needs.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Top Up Blood Strike Golds with DiasZone</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Get Blood Strike Golds top up and offers when you sign in to your DiasZone account. If you're new, sign up with DiasZone today! Our 24/7 support team is here to assist you anytime. Please don't hesitate to reach out for help via our 'Contact Us' page.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            Browse DiasZone website to find what you need, or check out more gaming news, exclusive offers, and updates.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Guide</h3>
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">How to top up Blood Strike Golds?</h4>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li>Select the Golds denomination.</li>
                            <li>Enter your User ID.</li>
                            <li>{{ __('recharge.checkout_select_method') }}.</li>
                            <li>Once payment made, Blood Strike Golds will credited to your account shortly.</li>
                        </ol>
                        <h4 class="text-lg font-semibold text-gray-800 mt-4 mb-2">How to find Blood Strike User ID?</h4>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li>Use your account to login the game.</li>
                            <li>Tap the "Avatar" on the Upper Left Corner.</li>
                            <li>Blood Strike User ID will be displayed.</li>
                        </ol>
                    </div>
                @elseif(isset($gameType) && $gameType === 'honorofkings')
                    <!-- Honor of Kings Specific Content -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">About Honor of Kings</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Honor of Kings is the world's most-played mobile MOBA that offers the ultimate competitive experience on mobile. Become immersed in the battlefield as you squad up with your friends, choose from unique heroes with amazing skills, and enjoy the extreme fun of fierce teamfights. In each battle, a team of five players advance along three lanes, with the goal of taking down nine towers, and ultimately destroy the enemy's crystal to claim victory.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">What is the release date of Honor of Kings?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Honor of Kings was initially released in China on November 26, 2015. The game's global version has been announced and is expected to have a staggered release across different regions.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">How can I download Honor of Kings?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-2">
                            You can download Honor of Kings from the following platforms:
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 ml-4">
                            <li><strong>iOS:</strong> Visit the App Store and search for "Honor of Kings."</li>
                            <li><strong>Android:</strong> Visit the Google Play Store or download the Honor of Kings APK from the official website.</li>
                            <li><strong>Official Website:</strong> Follow the download instructions provided on the official Honor of Kings website.</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Who are the heroes in Honor of Kings?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-2">
                            Honor of Kings features a wide variety of heroes, each with unique abilities and roles. Some of the popular heroes include:
                        </p>
                        <ul class="list-disc list-inside space-y-1 text-sm text-gray-700 ml-4">
                            <li><strong>Zhao Yun:</strong> A versatile warrior with strong offensive and defensive capabilities.</li>
                            <li><strong>Diao Chan:</strong> A powerful mage with control abilities.</li>
                            <li><strong>Arthur:</strong> A tank hero known for his resilience and crowd control skills.</li>
                            <li><strong>Sun Shangxiang:</strong> A marksman with high damage output and mobility.</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Where to top up Honor of Kings Tokens & Package?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Want to top up Honor of Kings Tokens & Package easily? Get it at DiasZone - trusted by gamers in Global! We provide many payment options like Visa/Master Credit Card, Online Banking, 7-11, Boost, Bank ATM Transfer, bank cash deposit machine, Paysafecard, Razer Gold and many more. Enjoy fast delivery, secure transactions, and the best price for Honor of Kings Tokens & Package Top up.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            Top up now at DiasZone and make your Honor of Kings experience even better!
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Why Choose DiasZone for Honor of Kings Tokens & Package Top Up?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-2">
                            Our dedicated customer support team is ready to assist you anytime. Reach out to us!
                        </p>
                        <ul class="list-disc list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li><strong>Fast and Hassle-Free:</strong> Get your Honor of Kings Tokens & Package top up on DiasZone in quick and easy.</li>
                            <li><strong>Flexible Payment Options:</strong> Pay using the most popular payment methods worldwide.</li>
                            <li><strong>Instant and Secure Delivery:</strong> Receive your purchase securely and instantly.</li>
                            <li><strong>Exciting Offers and Promotions:</strong> Take advantage of incredible deals, giveaways, and exclusive offers only on DiasZone.</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Top Up Honor of Kings Tokens & Package with DiasZone</h3>
                        <p class="text-sm text-gray-700 leading-relaxed">
                            Get Honor of Kings top up today by sign in to your DiasZone account. If you're new, sign up with DiasZone today! Our 24/7 support team is here to assist you anytime. Please don't hesitate to reach out for help via our 'Contact Us' page.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            Browse DiasZone website for more our mobile game category products to find what you need. Top up Honor of Kings today with DiasZone for instant delivery and global trust.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mt-2">
                            The final bonus you receive for recharging in Honor of Kings depends on the official rules.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Guide</h3>
                        <h4 class="text-lg font-semibold text-gray-800 mb-2">How to top up Honor Of Kings (HOK) Tokens?</h4>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li>Select the Honor of Kings denomination.</li>
                            <li>Enter your Player ID.</li>
                            <li>Check out and select your payment method.</li>
                            <li>Once payment is made, the Honor of Kings Tokens you purchased will be credited to your account shortly.</li>
                        </ol>
                        <h4 class="text-lg font-semibold text-gray-800 mt-4 mb-2">How to find Honor of Kings Player ID?</h4>
                        <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 ml-4">
                            <li>Use your account to login the game.</li>
                            <li>Click your Avatar and enter personal Homepage.</li>
                            <li>Expand the setting on top right and select view UID.</li>
                            <li>Your Player ID will be displayed.</li>
                        </ol>
                    </div>
                @elseif(isset($gameType) && $gameType === 'freefire')
                    <!-- Free Fire Content -->
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">About Free Fire Diamond Top-up</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            Garena Free Fire Diamonds are a virtual currency that can be used to purchase weapons, pets, skins, and other items in the in-game store. With over 100 secure payment options available, players can easily buy and receive their codes instantly, eliminating the need to go through the App Store or Google Play Store. Not only that, but players can also participate in Luck Royale and Diamond Spin to win unique character skins, weapon skins, weapon upgrades, and cosmetics. As the leading battle royale game, Free Fire has gained immense popularity in Asia and is now expanding worldwide, making the Free Fire Diamonds a highly sought-after commodity. To enhance the gameplay experience, players can top up their Free Fire Diamonds at DiasZone, the cheapest and easiest way to purchase in-game currency.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Difference between Free Fire Max Diamonds & Free Fire Diamonds?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            Free Fire Diamonds and Free Fire Max Diamonds are both in-game currency that can be used to purchase various items, weapons, skins, and more within the popular mobile battle royale game, Garena Free Fire. The main difference between the two is that Free Fire Diamonds can be used in the regular version of the game, while Free Fire Max Diamonds can be used in the upgraded version, Free Fire Max. However, it's important to note that both types of diamonds can be used interchangeably and will automatically sync to your game ID, regardless of which version of the game you are playing. This means that if you purchase Free Fire Diamonds, they can also be used in Free Fire Max and vice versa. To purchase either type of diamonds, players can visit DiasZone. With these diamonds, players can enhance their gameplay experience and gain access to a wider variety of in-game items and features.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">How do I get Free Fire Diamonds?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            There are several ways to obtain Free Fire Diamonds, the in-game currency used to purchase weapons, pets, skins, and other items in the game. One way is to purchase them directly from the in-game store using real money. You can also participate in in-game events and challenges to earn diamonds as rewards. Another way is to exchange with your friends, some players may offer to trade diamonds with other in-game items or currency. Additionally, you can also buy Free Fire diamonds through third-party websites like DiasZone.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">What are Free Fire Diamonds Used for?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            Based on the same principle as PUBG Mobile Unknown Cash or Apex Legends Coins, Free Fire Diamonds work the same way. Free Fire Diamonds, also known as FF Diamonds, are a virtual currency used in the popular mobile game, Garena Free Fire. They can be used to purchase a variety of in-game items such as weapons, skins, and characters. These items can enhance the player's experience and provide a unique look to the player's character. Additionally, they can also be used to participate in in-game events, such as the Luck Royale and Diamond Spin, which offer a chance to obtain exclusive items such as character skins, weapon upgrades and other cosmetics. Furthermore, FF Diamonds can also be used to purchase in-game currency and other items from the game's store, allowing players to quickly acquire new items or advance in the game without having to spend a lot of time earning in-game currency through gameplay. Overall, Free Fire Diamonds offer players a convenient way to enhance their gaming experience and access exclusive items that are not available through normal gameplay.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Why Buy Free Fire Diamonds?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            Free Fire Diamonds offer a variety of benefits to enhance your gaming experience. With Free Fire Diamonds, you can purchase exclusive in-game items such as weapons, skins, and characters, each of which provides a unique look to your character and will help you stand out from other players. Furthermore, Free Fire Diamonds can be used to participate in in-game events such as Luck Royale and Diamond Spin, which offer a chance to obtain rare and unique items that can give you an edge over other players.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            In addition to these benefits, Free Fire Diamonds can also be used to purchase in-game currency, which can help you advance in the game more quickly. This is especially useful for players who want to obtain new items or advance to higher levels without having to spend a lot of time earning in-game currency through gameplay. Another advantage of purchasing Free Fire Diamonds is that it is a safe and secure way to buy in-game items, with over 100 secure payment options available. The process is quick and easy, and you will receive your code instantly, so you can start using your Diamonds right away. Free Fire Diamonds are an essential aspect of the game that can greatly enhance your gaming experience. With Free Fire Diamonds, you can purchase exclusive items, participate in in-game events, and advance in the game more quickly. So why wait? Purchase Free Fire Diamonds today and take your gaming experience to the next level!
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">How to check Free Fire account balance?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            Log into your account in Garena Free Fire - Battlegrounds and click on the diamond button on the top left side of the screen. In case of a delay, exit the game and log in again to see your new top-up balance in your account once you have bought Free Fire Diamonds.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Where to top up Free Fire Diamonds?</h3>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4">
                            Want to top up Free Fire Diamonds easily? Get it at DiasZone - trusted by gamers! We provide many payment options like Flexy, Baridimob, and Cryptocurrency. Enjoy fast delivery, secure transactions, and the best price for Free Fire Diamonds Top up.
                        </p>
                        <p class="text-sm text-gray-700 leading-relaxed mb-4 font-semibold">
                            Top up now at DiasZone and make your Free Fire Diamonds experience even better!
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Why Choose DiasZone to Top Up Free Fire Diamonds?</h3>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-2">
                                <span class="text-purple-600 font-bold">✓</span>
                                <span><strong>Fast and Hassle-Free:</strong> Get your Free Fire Diamonds on DiasZone in quick and easy.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-600 font-bold">✓</span>
                                <span><strong>Flexible Payment Options:</strong> Pay using the most popular payment methods worldwide.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-600 font-bold">✓</span>
                                <span><strong>Instant and Secure Delivery:</strong> Receive your purchase securely and instantly.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-purple-600 font-bold">✓</span>
                                <span><strong>Dedicated Customer Support:</strong> Our customer support team is ready to assist you anytime.</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-4">Guide</h3>
                        <div class="space-y-4 text-sm text-gray-700">
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">Free Fire Diamonds Buying Steps:</h4>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>Only Player ID is needed for Garena Free Fire Diamonds top-up.</li>
                                    <li>You may stay logged in throughout the transaction, once the top-up is completed, you will receive the Diamonds in your Garena Free Fire account.</li>
                                    <li>Please enter your Player ID correctly to avoid delay on Diamonds top-up.</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">How to top-up Free Fire Diamond?</h4>
                                <ol class="list-decimal list-inside space-y-1 ml-4">
                                    <li>Select the Diamond denomination.</li>
                                    <li>Enter your Free Fire Player ID.</li>
                                    <li>Check out and select your payment method.</li>
                                    <li>Once payment is made, the Free Fire Diamond you purchased will be credited to your Free Fire Account shortly.</li>
                                </ol>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-2">How to find Free Fire Player ID:</h4>
                                <ol class="list-decimal list-inside space-y-1 ml-4">
                                    <li>Use your account to login the game.</li>
                                    <li>Click on your avatar in the top-left corner.</li>
                                    <li>Your Free Fire Player ID will be displayed.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                @else
                    @php
                        $content = isset($game) && $game ? $game->content : null;
                        $images = isset($game) && $game ? $game->images : collect([]);
                        $currencyName = $content->currency_name ?? 'Diamonds';
                        $gameTitleForContent = $gameTitle ?? 'Game';
                    @endphp
                    
                    @if($content)
                        <!-- Dynamic Game Content -->
                        @if($content->about_text)
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('game_content.about_title', ['game' => $gameTitleForContent, 'currency' => $currencyName]) }}</h3>
                            <div class="text-sm text-gray-700 leading-relaxed space-y-4">
                                {!! nl2br(e($content->about_text)) !!}
                                
                                @php
                                    $aboutImages = $images->where('image_type', 'about')->sortBy('display_order');
                                @endphp
                                
                                @if($aboutImages->count() > 0)
                                    @foreach($aboutImages as $img)
                                        <div class="mt-4">
                                            <img src="{{ asset($img->image_path) }}" 
                                                 alt="{{ $img->alt_text ?? 'About image' }}" 
                                                 class="rounded-lg max-w-full h-auto">
                                            @if($img->title)
                                                <p class="text-xs text-gray-500 mt-2 italic">{{ $img->title }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        @if($content->instructions_text)
                        <!-- How to find User ID/Zone ID/etc -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">How to find {{ $gameTitleForContent }} {{ $content->id_format === 'user_id_zone_id' ? 'User ID and Zone ID' : ($content->id_format === 'player_id' ? 'Player ID' : ($content->id_format === 'user_id' ? 'User ID' : 'ID')) }}?</h3>
                            <div class="space-y-4 text-sm text-gray-700">
                                {!! nl2br(e($content->instructions_text)) !!}
                                
                                @php
                                    $instructionImages = $images->where('image_type', 'instruction')->sortBy('display_order');
                                @endphp
                                
                                @if($instructionImages->count() > 0)
                                    @foreach($instructionImages as $img)
                                        <div class="mt-4">
                                            <img src="{{ asset($img->image_path) }}" 
                                                 alt="{{ $img->alt_text ?? 'Instruction image' }}" 
                                                 class="rounded-lg max-w-full h-auto">
                                            @if($img->title)
                                                <p class="text-xs text-gray-500 mt-2 italic">{{ $img->title }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif
                        
                        <!-- Why Buy -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('game_content.why_buy_title', ['currency' => $currencyName]) }}</h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>Instant delivery after payment confirmation</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>{{ __('recharge.secure_payment_methods') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>Best prices and exclusive discounts</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>24/7 customer support</span>
                                </li>
                            </ul>
                        </div>
                        
                        @if($content->how_to_topup)
                        <!-- How to Top Up -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">How to Top Up</h3>
                            <div class="text-sm text-gray-700 space-y-4">
                                {!! nl2br(e($content->how_to_topup)) !!}
                                
                                @php
                                    $howToTopupImages = $images->where('image_type', 'how_to_topup')->sortBy('display_order');
                                @endphp
                                
                                @if($howToTopupImages->count() > 0)
                                    @foreach($howToTopupImages as $img)
                                        <div class="mt-4">
                                            <img src="{{ asset($img->image_path) }}" 
                                                 alt="{{ $img->alt_text ?? 'How to top up image' }}" 
                                                 class="rounded-lg max-w-full h-auto">
                                            @if($img->title)
                                                <p class="text-xs text-gray-500 mt-2 italic">{{ $img->title }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @else
                        <!-- Default How to Top Up -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">{{ __('game_content.how_to_topup_title') }}</h3>
                            <div class="space-y-4 text-sm text-gray-700">
                                <ol class="list-decimal list-inside space-y-2 ml-4">
                                    <li>Select a {{ strtolower($currencyName) }} pack from the offers above</li>
                                    <li>Enter your {{ $gameTitleForContent }} {{ $content->id_format === 'user_id_zone_id' ? 'User ID and Zone ID' : ($content->id_format === 'player_id' ? 'Player ID' : ($content->id_format === 'user_id' ? 'User ID' : 'ID')) }}</li>
                                    <li>Choose your preferred payment method</li>
                                    <li>Complete the payment</li>
                                    <li>Receive your {{ strtolower($currencyName) }} instantly!</li>
                                </ol>
                                
                                @php
                                    $howToTopupImages = $images->where('image_type', 'how_to_topup')->sortBy('display_order');
                                @endphp
                                
                                @if($howToTopupImages->count() > 0)
                                    @foreach($howToTopupImages as $img)
                                        <div class="mt-4">
                                            <img src="{{ asset($img->image_path) }}" 
                                                 alt="{{ $img->alt_text ?? 'How to top up image' }}" 
                                                 class="rounded-lg max-w-full h-auto">
                                            @if($img->title)
                                                <p class="text-xs text-gray-500 mt-2 italic">{{ $img->title }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif
                    @else
                        <!-- Fallback: Mobile Legends Content (Default) -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">About Mobile Legends Diamonds</h3>
                            <p class="text-sm text-gray-700 leading-relaxed">
                                Mobile Legends Diamonds are the premium in-game currency used to purchase heroes, skins, emblems, and other exclusive items. With DiasZone, you can top up your diamonds quickly and securely.
                            </p>
                        </div>
                        
                        <!-- How to find ML User ID and Zone ID -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">How to find ML User ID and Zone ID?</h3>
                            <div class="space-y-4 text-sm text-gray-700">
                                <p>To find your User ID and Zone ID in Mobile Legends:</p>
                                <ol class="list-decimal list-inside space-y-2 ml-4">
                                    <li>Open Mobile Legends: Bang Bang</li>
                                    <li>Go to your Profile</li>
                                    <li>Your User ID and Zone ID are displayed at the top of your profile</li>
                                </ol>
                                <p><img src="{{ asset('storage/images_homepage/how.webp') }}" alt="How to find User ID and Zone ID" class="mt-4 rounded-lg"></p>
                                <p><img src="{{ asset('storage/images_homepage/howtwo.webp') }}" alt="User ID and Zone ID example" class="mt-4 rounded-lg"></p>
                            </div>
                        </div>
                        
                        <!-- Why Buy Diamonds -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Why Buy Diamonds from DiasZone?</h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>Instant delivery after payment confirmation</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>{{ __('recharge.secure_payment_methods') }}</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>Best prices and exclusive discounts</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>24/7 customer support</span>
                                </li>
                            </ul>
                        </div>
                        
                        <!-- How to Top Up -->
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-4">How to Top Up</h3>
                            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700 ml-4">
                                <li>Select a diamond pack from the offers above</li>
                                <li>Enter your Mobile Legends User ID and Zone ID</li>
                                <li>Choose your preferred payment method</li>
                                <li>Complete the payment</li>
                                <li>Receive your diamonds instantly!</li>
                            </ol>
                        </div>
                    @endif
                @endif
            </div>
            @endif
            
            @if(isset($gameType))
            <!-- Right Column: Customer Reviews (Only on Game Pages) -->
            <div id="comments">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-900">Customer Reviews</h3>
                    <button id="leave-review-btn" class="px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 font-semibold rounded-lg transition-colors text-sm">
                        Leave Review
                    </button>
                </div>
                
                <!-- Overall Rating -->
                @php
                    $displayRating = (isset($totalReviews) && $totalReviews > 0 && isset($averageRating)) ? $averageRating : 5.0;
                    $displayTotalReviews = $totalReviews ?? 0;
                @endphp
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-3xl font-bold text-purple-600">{{ number_format($displayRating, 1) }}</span>
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= round($displayRating) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm text-gray-600">
                            @if($displayTotalReviews > 0)
                                Based on {{ $displayTotalReviews }} {{ $displayTotalReviews === 1 ? 'review' : 'reviews' }}
                            @else
                                No reviews yet
                            @endif
                        </span>
                    </div>
                </div>
                
                <!-- Reviews List -->
                @php
                    // Get reviews for this component (limit to latest 3 for preview)
                    $reviewsForComponent = isset($reviews) && $reviews->count() > 0 
                        ? $reviews->take(3) 
                        : collect([]);
                @endphp
                
                @if($reviewsForComponent->count() > 0)
                <div id="review-list-container" class="space-y-4 max-h-96 overflow-y-auto mb-6">
                    @foreach($reviewsForComponent as $review)
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="flex">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{ e($review->name) }}</span>
                            <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-gray-700">{{ e($review->comment) }}</p>
                    </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Review Form -->
                <div id="review-form-container" class="bg-white border-2 border-purple-200 rounded-lg p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Write a Review</h4>
                    <form id="review-form" class="space-y-4">
                        @csrf
                        @if(isset($game) && $game)
                        <input type="hidden" id="review-game-id" name="game_id" value="{{ $game->id }}">
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                            <input type="text" 
                                   id="review-name"
                                   name="name"
                                   required
                                   maxlength="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                            <div id="rating-stars" class="flex gap-1">
                                <svg class="rating-star w-6 h-6 text-gray-300 cursor-pointer" data-rating="1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg class="rating-star w-6 h-6 text-gray-300 cursor-pointer" data-rating="2" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg class="rating-star w-6 h-6 text-gray-300 cursor-pointer" data-rating="3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg class="rating-star w-6 h-6 text-gray-300 cursor-pointer" data-rating="4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg class="rating-star w-6 h-6 text-gray-300 cursor-pointer" data-rating="5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            </div>
                            <input type="hidden" id="review-rating" name="rating" value="0">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Your Review</label>
                            <textarea id="review-comment"
                                      name="comment"
                                      rows="4"
                                      required
                                      minlength="5"
                                      maxlength="1000"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm resize-none"></textarea>
                        </div>
                        
                        <button type="submit" 
                                id="review-submit-btn"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="review-submit-text">Submit Review</span>
                        </button>
                        <div id="review-message" class="hidden mt-4 p-4 rounded-lg"></div>
                    </form>
                </div>
            </div>
            @else
            <!-- Right Column: Why Choose DiasZone (Only on Home Page) -->
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('home.why_choose_title') }}</h3>
                
                <div class="space-y-6">
                    <!-- Feature 1: Fast Delivery -->
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100 hover:shadow-lg transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-purple-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('home.lightning_fast') }}</h4>
                                <p class="text-sm text-gray-700 leading-relaxed">
                                    {{ __('home.lightning_fast_desc') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feature 2: Secure Payments -->
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-6 border border-blue-100 hover:shadow-lg transition-shadow">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-2">{{ __('home.secure_transactions') }}</h4>
                                <p class="text-sm text-gray-700 leading-relaxed">
                                    {{ __('home.secure_transactions_desc') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
