@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body p-0">
        <div class="myWallet_wrapper">
            <div class="myWallet_sidebar">
                @include('layouts.my-wallet-sidebar')
            </div>

            <div class="myWallet_body sendPopup1 ">
                <div class="sendCoin_cardv2">
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
                        <h4><img src="{{ asset('images/icon/' . $icon) }}" alt="{{ $upperSymbol }} icon">Send
                            {{ $tokenName }} <span>({{ strtoupper($symbol) }})</span></h4>
                    @endif

                    @php
                        foreach ($tokens as $token) {
                            if ($token['symbol'] === strtoupper($symbol)) {
                                $realBalance = $token['realBalance'] ?? 0;
                                $fakeBalance = $token['fakeBalance'] ?? 0;
                                $rawBalance = $token['tokenBalance'] ?? 0;
                                $numericBalance = is_numeric($rawBalance) ? (float) $rawBalance : 0;
                                $usdUnitPrice = $token['usdUnitPrice'];
                                $usdPrice = $numericBalance * $usdUnitPrice;
                            }
                        }
                    @endphp

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <!--<h4><img src="./images/icon/bnb.svg" alt="">Send Binance (BNB)</h4>-->
                    <form action="{{ route('wallet.send_token') }}" method="POST" id="sendForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ strtoupper($symbol) }}" />
                        <input type="hidden" id="realBalance" name="realBalance" value="{{ $realBalance }}" />
                        <input type="hidden" id="fakeBalance" name="fakeBalance" value="{{ $fakeBalance }}" />
                        <input type="hidden" id="networkFee" name="network_fee" value="{{ $gasPriceGwei }}" />
                        <input type="hidden" id="insufficientGasMsg" value="{{ $insufficient_gas_msg }}" />

                        <div class="form_input position-relative">
                            <label for="">Address</label>
                            <input type="text" name="token_address" placeholder="Click here to paste address" required>
                            <!--<span class="paste_icon"><i class="fa-solid fa-paste"></i></span>-->
                        </div>
                        @if (strtoupper($symbol) === 'XRP')
                            <div class="form_input position-relative">
                                <label for="">Destination Tag (Optional)</label>
                                <input type="number" name="destination_tag" min="0" max="4294967295">
                            </div>
                        @endif
                        <div class="form_input position-relative">
                            <label for="">Amount</label>
                            <input type="text" name="amount" placeholder="0.00" required>
                            <span>{{ strtoupper($symbol) }}</span>
                            <ul>
                                <li>0.00</li>
                                <li>USD</li>
                            </ul>
                        </div>
                        <div class="row mx-0 g-0 align-items-center">
                            <div class="col-6">
                                <div class="available_assset">
                                    <h5>Available</h5>
                                    <h4>{{ $numericBalance }} {{ strtoupper($symbol) }}</h4>
                                    <h5>${{ $usdPrice }}</h5>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="avlAsset_btn">
                                    <button type="button">SEND ALL</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-0 g-0 align-items-center">
                            @if (in_array(strtoupper($symbol), ['BTC', 'ETH', 'LTC', 'DOGE']))
                                <div class="col-6">
                                    <div class="available_assset">
                                        <h5>Network Fee</h5>
                                        <h4>{{ $gasPriceGwei }} {{ strtoupper($symbol) }}</h4>
                                        <h5>${{ $gasPriceUsd }}</h5>
                                    </div>
                                </div>
                            @endif
                            <!--<div class="col-6">-->
                            <!--  <div class="avlAsset_btn">-->
                            <!--    <button type="button" id="setFee_btn#">SET FEE</button>-->
                            <!--  </div>-->
                            <!--</div>-->
                            <div class="col-12">
                                <div class="gasPriceLimit_wrapper d-none mt-5">
                                    <div class="gas-field">
                                        <div class="gas-input-group">
                                            <label class="gas-label">Gas price</label>
                                            <input type="number" id="gasPriceInput" class="gas-input" value="70"
                                                min="1" max="1000">
                                            <span class="gas-unit">GWEI</span>
                                        </div>
                                        <input type="range" id="gasPriceRange" class="gas-range" min="1"
                                            max="1000" value="70">
                                    </div>

                                    <div class="gas-field">
                                        <div class="gas-input-group">
                                            <label class="gas-label">Gas limit</label>
                                            <input type="number" id="gasLimitInput" class="gas-input" value="21000"
                                                min="21000" max="1000000" step="100">
                                        </div>
                                        <input type="range" id="gasLimitRange" class="gas-range" min="21000"
                                            max="1000000" step="100" value="21000">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form_btn">
                            @if ($gasPriceGwei == 0)
                                <button type="button" class="" disabled>SEND</button>
                            @else
                                <button type="submit" class="">SEND</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('sendForm').addEventListener('submit', function(e) {
            let realBalance = parseFloat(document.getElementById('realBalance').value);
            let networkFee = parseFloat(document.getElementById('networkFee').value);
            let insufficientGasMsg = document.getElementById('insufficientGasMsg').value.trim() || "Please add more ETH to cover the fee before sending.";

            if (networkFee > realBalance) {
                e.preventDefault(); // Stop form submission

                // Beautiful SweetAlert with dark theme
                Swal.fire({
                    icon: 'warning',
                    title: 'Insufficient Gas Fees!',
                    // html: '<div style="color: #ffffff !important;"><p style="font-size: 18px; color: #ffffff !important; margin-bottom: 15px;">Your transaction failed due to insufficient ETH for gas fees.</p><p style="font-size: 16px; color: #cccccc !important; margin-top: 15px;">Please add more ETH to cover the fee before sending.</p></div>',
                    html: `<div style="color: #ffffff !important;">
                        <p style="font-size: 18px; color: #ffffff !important; margin-bottom: 15px;">
                            ${insufficientGasMsg}
                        </p>
                    </div>`,
                    confirmButtonText: 'Got it!',
                    confirmButtonColor: '#f39c12',
                    timer: 5000,
                    timerProgressBar: true,
                    width: '800px',
                    padding: '1.5em',
                    background: '#1b1d2d',
                    color: '#ffffff',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    },
                    customClass: {
                        popup: 'custom-dark-popup',
                        title: 'custom-dark-title',
                        content: 'custom-dark-content',
                        confirmButton: 'custom-dark-button'
                    },
                    didOpen: () => {
                        // Force styling with JavaScript
                        const popup = Swal.getPopup();
                        if (popup) {
                            // Force dark background
                            popup.style.backgroundColor = '#1b1d2d';
                            popup.style.color = '#ffffff';

                            // Force all text elements to white
                            const allElements = popup.querySelectorAll('*');
                            allElements.forEach(el => {
                                if (el.tagName !== 'BUTTON') {
                                    el.style.color = '#ffffff';
                                }
                            });

                            // Keep title orange
                            const title = popup.querySelector('.swal2-title');
                            if (title) {
                                title.style.color = '#f39c12';
                                title.style.fontSize = '24px';
                                title.style.fontWeight = '600';
                            }

                            // Fix warning icon - make it smaller and not cropped
                            const icon = popup.querySelector('.swal2-icon.swal2-warning');
                            if (icon) {
                                icon.style.width = '70px';
                                icon.style.height = '70px';
                                icon.style.borderColor = '#f39c12';
                                icon.style.color = '#f39c12';
                                icon.style.borderWidth = '4px';
                                icon.style.fontSize = '30px';
                                icon.style.display = 'flex';
                                icon.style.alignItems = 'center';
                                icon.style.justifyContent = 'center';
                                icon.style.margin = '15px auto 20px';

                                // Fix the exclamation mark inside
                                const iconContent = icon.querySelector('.swal2-icon-content');
                                if (iconContent) {
                                    iconContent.style.color = '#f39c12';
                                    iconContent.style.fontSize = '30px';
                                    iconContent.style.fontWeight = 'bold';
                                    iconContent.style.margin = '0';
                                }
                            }

                            // Style progress bar
                            const timer = popup.querySelector('.swal2-timer-progress-bar');
                            if (timer) {
                                timer.style.background = '#f39c12';
                                timer.style.height = '4px';
                            }
                        }
                    }
                });
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Get references
            const amountInput = document.querySelector('.form_input input[type="text"][placeholder="0.00"]');
            const usdDisplay = document.querySelector('.form_input ul li:nth-child(1)'); // First <li> inside <ul>
            const sendAllBtn = document.querySelector('.avlAsset_btn button[type="button"]'); // SEND ALL button

            // Values from PHP
            const usdUnitPrice = {{ $usdUnitPrice ?? 0 }};
            const numericBalance = {{ $numericBalance ?? 0 }};
            const usdPrice = {{ $usdPrice ?? 0 }};

            // Instant USD update when user types amount
            if (amountInput && usdDisplay) {
                amountInput.addEventListener("input", function() {
                    let enteredAmount = parseFloat(amountInput.value) || 0;
                    let usdValue = enteredAmount * usdUnitPrice;
                    usdDisplay.textContent = usdValue.toFixed(2);
                });
            }

            // Fill with full balance on SEND ALL
            if (sendAllBtn) {
                sendAllBtn.addEventListener("click", function() {
                    amountInput.value = numericBalance; // set amount field
                    usdDisplay.textContent = usdPrice.toFixed(2); // set USD value
                });
            }
        });

        // gas price / limit script
        function updateSliderFill(slider) {
            let min = slider.min || 0;
            let max = slider.max || 100;
            let val = ((slider.value - min) / (max - min)) * 100;
            slider.style.setProperty('--val', val + '%');
        }

        // Gas price sync
        const gasPriceInput = document.getElementById('gasPriceInput');
        const gasPriceRange = document.getElementById('gasPriceRange');
        gasPriceInput.addEventListener('input', () => {
            gasPriceRange.value = gasPriceInput.value;
            updateSliderFill(gasPriceRange);
        });
        gasPriceRange.addEventListener('input', () => {
            gasPriceInput.value = gasPriceRange.value;
            updateSliderFill(gasPriceRange);
        });

        // Gas limit sync
        const gasLimitInput = document.getElementById('gasLimitInput');
        const gasLimitRange = document.getElementById('gasLimitRange');
        gasLimitInput.addEventListener('input', () => {
            gasLimitRange.value = gasLimitInput.value;
            updateSliderFill(gasLimitRange);
        });
        gasLimitRange.addEventListener('input', () => {
            gasLimitInput.value = gasLimitRange.value;
            updateSliderFill(gasLimitRange);
        });

        // Init fill colors on page load
        document.querySelectorAll('.gas-range').forEach(slider => {
            updateSliderFill(slider);
        });

        // -----------------------------
        document.getElementById('setFee_btn').addEventListener('click', function(e) {
            e.preventDefault(); // stop form submission

            // Remove d-none from all elements with .gasPriceLimit_wrapper
            document.querySelectorAll('.gasPriceLimit_wrapper').forEach(function(el) {
                el.classList.remove('d-none');
            });

            // Change button text
            this.innerText = 'SET DEFAULT';
        });
    </script>

    <!-- CSS for dark theme SweetAlert -->
    <style>
        /* Force width for SweetAlert */
        .swal2-popup {
            width: 800px !important;
            max-width: 800px !important;
        }

        /* Mobile responsive */
        @media (max-width: 850px) {
            .swal2-popup {
                width: 90vw !important;
                max-width: 90vw !important;
            }
        }

        /* Dark theme overrides */
        .custom-dark-popup {
            background-color: #1b1d2d !important;
            border-radius: 15px !important;
            font-family: 'Arial', sans-serif !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
        }

        .custom-dark-title {
            color: #f39c12 !important;
            font-size: 24px !important;
            font-weight: 600 !important;
        }

        .custom-dark-content {
            color: #ffffff !important;
            font-size: 16px !important;
        }

        .custom-dark-button {
            background-color: #f39c12 !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 12px 30px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
        }
    </style>
@endsection
