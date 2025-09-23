@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body p-0">
        <div class="myWallet_wrapper">
            <div class="myWallet_sidebar">
                @include('layouts.my-wallet-sidebar')
            </div>

            @if (isset($wallet_address))
                <div class="myWallet_body sendPopup1 " id="receivePopup1">
                    <div class="newAccount_popup_wrapper position-relative">
                        <div class="newAccountPopup_header">
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

                                $tokenNameMap = [
                                    'BTC' => 'Bitcoin',
                                    'LTC' => 'Litecoin',
                                    'ETH' => 'Ethereum',
                                    'XRP' => 'Ripple',
                                    'USDT' => 'Tether',
                                    'DOGE' => 'Doge',
                                    'TRX' => 'Tron',
                                    'BNB' => 'Binance',
                                ];

                                $upperSymbol = strtoupper($symbol);
                                $icon = $iconMap[$upperSymbol] ?? null;
                                $tokenName = $tokenNameMap[$upperSymbol] ?? null;
                            @endphp

                            <a href="{{ url()->previous() }}" class="sendPage_closeBtn"><i class="fa-regular fa-xmark"></i></a>

                            @if ($icon)
                                <img class="vector" src="{{ asset('images/icon/' . $icon) }}"
                                    alt="{{ $upperSymbol }} icon">
                            @endif

                            <h3>{{ $tokenName }}</h3>
                            <h6>Your {{ $upperSymbol }} address</h6>
                            @php
                                $qrCode = QrCode::size(200)->generate($wallet_address);
                            @endphp
                            <div class="qrCode" style="margin-left: 20rem;">{{ $qrCode }}</div>
                            {{-- <img class="qrCode" src="{{ asset('images/vector/vector9.png') }}" alt=""> --}}
                        </div>
                        <div class="row g-2 m-0">
                            <div class="col-12">
                                <div class="form_input position-relative">
                                    <input type="text" id="walletAddress" placeholder="" value="{{ $wallet_address }}" readonly>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form_btn">
                                    <button type="button" class="changeName" onclick="copyWalletAddress()">Copy Address <i
                                            class="fa-solid fa-copy"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="myWallet_body sendPopup1 " id="receivePopup1">
                    <div class="newAccount_popup_wrapper position-relative">
                        <div class="row g-2 m-0">
                            <div class="col-12">
                                <div class="form_input position-relative">
                                    <input type="text" id="walletAddress" placeholder="" value="This Wallet is missing!" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function copyWalletAddress() {
            const input = document.getElementById('walletAddress');
            input.select();
            input.setSelectionRange(0, 99999); // For mobile devices
            navigator.clipboard.writeText(input.value)
                .then(() => {
                    alert('Wallet address copied to clipboard!');
                })
                .catch(err => {
                    console.error('Failed to copy!', err);
                });
        }
    </script>
@endsection
