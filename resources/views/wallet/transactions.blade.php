@php
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

@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body">
        <div class="transaction_body_wrapper">
            <div class="transaction_title">
                <h3>Transactions</h3>
                <div class="transaction_filter">
                    <select id="transactionFilter" class="form-select" onchange="handleFilterChange(this)">
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
                                        @php
                                            $walletAddress = $value['wallet_address'];
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
        .transaction_title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .transaction_filter {
            margin-left: auto;
        }

        .transaction_filter .form-select {
            padding: 6px 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            background-color: white;
            transition: border-color 0.2s;
            min-width: 140px;
            height: auto;
        }

        .transaction_filter .form-select:hover {
            border-color: #adb5bd;
        }

        .transaction_filter .form-select:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

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
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const filterParam = urlParams.get('filter') || 'all';
            const selectElement = document.getElementById('transactionFilter');
            
            if (selectElement) {
                selectElement.value = filterParam;
            }
        });

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