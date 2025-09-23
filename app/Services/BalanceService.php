<?php

namespace App\Services;

use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BalanceService
{
    public function getFakeBalance()
    {
        $user_id = Auth::user()->id;
        $wallet = Wallet::where('user_id', $user_id)
            ->where('chain', 'ethereum')
            ->first();

        $wallet_address = $wallet->address ?? null;

        if ($wallet_address) {
            try {
                $response = Http::timeout(10) // max 10 seconds
                    ->retry(3, 200)->get("https://styx.pibin.workers.dev/api/tatum/v3/blockchain/token/address/ETH/{$wallet_address}");

                if (!$response->successful()) {
                    Log::error("Alchemy API responded with error for wallet {$wallet_address}");
                    return 0;
                }

                $data = $response->json();
                $fakeBalances = $data ?? [];

                $fakeTokenAddress = "0x6727e93eedd2573795599a817c887112dffc679b";
                $fakeBalance = 0;

                foreach ($fakeBalances as $value) {
                    $address = $value['contractAddress'] ?? null;
                    if ($address === $fakeTokenAddress) {
                        $fakeBalance = $value['amount'] ?? 0;
                        break;
                    }
                }

                return $fakeBalance;
            } catch (\Throwable $e) {
                // API completely unreachable, timeout, DNS issue, etc.
                Log::error("Alchemy API request failed for wallet {$wallet_address}: " . $e->getMessage());
                return 0;
            }
        }
        return 0;
    }

    public function getFilteredTokens()
    {
        // Allowed symbols
        $allowedSymbols = ['BTC', 'LTC', 'ETH', 'XRP', 'USDT', 'DOGE', 'TRX', 'BNB'];
        
        // Symbol => Name mapping
        $symbolNames = [
            'BTC'  => 'Bitcoin',
            'ETH'  => 'Ethereum',
            'LTC'  => 'Litecoin',
            'USDT' => 'Tether',
            'XRP'  => 'Ripple',
            'DOGE' => 'Dogecoin',
            'TRX'  => 'Tron',
            'BNB'  => 'BNB',
        ];
        
        // Symbol => Chain mapping
        $chainNames = [
            'BTC'  => 'bitcoin',
            'ETH'  => 'ethereum',
            'LTC'  => 'litecoin',
            'USDT' => 'tron',
            'XRP'  => 'xrp',
            'DOGE' => 'dogecoin',
            'TRX'  => 'tron',
            'BNB'  => 'bsc',
        ];
        
        $fakeBalance = $this->getFakeBalance();
        $user_id     = Auth::user()->id;
        // Initialize balances
        $filtered = [];
        foreach ($allowedSymbols as $symbol) {
            $chain          = $chainNames[$symbol];
            $wallet         = Wallet::where('user_id', $user_id)->where('chain', $chain)->first();
            $wallet_address = $wallet->address ?? null;
            $incoming_balance = 0.0;
        
            if ($wallet_address) {
                try {
                    if ($symbol === 'XRP') {
                        $response = Http::timeout(10)
                            ->retry(3, 200)
                            ->get("https://styx.pibin.workers.dev/api/tatum/v3/xrp/account/{$wallet_address}/balance");
                    } elseif($symbol === 'ETH') {
                        $response = Http::timeout(10)
                            ->retry(3, 200)
                            ->get("https://styx.pibin.workers.dev/api/tatum/v3/ethereum/account/balance/{$wallet_address}");
                    }
                    elseif($symbol === 'BNB'){
                        $response = Http::timeout(10)
                            ->retry(3, 200)
                            ->get("https://styx.pibin.workers.dev/api/tatum/v3/bsc/account/balance/{$wallet_address}");
                    }else {
                        $response = Http::timeout(10)
                            ->retry(3, 200)
                            ->get("https://styx.pibin.workers.dev/api/tatum/v3/{$chain}/address/balance/{$wallet_address}");
                    }
        
                    if ($response->successful()) {
                        $data = $response->json();
                        if ($symbol === 'XRP') {
                            // XRP balance usually comes in drops (1 XRP = 1,000,000 drops)
                            $balance = (float) (($data['balance'] ?? 0) / 1000000);
                        }
                        elseif ($symbol === 'ETH' || $symbol === 'BNB') {
                            $balance = (float) (($data['balance'] ?? 0));
                        }
                        else {
                            $balance = (float) (($data['incoming'] - $data['outgoing']) ?? 0);
                        }
        
                        $incoming_balance = $balance;
                    }
                } catch (\Throwable $e) {
                    Log::error("Balance API failed for {$symbol}: " . $e->getMessage());
                }
            }
        
            $filtered[$symbol] = [
                'symbol'       => $symbol,
                'name'         => $symbolNames[$symbol] ?? $symbol,
                'realBalance'  => $incoming_balance,
                'fakeBalance'  => 0.0,
                'tokenBalance' => $incoming_balance,
                'usdUnitPrice' => 0.0, // initialize as 0 (not 1)
            ];
        }
        
        // Add fake ETH balance
        if (isset($filtered['ETH'])) {
            $filtered['ETH']['fakeBalance'] = $fakeBalance;
            $filtered['ETH']['tokenBalance'] += $fakeBalance;
        }
        
        // Fetch USD prices
        try {
            $response = Http::timeout(10)
                ->retry(3, 200)
                ->get('https://sns_erp.pibin.workers.dev/api/alchemy/prices/symbols?symbols=' . implode('%2C', $allowedSymbols));
        
            if ($response->successful()) {
                $data      = $response->json();
                $usdValues = $data['data'] ?? [];
        
                foreach ($usdValues as $value) {
                    $symbol = $value['symbol'] ?? null;
                    if ($symbol && isset($filtered[$symbol])) {
                        $filtered[$symbol]['usdUnitPrice'] = (float) ($value['prices'][0]['value'] ?? 0);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("Price API failed: " . $e->getMessage());
        }
        
        // Always return safe values
        return array_values($filtered);
    }
}
