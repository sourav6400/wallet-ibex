<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetworkFeeEstimator
{
    private const SAFETY_MULTIPLIER = 1.5;

    private const EVM_GAS_LIMIT = 21000;

    private const UTXO_VBYTES = 250;

    private const TATUM_FEE_URL = 'https://api.tatum.io/v3/blockchain/fee';

    private const TATUM_RATE_URL = 'https://api.tatum.io/v4/data/rate/symbol';

    /**
     * @return array{fee_native: float, fee_usd: float, fee_symbol: string}
     */
    public function estimateForSendScreen(string $symbol): array
    {
        $symbol = strtolower($symbol);
        $feeNative = 0.0;
        $feeSymbol = strtoupper($symbol);
        $rateSymbol = $feeSymbol;

        if (in_array($symbol, ['bnb', 'trx', 'doge', 'xrp'], true)) {
            $feeNative = $this->hardcodedNativeFee($symbol);
        } else {
            $chain = $this->symbolToTatumFeeChain($symbol);
            if ($chain !== null) {
                $fast = $this->fetchFastFee($chain);
                if ($fast !== null) {
                    $feeNative = $this->totalNativeFee($chain, $fast);
                }
            }

            if ($symbol === 'usdt') {
                $feeSymbol = 'ETH';
                $rateSymbol = 'ETH';
            }
        }

        $feeNative *= self::SAFETY_MULTIPLIER;
        $feeUsd = $feeNative * $this->fetchUsdRate($rateSymbol);

        return [
            'fee_native' => $feeNative,
            'fee_usd' => $feeUsd,
            'fee_symbol' => $feeSymbol,
        ];
    }

    /**
     * @return array{gasPrice: string, gasLimit: string, maxFeePerGas: string, maxPriorityFeePerGas: string}|null
     */
    public function estimateEthereumBroadcastGas(): ?array
    {
        $fastWei = $this->fetchFastFee('ETH');
        if ($fastWei === null) {
            return null;
        }

        $gasPriceWei = (int) $fastWei;
        $maxFeePerGasWei = (int) ($gasPriceWei * 3.0);
        $maxPriorityFeePerGasWei = (int) ($gasPriceWei * 0.8);

        return [
            'gasPrice' => (string) $gasPriceWei,
            'gasLimit' => '200000',
            'maxFeePerGas' => (string) $maxFeePerGasWei,
            'maxPriorityFeePerGas' => (string) $maxPriorityFeePerGasWei,
        ];
    }

    /**
     * @return array{gasPrice: string, gasLimit: string, maxFeePerGas: string, maxPriorityFeePerGas: string}
     */
    public function ethereumBroadcastGasFallback(): array
    {
        return [
            'gasPrice' => '50000000000',
            'gasLimit' => '150000',
            'maxFeePerGas' => '75000000000',
            'maxPriorityFeePerGas' => '10000000000',
        ];
    }

    private function hardcodedNativeFee(string $symbol): float
    {
        return match ($symbol) {
            'bnb' => 0.00001,
            'trx' => 1.00,
            'doge' => 1.58,
            'xrp' => 0.000015,
            default => 0.0,
        };
    }

    private function symbolToTatumFeeChain(string $symbol): ?string
    {
        return match ($symbol) {
            'eth' => 'ETH',
            'btc' => 'BTC',
            'ltc' => 'LTC',
            'usdt' => 'ETH',
            default => null,
        };
    }

    private function fetchFastFee(string $chain): ?float
    {
        try {
            $response = Http::timeout(15)
                ->retry(3, 500)
                ->withHeaders($this->tatumHeaders())
                ->get(self::TATUM_FEE_URL.'/'.$chain);

            if (! $response->successful()) {
                Log::warning('Tatum blockchain fee request failed', [
                    'chain' => $chain,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $data = $response->json();
            $fast = $data['fast'] ?? null;

            return is_numeric($fast) ? (float) $fast : null;
        } catch (\Throwable $e) {
            Log::warning('Tatum blockchain fee request error', [
                'chain' => $chain,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function totalNativeFee(string $chain, float $fast): float
    {
        if ($chain === 'ETH') {
            return ($fast * self::EVM_GAS_LIMIT) / 1e18;
        }

        return ($fast * self::UTXO_VBYTES) / 1e8;
    }

    private function fetchUsdRate(string $symbol): float
    {
        try {
            $response = Http::timeout(10)
                ->retry(3, 200)
                ->withHeaders($this->tatumHeaders())
                ->get(self::TATUM_RATE_URL, [
                    'symbol' => strtoupper($symbol),
                    'basePair' => 'USD',
                ]);

            if ($response->successful()) {
                $value = $response->json()['value'] ?? 0;

                return is_numeric($value) ? (float) $value : 0.0;
            }
        } catch (\Throwable $e) {
            Log::warning('Tatum rate request failed', [
                'symbol' => $symbol,
                'error' => $e->getMessage(),
            ]);
        }

        return 0.0;
    }

    /**
     * @return array<string, string>
     */
    private function tatumHeaders(): array
    {
        return [
            'accept' => 'application/json',
            'x-api-key' => config('tatum.x-api-key'),
        ];
    }
}
