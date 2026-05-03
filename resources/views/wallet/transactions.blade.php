@extends('layouts.app')
@section('content')
    @php
        $upperSymbol = strtoupper($symbol);
        
        // Helper functions
        function formatAddress($address) {
            if (strtolower($address) === 'pending') {
                return "Processing"; // or you can return any other text here with quote. example, return “Processing”;
            }

            return substr($address, 0, 10) . '...' . substr($address, -8);
        }
        
        function formatTimestamp($timestamp) {
            // Check if timestamp is in milliseconds (13 digits) or seconds (10 digits)
            $timestampSec = strlen((string)$timestamp) > 10 ? $timestamp / 1000 : $timestamp;
            return date('M d, Y h:i A', $timestampSec);
        }
    @endphp
    <div class="dashboardRightMain_body">
        
        <div class="transaction_body_wrapper">
			<div class="transaction_title v3">
				<h3>{{ $upperSymbol }} Transactions</h3>
				<select class="transaction_dropdown_v3" id="transactionFilter" onchange="handleFilterChange(this)">
				    <option value="" disabled selected>Filter By</option>
					<option value="btc">Bitcoin</option>
					<option value="eth">Ethereum</option>
					<option value="ltc">Litecoin</option>
					<option value="usdt">Tron</option>
					<option value="xrp">XRP</option>
					<option value="doge">Dogecoin</option>
					<option value="trx">Tron</option>
					<option value="bnb">BNB</option>
				</select>
			</div>
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
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sl = 0; @endphp

                                @if(in_array($upperSymbol, ['ETH', 'BNB']))
                                    {{-- ETH/BNB Transactions (same structure) --}}
                                    @foreach($transfers as $value)
                                        @if(isset($value['isLocal']) && $value['isLocal'] === true)
                                            @php $sl++; @endphp
                                            @include('partials.transaction_row', [
                                                'sl' => $sl,
                                                'hash' => $value['hash'],
                                                'blockNumber' => $value['blockNumber'],
                                                'from' => $value['from'],
                                                'to' => $value['to'],
                                                'type' => $value['displayType'],
                                                'amount' => abs($value['amount']),
                                                'timestamp' => $value['timestamp'],
                                                'symbol' => $upperSymbol
                                            ])
                                            @continue
                                        @endif
                                        @php
                                            $walletAddress = $value['wallet_address'];
                                            $subtype = $value['transactionSubtype'];
                                            
                                            // ETH: native transfers omit tokenAddress; ERC-20 uses configured contract
                                            if($upperSymbol === 'ETH') {
                                                $isNative = isset($value['transactionType']) && $value['transactionType'] === 'native';
                                                $isConfiguredToken = isset($value['tokenAddress'])
                                                    && $value['tokenAddress'] === config('tatum.contract_address');
                                                $isValidTransaction = in_array($subtype, ['incoming', 'outgoing'])
                                                    && ($isNative || $isConfiguredToken);
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
                                                'amount' => abs($value['amount']),
                                                'timestamp' => $value['timestamp'],
                                                'symbol' => $upperSymbol
                                            ])
                                        @endif
                                    @endforeach

                                @elseif(in_array($upperSymbol, ['BTC', 'LTC', 'DOGE']))
                                    {{-- BTC/LTC/DOGE Transactions --}}
                                    @foreach($transfers as $value)
                                        @if(isset($value['isLocal']) && $value['isLocal'] === true)
                                            @php $sl++; @endphp
                                            @include('partials.transaction_row', [
                                                'sl' => $sl,
                                                'hash' => $value['hash'],
                                                'blockNumber' => $value['blockNumber'],
                                                'from' => $value['from'],
                                                'to' => $value['to'],
                                                'type' => $value['displayType'],
                                                'amount' => abs($value['amount']),
                                                'timestamp' => $value['timestamp'],
                                                'symbol' => $upperSymbol
                                            ])
                                            @continue
                                        @endif
                                        @php
                                            $sl++;
                                            $walletAddress = $value['wallet_address'];
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
                                            } elseif($receiver) {
                                                $from = $input['coin']['address'] ?? null;
                                            }
                                        @endphp

                                        @include('partials.transaction_row', [
                                            'sl' => $sl,
                                            'hash' => $value['hash'],
                                            'blockNumber' => $value['blockNumber'],
                                            'from' => $from,
                                            'to' => $to,
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

    <style>
        .transaction_dropdown_v3 {
            background: #1B1D2D;
            border: 0;
            color: #fff;
            font-size: 16px;
            padding: 14px 10px;
            border-radius: 5px;
            width: 100%;
            max-width: 140px;
            cursor: pointer;
        }
        .transaction_title.v3 {
            display: flex;
            align-items: center;
            justify-content: space-between;
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
        function handleFilterChange(selectElement) {
            const filterValue = selectElement.value;
            const currentSymbol = '{{ $upperSymbol }}';
            
            // Construct the URL with the filter parameter
            // Adjust the route name according to your Laravel routes
            const url = `{{ url('transactions') }}/${filterValue}`;
            
            // Redirect to the new URL
            window.location.href = url;
        }

        // Set the selected filter on page load
        // document.addEventListener('DOMContentLoaded', function() {
        //     const urlParams = new URLSearchParams(window.location.search);
        //     const filterParam = urlParams.get('filter') || 'all';
        //     const selectElement = document.getElementById('transactionFilter');
            
        //     if (selectElement) {
        //         selectElement.value = filterParam;
        //     }
        // });

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
@endsection