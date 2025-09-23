@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body">
        <div class="transaction_body_wrapper">
            <div class="transaction_title">
                <h3>Transactions</h3>
            </div>
            {{-- <div class="transaction_content_wrapper">
                <div class="transaction_header">
                    <div class="row m-0 g-3">
                        <div class="col-12">
                            <div class="transaction_search">
                                <input type="search" placeholder="Search by address, amount, or ID">
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
        </div>

        <div class="coinAssetTable_wrapper">
            <div class="coinAsset_table">
                <div class="mt-4 mb-4">
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>Transaction Hash</th>
                                <th>Block</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transfers as $key => $value)
                                @php $transactionSubtype = $value['transactionSubtype']; @endphp
                                @if ($transactionSubtype == 'incoming' || $transactionSubtype == 'outgoing')
                                    <tr>
                                        <td>
                                            @php
                                                $hash_full = $value['hash'];
                                                $hash_short =
                                                    substr($hash_full, 0, 10) . '...' . substr($hash_full, -8);
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
                                            $from_short = substr($from_full, 0, 10) . '...' . substr($from_full, -8);
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
                                                <h5>{{ $value['amount'] }} {{ $value['chain'] }}</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
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
@endsection
