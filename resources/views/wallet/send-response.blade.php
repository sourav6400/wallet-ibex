@extends('layouts.app')
@section('content')
    <div class="dashboardRightMain_body p-0">
        <div class="myWallet_wrapper">
            <div class="myWallet_sidebar">
                @include('layouts.my-wallet-sidebar')
            </div>
            <div class="myWallet_body sendPopup1 ">
                @if ($status == 'success')
                    <div class="newAccount_popup_wrapper position-relative">
                        <div class="sucessfully_sent">
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
                                $icon = $iconMap[$token] ?? null;
                            @endphp
                            <h3>Send {{ $tokenName }} <span>{{ $token }}</span>
                                @if ($icon)
                                    <img src="{{ asset('images/icon/' . $icon) }}" alt="{{ $token }} icon">
                                @endif
                            </h3>
                            <img class="vector" src="{{ asset('images/vector/vector8.png') }}" alt="">
                            <span>Sucessfully sent {{ $amount }} {{ $token }}</span>
                            <span>Transaction ID: {{ $message }}</span>
                        </div>
                    </div>
                @else
                    <div class="error-card">
                        <!-- Error Icon -->
                        <div class="icon-container">
                            <div class="error-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Error Title -->
                        <h1 class="error-title">
                            Transaction Failed!
                        </h1>

                        <!-- Error Description -->
                        <div class="error-description">
                            <p>{{ $message }}</p>
                            @if ($details)
                                <p class="error_details">{{ $details }}</p>
                            @endif
                        </div>

                        <!-- Action Button -->
                        <a href="{{ route('wallet.send_token_s1', strtolower($token)) }}" class="error-button">
                            Try Again
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .error-card {
            position: relative;
            z-index: 2;
            background: #1B1D2D;
            border-radius: 24px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(75, 85, 99, 0.5);
            animation: bounce-in 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            margin: 0 auto;
        }

        @keyframes bounce-in {
            0% {
                opacity: 0;
                transform: scale(0.3) translateY(-50px);
            }

            50% {
                opacity: 0.8;
                transform: scale(1.05) translateY(0);
            }

            70% {
                transform: scale(0.95);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .icon-container {
            margin-bottom: 32px;
            display: flex;
            justify-content: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-icon svg {
            width: 40px;
            height: 40px;
            color: white;
            stroke-width: 2.5;
        }

        .error-title {
            font-size: 30px;
            font-weight: 700;
            color: #f87171;
            margin-bottom: 24px;
            line-height: 1.2;
        }

        .error-description {
            margin-bottom: 32px;
        }

        .error-description p {
            color: #d1d5db;
            font-size: 18px;
            line-height: 1.6;
            margin: 0;
        }

        .error-button {
            width: 100%;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-weight: 600;
            padding: 16px 32px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .error-button:hover {
            box-shadow: 0 10px 25px -3px rgba(239, 68, 68, 0.25);
        }

        .error-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .error-card {
                padding: 32px;
                margin: 16px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-description p {
                font-size: 16px;
            }

            .error-icon {
                width: 64px;
                height: 64px;
            }

            .error-icon svg {
                width: 32px;
                height: 32px;
            }
        }
    </style>
@endsection
