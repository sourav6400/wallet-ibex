{{-- Extract reusable variables --}}
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
    $currentToken = collect($tokens)->firstWhere('symbol', $upperSymbol);
    
    // Helper functions
    function formatAddress($address) {
        return substr($address, 0, 10) . '...' . substr($address, -8);
    }
    
    function formatTimestamp($timestamp) {
        // Check if timestamp is in milliseconds (13 digits) or seconds (10 digits)
        $timestampSec = strlen((string)$timestamp) > 10 ? $timestamp / 1000 : $timestamp;
        return date('M d, Y h:i A', $timestampSec);
    }
@endphp

<div class="myWallet_body">
    <div class="myWallet_balance bitcoin">
        @if($currentToken)
            {{-- Token Icon --}}
            @if(isset($iconMap[$upperSymbol]))
                <img src="{{ asset('images/icon/' . $iconMap[$upperSymbol]) }}" alt="{{ $upperSymbol }} icon">
            @endif

            {{-- Balance & USD Value --}}
            @php
                $tokenBalance = (float) ($currentToken['tokenBalance'] ?? 0);
                $usdUnitPrice = (float) ($currentToken['usdUnitPrice'] ?? 0);
                $usdValue = $tokenBalance * $usdUnitPrice;
            @endphp

            <h2 class="balance">
                {{ number_format($tokenBalance, 4, '.', ',') }} {{ $upperSymbol }}
            </h2>
            <h6 class="usd_balance">{{ number_format($usdValue, 4, '.', ',') }} USD</h6>
        @endif

        <ul>
            <a href="{{ url('send/' . $symbol) }}">
                <li><img src="{{ asset('images/icon/icon11.svg') }}" alt="">Send</li>
            </a>
            <a href="{{ url('receive/' . $symbol) }}">
                <li><img src="{{ asset('images/icon/icon12.svg') }}" alt=""> Receive</li>
            </a>
        </ul>

        {{-- Transactions Section --}}
        <div class="transaction_body_wrapper">
            <div class="transaction_title">
                <h3>Transactions</h3>
            </div>

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
                            <tbody>
                                @php $sl = 0; @endphp

                                @if(in_array($upperSymbol, ['ETH', 'BNB']))
                                    {{-- ETH/BNB Transactions (same structure) --}}
                                    @foreach($transfers as $value)
                                        @php
                                            $subtype = $value['transactionSubtype'];
                                            
                                            // ETH has additional tokenAddress validation
                                            if($upperSymbol === 'ETH') {
                                                $isValidTransaction = in_array($subtype, ['incoming', 'outgoing']) 
                                                    && isset($value['tokenAddress']) 
                                                    && $value['tokenAddress'] === '0x6727e93eedd2573795599a817c887112dffc679b';
                                            } else {
                                                $isValidTransaction = in_array($subtype, ['incoming', 'outgoing']);
                                            }
                                        @endphp

                                        @if($isValidTransaction)
                                            @php
                                                $sl++;
                                                $from = $subtype === 'incoming' ? $value['counterAddress'] : $value['address'];
                                                $to = $subtype === 'incoming' ? $value['address'] : $value['counterAddress'];
                                            @endphp
                                            @include('partials.transaction_row', [
                                                'sl' => $sl,
                                                'hash' => $value['hash'],
                                                'blockNumber' => $value['blockNumber'],
                                                'from' => $from,
                                                'to' => $to,
                                                'type' => ucfirst($subtype),
                                                'amount' => abs($value['amount']),
                                                'timestamp' => $value['timestamp'],
                                                'symbol' => $upperSymbol
                                            ])
                                        @endif
                                    @endforeach

                                @elseif(in_array($upperSymbol, ['BTC', 'LTC', 'DOGE']))
                                    {{-- BTC/LTC/DOGE Transactions --}}
                                    @foreach($transfers as $value)
                                        @php
                                            $sl++;
                                            $sender = false;
                                            $receiver = false;
                                            $from = null;
                                            $to = null;
                                            $amount = null;

                                            // Check inputs
                                            foreach($value['inputs'] as $input) {
                                                if($input['coin']['address'] === $walletAddress) {
                                                    $sender = true;
                                                    $from = $walletAddress;
                                                    $amount = $input['coin']['value'];
                                                    break;
                                                }
                                            }

                                            // Check outputs
                                            $outputAddress = null;
                                            foreach($value['outputs'] as $output) {
                                                $outputAddress = $output['address'];
                                                if($outputAddress === $walletAddress) {
                                                    $receiver = true;
                                                    $to = $walletAddress;
                                                    $amount = $output['value'];
                                                }
                                            }

                                            // Determine transaction type
                                            if($sender) {
                                                $to = $outputAddress;
                                                $type = 'Outgoing';
                                            } elseif($receiver) {
                                                $from = $input['coin']['address'] ?? null;
                                                $type = 'Incoming';
                                            }
                                        @endphp

                                        @include('partials.transaction_row', [
                                            'sl' => $sl,
                                            'hash' => $value['hash'],
                                            'blockNumber' => $value['blockNumber'],
                                            'from' => $from,
                                            'to' => $to,
                                            'type' => $type,
                                            'amount' => abs($amount/100000000 ?? 0),
                                            'timestamp' => $value['time'],
                                            'symbol' => $upperSymbol
                                        ])
                                    @endforeach
                                @endif
                            </tbody>
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
    }

    #dataTable thead th {
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
    }
</style>

<script>
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const alertSpan = btn.parentElement.querySelector('.copy-alert');
            alertSpan.style.display = 'inline';
            setTimeout(() => alertSpan.style.display = 'none', 1500);
        });
    }
</script>