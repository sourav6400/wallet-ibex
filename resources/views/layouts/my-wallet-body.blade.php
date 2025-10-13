<div class="myWallet_body">
    <div class="myWallet_balance bitcoin">
        @foreach ($tokens as $token)
        @if ($token['symbol'] == strtoupper($symbol))
        {{-- Token Icon --}}
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

        $upperSymbol = strtoupper($symbol);
        $icon = $iconMap[$upperSymbol] ?? null;
        @endphp

        @if ($icon)
        <img src="{{ asset('images/icon/' . $icon) }}" alt="{{ $upperSymbol }} icon">
        @endif

        {{-- Balance & USD Value Calculation --}}
        @php
        $tokenBalanceRaw = $token['tokenBalance'] ?? 0;
        $unitPriceRaw = $token['usdUnitPrice'] ?? 0;

        $tokenBalance = is_numeric($tokenBalanceRaw) ? (float) $tokenBalanceRaw : 0;
        $usdUnitPrice = is_numeric($unitPriceRaw) ? (float) $unitPriceRaw : 0;

        $formattedTokenBalance = number_format((float) $tokenBalance, 4, '.', ',');
        $usdValue = $tokenBalance * $usdUnitPrice;
        $formattedUsdValue = number_format((float) $usdValue, 4, '.', ',');
        @endphp

        <h2 class="balance">
            {{ $formattedTokenBalance }} {{ $upperSymbol }}
        </h2>
        <h6 class="usd_balance">{{ $formattedUsdValue }} USD</h6>
        @endif
        @endforeach

        <ul>
            <a href="{{ url('send/' . $symbol) }}">
                <li><img src="{{ asset('images/icon/icon11.svg') }}" alt="">Send</li>
            </a>

            <a href="{{ url('receive/' . $symbol) }}">
                <li><img src="{{ asset('images/icon/icon12.svg') }}" alt=""> Receive</li>
            </a>
        </ul>

        <!-- transaction content here -->
        <div class="transaction_body_wrapper">
            <div class="transaction_title">
                <h3>Transactions</h3>
            </div>
            <!-- dynamic data here -->

            <div class="coinAssetTable_wrapper">
                <div class="coinAsset_table">
                    <div class="mt-4 mb-4">
                        <table id="dataTable">
                            <thead>
                                <tr>
                                    <th>SL#</th>
                                    <th>Transaction Hash</th>
                                    <th>Block</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            @if($upperSymbol == 'ETH')
                            <tbody>
                                @php $sl = 0; @endphp
                                @foreach ($transfers as $key => $value)
                                @php $transactionSubtype = $value['transactionSubtype']; @endphp
                                @if (($transactionSubtype == 'incoming' || $transactionSubtype == 'outgoing') && (isset($value['tokenAddress']) && $value['tokenAddress']=='0x6727e93eedd2573795599a817c887112dffc679b'))
                                @php $sl = $sl + 1; @endphp
                                <tr>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $sl }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                        $hash_full = $value['hash'];
                                        $hash_short = substr($hash_full, 0, 10) . '...' . substr($hash_full, -8);
                                        $timestampMs = $value['timestamp']; // from your array
                                        $timestampSec = $timestampMs / 1000; // convert ms → s

                                        $dateTime = date('Y-m-d H:i a', $timestampSec);
                                        @endphp
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $hash_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $hash_full }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $value['blockNumber'] }}</h5>
                                        </div>
                                    </td>
                                    @php
                                    if ($transactionSubtype == 'incoming') {
                                    $from_full = $value['counterAddress'];
                                    $to_full = $value['address'];
                                    } elseif ($transactionSubtype == 'outgoing') {
                                    $to_full = $value['counterAddress'];
                                    $from_full = $value['address'];
                                    }
                                    $from_short =
                                    substr($from_full, 0, 10) . '...' . substr($from_full, -8);
                                    $to_short = substr($to_full, 0, 10) . '...' . substr($to_full, -8);
                                    @endphp
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $from_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $from_full }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $to_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $to_full }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ ucfirst($transactionSubtype) }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            {{-- {{ $value['chain'] }} --}}
                                            <h5>{{ number_format(abs($value['amount']), 6, '.', '') }} {{ $upperSymbol }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $dateTime }}</h5>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                            
                            @elseif($upperSymbol == 'BNB')
                            <tbody>
                                @php $sl = 0; @endphp
                                @foreach ($transfers as $key => $value)
                                @php $transactionSubtype = $value['transactionSubtype']; @endphp
                                @if ($transactionSubtype == 'incoming' || $transactionSubtype == 'outgoing')
                                @php $sl = $sl + 1; @endphp
                                <tr>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $sl }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                        $hash_full = $value['hash'];
                                        $hash_short = substr($hash_full, 0, 10) . '...' . substr($hash_full, -8);
                                        $timestampMs = $value['timestamp']; // from your array
                                        $timestampSec = $timestampMs / 1000; // convert ms → s

                                        $dateTime = date('Y-m-d H:i a', $timestampSec);
                                        @endphp
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $hash_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $hash_full }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $value['blockNumber'] }}</h5>
                                        </div>
                                    </td>
                                    @php
                                    if ($transactionSubtype == 'incoming') {
                                    $from_full = $value['counterAddress'];
                                    $to_full = $value['address'];
                                    } elseif ($transactionSubtype == 'outgoing') {
                                    $to_full = $value['counterAddress'];
                                    $from_full = $value['address'];
                                    }
                                    $from_short =
                                    substr($from_full, 0, 10) . '...' . substr($from_full, -8);
                                    $to_short = substr($to_full, 0, 10) . '...' . substr($to_full, -8);
                                    @endphp
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $from_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $from_full }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $to_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $to_full }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ ucfirst($transactionSubtype) }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            {{-- {{ $value['chain'] }} --}}
                                            <h5>{{ number_format(abs($value['amount']), 6, '.', '') }} {{ $upperSymbol }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $dateTime }}</h5>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>

                            @elseif($upperSymbol == 'BTC' || $upperSymbol == 'LTC' || $upperSymbol == 'DOGE')
                            <tbody>
                                @php $sl = 0; @endphp
                                @foreach ($transfers as $key=>$value)
                                @php
                                $sl++;
                                $hash_full = $value['hash'];
                                $hash_short = substr($hash_full, 0, 10) . '...' . substr($hash_full, -8);
                                $timestampMs = $value['time']; // from your array
                                $timestampSec = $timestampMs / 1000; // convert ms → s
                                $dateTime = date('Y-m-d H:i a', $timestampSec);

                                $sender = false;
                                $receiver = false;
                                $from = null;
                                $to = null;
                                $amount = null;
                                $type = null;

                                $inputs = $value['inputs'];
                                foreach($inputs as $input)
                                {
                                $coinAddress = $input['coin']['address'];
                                if($coinAddress == $walletAddress)
                                {
                                $sender = true;
                                $from = $walletAddress;
                                $amount = $input['coin']['value'];
                                break;
                                }
                                }

                                $outputs = $value['outputs'];
                                foreach ($outputs as $output)
                                {
                                $outputAddress = $output['address'];
                                if($outputAddress == $walletAddress)
                                {
                                $receiver = true;
                                $to = $walletAddress;
                                $amount = $output['value'];
                                }
                                }

                                if ($sender == true)
                                {
                                $to = $outputAddress;
                                $type = "Outgoing";
                                }
                                if ($receiver == true)
                                {
                                $from = $coinAddress;
                                $type = "Incoming";
                                }
                                @endphp

                                <tr>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $sl }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $hash_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $value['hash'] }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $value['blockNumber'] }}</h5>
                                        </div>
                                    </td>
                                    @php
                                    $from_short = substr($from, 0, 10) . '...' . substr($from, -8);
                                    $to_short = substr($to, 0, 10) . '...' . substr($to, -8);
                                    @endphp
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $from_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $from }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <div class="flex-center">
                                                <h5>{{ $to_short }}</h5>
                                                <button onclick="copyToClipboard('{{ $to }}', this)"
                                                    class="copy-btn" title="Copy full address">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <span class="copy-alert">Copied!</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ ucfirst($type) }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ number_format(abs($amount), 6, '.', '') }} {{ $upperSymbol }}</h5>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="value_data">
                                            <h5>{{ $dateTime }}</h5>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #dataTable {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        /* each column adjusts to its content */
    }

    #dataTable thead th {
        /*background: #f8f9fa;*/
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }

    #dataTable tbody td {
        padding: 8px 10px;
        text-align: center;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
        white-space: nowrap;
    }

    #dataTable tbody tr:hover {
        background: #f1f3f5;
    }

    .value_data h5 {
        margin: 0;
        font-size: 14px;
        font-weight: 500;
        align-items: center;
    }

    .flex-center {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
    }

    .copy-alert {
        display: none;
        color: green;
        font-size: 0.8em;
    }

    .copy-btn {
        border: none;
        background: none;
        padding: 0;
        cursor: pointer;
    }

    .copy-btn i {
        color: #ffc107;
        /* same as your inline */
    }
</style>

<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const alertSpan = btn.parentElement.querySelector('.copy-alert');
            alertSpan.style.display = 'inline';
            setTimeout(() => {
                alertSpan.style.display = 'none';
            }, 1500);
        });
    }
</script>