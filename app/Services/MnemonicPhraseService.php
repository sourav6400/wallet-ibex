<?php

namespace App\Services;

use App\Models\User;
use App\Support\ExternalApiEndpoints;
use FurqanSiddiqui\BIP39\BIP39;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MnemonicPhraseService
{
    private const MAX_UNIQUE_ATTEMPTS = 15;

    /**
     * @return array{mnemonic12: string, mnemonic24: string}|null
     */
    public function resolvePair(): ?array
    {
        $fromApi = $this->fetchFromApi();
        if ($fromApi !== null) {
            return $fromApi;
        }

        $fromLocal = $this->generateLocalUniquePair();
        if ($fromLocal !== null) {
            Log::warning('Mnemonic API unavailable or unusable; generated phrases locally.');

            return $fromLocal;
        }

        return null;
    }

    /**
     * @return array{mnemonic12: string, mnemonic24: string}|null
     */
    public function fetchFromApi(): ?array
    {
        try {
            $response = Http::timeout(10)
                ->retry(3, 200)
                ->get(ExternalApiEndpoints::mnemonicNew());

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $mnemonic12 = $this->normalizePhrase($data['mnemonic12'] ?? null);
            $mnemonic24 = $this->normalizePhrase($data['mnemonic24'] ?? null);

            if (! $this->isValidPair($mnemonic12, $mnemonic24)) {
                return null;
            }

            if ($this->isPairAvailable($mnemonic12, $mnemonic24)) {
                return [
                    'mnemonic12' => $mnemonic12,
                    'mnemonic24' => $mnemonic24,
                ];
            }

            Log::warning('Mnemonic API returned phrases already assigned to a user.');
        } catch (\Throwable $e) {
            Log::warning('Mnemonic API request failed', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @return array{mnemonic12: string, mnemonic24: string}|null
     */
    public function generateLocalUniquePair(): ?array
    {
        for ($attempt = 0; $attempt < self::MAX_UNIQUE_ATTEMPTS; $attempt++) {
            try {
                $mnemonic12 = $this->normalizePhrase(implode(' ', BIP39::Generate(12)->words));
                $mnemonic24 = $this->normalizePhrase(implode(' ', BIP39::Generate(24)->words));

                if (
                    $this->isValidPair($mnemonic12, $mnemonic24)
                    && $this->isPairAvailable($mnemonic12, $mnemonic24)
                ) {
                    return [
                        'mnemonic12' => $mnemonic12,
                        'mnemonic24' => $mnemonic24,
                    ];
                }
            } catch (\Throwable $e) {
                Log::error('Local mnemonic generation failed', ['error' => $e->getMessage()]);

                return null;
            }
        }

        return null;
    }

    public function normalizePhrase(?string $phrase): ?string
    {
        if ($phrase === null || trim($phrase) === '') {
            return null;
        }

        $words = preg_split('/\s+/', strtolower(trim($phrase)));

        return implode(' ', $words);
    }

    public function isPhraseTaken(string $phrase): bool
    {
        $normalized = $this->normalizePhrase($phrase);

        if ($normalized === null) {
            return true;
        }

        return User::query()
            ->where('phrase12', $normalized)
            ->orWhere('phrase24', $normalized)
            ->exists();
    }

    private function isValidPair(?string $mnemonic12, ?string $mnemonic24): bool
    {
        if ($mnemonic12 === null || $mnemonic24 === null) {
            return false;
        }

        if ($mnemonic12 === $mnemonic24) {
            return false;
        }

        return count(explode(' ', $mnemonic12)) === 12
            && count(explode(' ', $mnemonic24)) === 24;
    }

    private function isPairAvailable(string $mnemonic12, string $mnemonic24): bool
    {
        return ! $this->isPhraseTaken($mnemonic12) && ! $this->isPhraseTaken($mnemonic24);
    }
}
