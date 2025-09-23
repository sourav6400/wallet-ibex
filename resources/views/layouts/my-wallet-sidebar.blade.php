<div class="myWallet_sidebar">
    {{-- <input type="text" placeholder="Search assets..."> --}}
    <div class="myWallet_sidebar_item">
        @foreach ($tokens as $token)
            <a href="{{ url('my-wallet/' . strtolower($token['symbol'])) }}">
                <ul  class="{{ $token['symbol'] === strtoupper($symbol) ? 'active' : '' }}">
                <li>
                    @php
                        $iconMap = [
                            'BTC' => 'icon5.svg',
                            'LTC' => 'icon6.svg',
                            'ETH' => 'icon7.svg',
                            'XRP' => 'icon8.svg',
                            'USDT' => 'tether.svg',
                            'DOGE' => 'dodge.svg',
                            'TRX' => 'trx.svg',
                            'BNB' => 'icon_bnb.svg',
                        ];
                        $tokenIcon = $iconMap[$token['symbol']] ?? null;
                    @endphp
                    @if ($tokenIcon)
                        <img src="{{ asset('images/icon/' . $tokenIcon) }}" alt="">
                    @endif
                </li>
                <li>
                    <h5>{{ $token['name'] }}</h5>
                    @php
                        $rawBalance = $token['tokenBalance'] ?? 0;
                        $numericBalance = is_numeric($rawBalance) ? (float) $rawBalance : 0;

                        if ($numericBalance < 1000) {
                            $displayBalance = number_format($numericBalance, 4, '.', '');
                        } else {
                            $units = ['K', 'M', 'B', 'T'];
                            $power = floor(log($numericBalance, 1000));
                            $unit = $units[$power - 1] ?? '';
                            $displayBalance = round($numericBalance / pow(1000, $power), 1) . $unit;
                        }
                    @endphp
                    <h6><span>{{ $displayBalance }}</span> {{ $token['symbol'] }}</h6>
                </li>
                </ul>
            </a>
        @endforeach

        {{-- <button type="button" class="addAssets"><img src="{{ asset('images/icon/icon9.svg') }}" alt=""> Add Asset</button> --}}
    </div>
</div>
