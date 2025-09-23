@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body">
        <div class="row m-0 gx-3 gy-3 gy-xl-0 justify-content-center">
            <div class="col-xl-12 ps-xl-0 pe-0">
                <div class="walletBalanceChart_wrapper">
                    <div class="walletBalanceChart_header">
                        <div class="row g-2 m-0">
                            <div class="col-lg-5 ps-lg-0">
                                <div class="walletBalanceChart_title">
                                    <h4>Wallet Balance</h4>
                                    @php
                                        $totalUsd = number_format((float) $totalUsd, 2, '.', ',');
                                    @endphp
                                    <h3 id="totalBalance">$ {{ $totalUsd }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="coinAssetTable_wrapper">
            <div class="coinAssetTable_header">
                <h3 class="title">Active Assets</h3>
            </div>
            <div class="coinAsset_table">
                <table>
                    <tr>
                        <th>Asset name</th>
                        <th>Balance</th>
                        <th>Value</th>
                        <th>Unit Price</th>
                        <th>Portfolio</th>
                    </tr>
                    @foreach ($tokens as $token)
                        @php
                            if ($totalCoin > 0) {
                                $portfolio = ($token['tokenBalance'] / $totalCoin) * 100;
                            } else {
                                $portfolio = 0; // avoid division by zero
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="assetName_data">
                                    <ul>
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

                                            $symbol = $token['symbol'];
                                            $icon = $iconMap[$symbol] ?? null;
                                        @endphp

                                        @if ($icon)
                                            <li><img src="{{ asset('images/icon/' . $icon) }}"
                                                    alt="{{ $symbol }} icon"></li>
                                        @endif
                                        <li>
                                            <h4>{{ $token['name'] }}</h4>
                                            <h5>{{ $token['symbol'] }}</h5>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <div class="balance_data">
                                    <h5>{{ number_format($token['tokenBalance'], 2, '.', ',') }}</h5>
                                </div>
                            </td>
                            <td>
                                <div class="value_data">
                                    <h5>${{ number_format($token['tokenBalance'] * $token['usdUnitPrice'], 2, '.', ',') }}
                                    </h5>
                                </div>
                            </td>
                            <td>
                                <div class="price_data">
                                    <h5>${{ number_format($token['usdUnitPrice'], 2, '.', ',') }}</h5>
                                </div>
                            </td>
                            <td>
                                <div class="value_data">
                                    <h5>{{ number_format($portfolio, 2, '.', ',') }}%</h5>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
@endsection
