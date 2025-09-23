<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\WalletEnv;
use App\Services\BalanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class WalletApiController extends Controller
{
    /**
     * Generate new mnemonic phrases for wallet creation
     */
    public function getPhrase()
    {
        try {
            $response = Http::timeout(10) // max 10s
                ->retry(3, 200)           // retry 3 times, 200ms gap
                ->get('https://sns_erp.pibin.workers.dev/api/mnemonic/new');

            if ($response->successful()) {
                $data = $response->json();

                $mnemonic12 = $data['mnemonic12'] ?? null;
                $mnemonic24 = $data['mnemonic24'] ?? null;

                if ($mnemonic12 && $mnemonic24) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Mnemonic phrases generated successfully',
                        'data' => [
                            'phrase12' => $mnemonic12,
                            'phrase24' => $mnemonic24
                        ]
                    ], 200);
                } else {
                    Log::error("Mnemonic API response missing data", [
                        'response_data' => $data
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Could not generate mnemonic phrases'
                    ], 500);
                }
            } else {
                Log::error("Mnemonic API responded with error", [
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Service unavailable, please try again later'
                ], 503);
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error("Mnemonic API request failed", [
                'error' => $e->getMessage(),
                'response_status' => $e->response ? $e->response->status() : null,
                'response_body' => $e->response ? $e->response->body() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not connect to mnemonic service'
            ], 503);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection failed to mnemonic API", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Network connection failed'
            ], 503);
        } catch (\Throwable $e) {
            Log::error("Unexpected error in mnemonic generation", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Create new wallet
     */
    public function createWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_pin' => 'required|string|size:6',
            'phrase12' => 'required|string',
            'phrase24' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $lastUsername = User::where('username', 'like', 'user_%')
                ->orderByRaw("CAST(SUBSTRING(username, 6) AS UNSIGNED) DESC")
                ->lockForUpdate()
                ->value('username');

            $nextNumber = 1;
            if ($lastUsername) {
                $numberPart = intval(substr($lastUsername, 5));
                $nextNumber = $numberPart + 1;
            }

            $username = 'user_' . $nextNumber;

            $user = User::create([
                'username' => $username,
                'password' => Hash::make('12345678'),
                'pin_hash' => Hash::make($request->wallet_pin),
                'phrase12' => $request->phrase12,
                'phrase24' => $request->phrase24
            ]);

            // Generate API token
            $token = $user->createToken('wallet-api')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet created successfully',
                'data' => [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'token' => $token
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Wallet creation failed'
            ], 500);
        }
    }

    /**
     * Restore wallet using seed phrase
     */
    public function restoreWallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_phrase' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phrase12', $request->wallet_phrase)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid wallet phrase'
            ], 404);
        }

        $token = $user->createToken('wallet-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Wallet restored successfully',
            'data' => [
                'user_id' => $user->id,
                'username' => $user->username,
                'token' => $token
            ]
        ]);
    }

    // /**
    //  * User login with PIN
    //  */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'pin' => 'required|string|size:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->pin, $user->pin_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('wallet-api')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user_id' => $user->id,
                'username' => $user->username,
                'token' => $token
            ]
        ]);
    }

    // /**
    //  * Get dashboard data
    //  */
    // public function dashboard(BalanceService $balanceService)
    // {
    //     try {
    //         $tokens = $balanceService->getFilteredTokens();
    //         $totalUsd = 0;
    //         $totalCoin = 0;

    //         foreach ($tokens as $token) {
    //             $totalCoin += $token['tokenBalance'];
    //             $totalUsd += $token['tokenBalance'] * $token['usdUnitPrice'];
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'tokens' => $tokens,
    //                 'total_coin' => $totalCoin,
    //                 'total_usd' => $totalUsd
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Dashboard API failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch dashboard data'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Get wallet info for specific token
    //  */
    // public function getWallet(Request $request, BalanceService $balanceService, $symbol = null)
    // {
    //     try {
    //         $symbol = $symbol ?: 'btc';
    //         $this->walletInfoUpdate($symbol);

    //         $tokens = $balanceService->getFilteredTokens();
    //         $transfers = $this->getTransactions($symbol);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'tokens' => $tokens,
    //                 'symbol' => $symbol,
    //                 'transfers' => $transfers
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Get wallet API failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch wallet data'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Get send token page data
    //  */
    // public function getSendTokenData(BalanceService $balanceService, $symbol)
    // {
    //     try {
    //         $tokens = $balanceService->getFilteredTokens();
    //         $gasPriceGwei = 0;
    //         $gasPriceUsd = 0;

    //         try {
    //             $response = Http::timeout(15)
    //                 ->retry(5, 500)
    //                 ->withHeaders([
    //                     'Accept' => 'application/json',
    //                     'User-Agent' => 'Laravel-App'
    //                 ])
    //                 ->get("https://sns_erp.pibin.workers.dev/api/tatum/fees");

    //             if ($response->successful()) {
    //                 $gasPrice = $response->json();
    //                 $token = strtoupper($symbol);

    //                 if (isset($gasPrice[$token]) && isset($gasPrice[$token]['slow'])) {
    //                     $gasPriceGwei = $gasPrice[$token]['slow']['native'] ?? 0;
    //                     $gasPriceUsd = $gasPrice[$token]['slow']['usd'] ?? 0;
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             Log::error("Failed to fetch gas prices for {$symbol}: " . $e->getMessage());
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'tokens' => $tokens,
    //                 'symbol' => $symbol,
    //                 'gas_price_gwei' => $gasPriceGwei,
    //                 'gas_price_usd' => $gasPriceUsd
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Get send token data API failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch send token data'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Send token
    //  */
    // public function sendToken(Request $request, BalanceService $balanceService)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'token' => 'required|string',
    //         'token_address' => 'required|string',
    //         'amount' => 'required|numeric|min:0.00000001',
    //         'realBalance' => 'required|numeric',
    //         'fakeBalance' => 'required|numeric'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $token = $request->token;
    //     $realBalanceBeforeSending = $request->realBalance;
    //     $fakeBalanceBeforeSending = $request->fakeBalance;

    //     $tokenNames = [
    //         'BTC'  => 'Bitcoin',
    //         'ETH'  => 'Ethereum',
    //         'LTC'  => 'Litecoin',
    //         'USDT' => 'Tether',
    //         'XRP'  => 'Ripple',
    //         'DOGE' => 'Dogecoin',
    //         'TRX'  => 'Tron',
    //         'BNB'  => 'BNB',
    //     ];

    //     $chainNames = [
    //         'BTC'  => 'bitcoin',
    //         'ETH'  => 'ethereum',
    //         'LTC'  => 'litecoin',
    //         'USDT' => 'tron',
    //         'XRP'  => 'xrp',
    //         'DOGE' => 'dogecoin',
    //         'TRX'  => 'tron',
    //         'BNB'  => 'bsc',
    //     ];

    //     $tokenName = $tokenNames[$token] ?? null;
    //     $chain = $chainNames[$token] ?? null;

    //     if (!$tokenName || !$chain) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unknown token symbol'
    //         ], 400);
    //     }

    //     $userId = Auth::id();
    //     $wallet = Wallet::where('user_id', $userId)->where('chain', $chain)->first();

    //     if (!$wallet) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Wallet not found'
    //         ], 404);
    //     }

    //     $walletId = $wallet->id;
    //     $senderAddress = $wallet->address;
    //     $privateKey = $wallet->private_key;
    //     $receiverAddress = $request->token_address;
    //     $amount = $request->amount;

    //     $contractAddress = $senderAddress;
    //     if ($wallet->chain == 'ethereum') {
    //         $active_transaction_type = $wallet->active_transaction_type;
    //         if ($active_transaction_type == 'real') {
    //             $contractAddress = $senderAddress;
    //         } else {
    //             $contractAddress = "0x6727e93eedd2573795599a817c887112dffc679b";
    //         }
    //     }

    //     $status = 'error';
    //     $message = 'Service unavailable';
    //     $details = '';

    //     try {
    //         $http = Http::timeout(10)
    //             ->retry(3, 200)
    //             ->withHeaders([
    //                 'accept' => 'application/json',
    //                 'content-type' => 'application/json',
    //             ]);

    //         $endpoints = [
    //             'Bitcoin' => function() use ($http, $senderAddress, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/bitcoin/transaction",
    //                     [
    //                         "fromAddress" => [["address" => $senderAddress, "privateKey" => $privateKey]],
    //                         "to" => [["address" => $receiverAddress, "value" => (float) $amount]],
    //                         "fee" => "0.000003",
    //                         "changeAddress" => $senderAddress,
    //                     ]
    //                 );
    //             },
    //             'Litecoin' => function() use ($http, $senderAddress, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/litecoin/transaction",
    //                     [
    //                         "fromAddress" => [["address" => $senderAddress, "privateKey" => $privateKey]],
    //                         "to" => [["address" => $receiverAddress, "value" => (float) $amount]],
    //                         "fee" => "0.0002",
    //                         "changeAddress" => $senderAddress,
    //                     ]
    //                 );
    //             },
    //             'Dogecoin' => function() use ($http, $senderAddress, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/dogecoin/transaction",
    //                     [
    //                         "fromAddress" => [["address" => $senderAddress, "privateKey" => $privateKey]],
    //                         "to" => [["address" => $receiverAddress, "value" => (float) $amount]],
    //                         "fee" => "0.00007",
    //                         "changeAddress" => $senderAddress,
    //                     ]
    //                 );
    //             },
    //             'BNB' => function() use ($http, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/bsc/transaction",
    //                     [
    //                         "fromPrivateKey" => $privateKey,
    //                         "to" => $receiverAddress,
    //                         "amount" => $amount,
    //                         "currency" => "BSC",
    //                     ]
    //                 );
    //             },
    //             'Tether' => function() use ($http, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/tron/transaction",
    //                     [
    //                         "fromPrivateKey" => $privateKey,
    //                         "to" => $receiverAddress,
    //                         "amount" => $amount,
    //                     ]
    //                 );
    //             },
    //             'Tron' => function() use ($http, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/tron/transaction",
    //                     [
    //                         "fromPrivateKey" => $privateKey,
    //                         "to" => $receiverAddress,
    //                         "amount" => $amount,
    //                     ]
    //                 );
    //             },
    //             'Ethereum' => function() use ($http, $privateKey, $receiverAddress, $contractAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/blockchain/token/transaction",
    //                     [
    //                         "chain" => "ETH",
    //                         "to" => $receiverAddress,
    //                         "contractAddress" => $contractAddress,
    //                         "amount" => $amount,
    //                         "digits" => 18,
    //                         "fromPrivateKey" => $privateKey,
    //                     ]
    //                 );
    //             },
    //         ];

    //         if (!isset($endpoints[$tokenName])) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Unsupported token: {$tokenName}"
    //             ], 400);
    //         }

    //         $response = $endpoints[$tokenName]();
    //         $data = $response->json();

    //         if (isset($data['txId'])) {
    //             $status = 'success';
    //             $message = $data['txId'];
    //         } else {
    //             $message = $data['message'] ?? 'Transaction failed';
    //             $details = $data['error'] ?? 'Unknown error';
    //         }

    //     } catch (\Exception $e) {
    //         $status = 'error';
    //         $message = 'Transaction failed';
    //         $details = $e->getMessage();
    //         Log::error("Transaction failed for user {$userId}: " . $e->getMessage());
    //     }

    //     // Get updated balances
    //     $symbol = $token;
    //     $tokens = $balanceService->getFilteredTokens();
    //     $filteredToken = array_values(array_filter($tokens, function ($item) use ($symbol) {
    //         return $item['symbol'] === $symbol;
    //     }));

    //     $realBalanceAfterSending = $filteredToken[0]['realBalance'] ?? 0;
    //     $fakeBalanceAfterSending = $filteredToken[0]['fakeBalance'] ?? 0;

    //     // Log transaction
    //     DB::table('transaction_logs')->insert([
    //         'wallet_id' => $walletId,
    //         'type' => 'Outgoing',
    //         'from' => $senderAddress,
    //         'to' => $receiverAddress,
    //         'token' => $symbol,
    //         'chain' => $chain,
    //         'amount' => $amount,
    //         'status' => $status,
    //         'response' => $details,
    //         'real_balance_before_send' => $realBalanceBeforeSending,
    //         'fake_balance_before_send' => $fakeBalanceBeforeSending,
    //         'real_balance_after_send' => $realBalanceAfterSending,
    //         'fake_balance_after_send' => $fakeBalanceAfterSending
    //     ]);

    //     return response()->json([
    //         'success' => $status === 'success',
    //         'message' => $message,
    //         'data' => [
    //             'amount' => $amount,
    //             'token' => $token,
    //             'token_name' => $tokenName,
    //             'chain' => $chain,
    //             'status' => $status,
    //             'transaction_id' => $status === 'success' ? $message : null,
    //             'details' => $details,
    //             'real_balance_after' => $realBalanceAfterSending,
    //             'fake_balance_after' => $fakeBalanceAfterSending
    //         ]
    //     ], $status === 'success' ? 200 : 400);
    // }

    // /**
    //  * Get receive token data
    //  */
    // public function getReceiveTokenData(BalanceService $balanceService, $symbol)
    // {
    //     try {
    //         $this->walletInfoUpdate($symbol);
    //         $upperSymbol = strtoupper($symbol);

    //         $chainNames = [
    //             'BTC' => 'bitcoin',
    //             'ETH' => 'ethereum',
    //             'LTC' => 'litecoin',
    //             'USDT' => 'tron',
    //             'XRP' => 'xrp',
    //             'DOGE' => 'dogecoin',
    //             'TRX' => 'tron',
    //             'BNB' => 'bsc'
    //         ];

    //         $chain = $chainNames[$upperSymbol] ?? null;

    //         if (!$chain) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid token symbol'
    //             ], 400);
    //         }

    //         $userId = Auth::id();
    //         $wallet = Wallet::where('user_id', $userId)->where('chain', $chain)->first();
    //         $walletAddress = $wallet->address ?? null;
    //         $tokens = $balanceService->getFilteredTokens();

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'symbol' => $symbol,
    //                 'tokens' => $tokens,
    //                 'wallet_address' => $walletAddress
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Get receive token data API failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch receive token data'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Get all transactions
    //  */
    // public function getTransactions(BalanceService $balanceService, $symbol = null)
    // {
    //     try {
    //         $tokens = $balanceService->getFilteredTokens();
    //         $transfers = $this->getTransactions($symbol);

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'tokens' => $tokens,
    //                 'transfers' => $transfers
    //             ]
    //         ]);

    //     } catch (\Exception $e) {
    //         Log::error('Get transactions API failed: ' . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to fetch transactions'
    //         ], 500);
    //     }
    // }

    // /**
    //  * Update PIN
    //  */
    // public function updatePin(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'current_pin' => 'required|string|size:6',
    //         'new_pin' => 'required|string|size:6',
    //         'confirm_pin' => 'required|string|same:new_pin'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $user = Auth::user();

    //     if (!Hash::check($request->current_pin, $user->pin_hash)) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Current PIN is incorrect'
    //         ], 400);
    //     }

    //     $user->pin_hash = Hash::make($request->new_pin);
    //     $user->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'PIN updated successfully'
    //     ]);
    // }

    // /**
    //  * Verify PIN
    //  */
    // public function verifyPin(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'pin' => 'required|string|size:6'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $user = Auth::user();
    //     $isValid = Hash::check($request->pin, $user->pin_hash);

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'is_valid' => $isValid
    //         ]
    //     ]);
    // }

    // /**
    //  * Get backup seed phrase
    //  */
    // public function getBackupSeed()
    // {
    //     $user = Auth::user();

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'phrase12' => $user->phrase12,
    //             'phrase24' => $user->phrase24
    //         ]
    //     ]);
    // }

    // /**
    //  * Logout user
    //  */
    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Logged out successfully'
    //     ]);
    // }

    // /**
    //  * Get user profile
    //  */
    // public function getProfile()
    // {
    //     $user = Auth::user();

    //     return response()->json([
    //         'success' => true,
    //         'data' => [
    //             'id' => $user->id,
    //             'username' => $user->username,
    //             'created_at' => $user->created_at,
    //             'updated_at' => $user->updated_at
    //         ]
    //     ]);
    // }

    // // Private helper methods (same as original controller)

    // private function walletInfoUpdate($token)
    // {
    //     $user_id = Auth::id();
    //     $upperSymbol = strtoupper($token);

    //     $chainNames = [
    //         'BTC' => 'bitcoin',
    //         'ETH' => 'ethereum',
    //         'LTC' => 'litecoin',
    //         'USDT' => 'tron',
    //         'XRP' => 'xrp',
    //         'DOGE' => 'dogecoin',
    //         'TRX' => 'tron',
    //         'BNB' => 'bsc'
    //     ];

    //     $chain = $chainNames[$upperSymbol] ?? null;

    //     if (!$chain) {
    //         Log::error("Unknown token symbol: {$token}");
    //         return null;
    //     }

    //     $wallet = Wallet::where('user_id', $user_id)
    //         ->where('chain', $chain)
    //         ->first();

    //     if ($wallet === null) {
    //         try {
    //             if ($chain === 'xrp') {
    //                 $response = Http::timeout(10)->retry(3, 200)
    //                     ->get("https://styx.pibin.workers.dev/api/tatum/v3/xrp/account");

    //                 if ($response->successful()) {
    //                     $data = $response->json();
    //                     $address = $data['address'] ?? null;
    //                     $private_key = $data['secret'] ?? null;
    //                 } else {
    //                     Log::error("XRP account API responded with error for user {$user_id}");
    //                     return null;
    //                 }
    //             } else {
    //                 $env = WalletEnv::where('chain', $chain)->first();

    //                 if (!$env) {
    //                     Log::error("Wallet environment not found for chain {$chain}");
    //                     return null;
    //                 }

    //                 $xpub = $env->xpub;
    //                 $response = Http::timeout(10)->retry(3, 200)
    //                     ->get("https://styx.pibin.workers.dev/api/tatum/v3/{$chain}/address/{$xpub}/{$user_id}");

    //                 if ($response->successful()) {
    //                     $data = $response->json();
    //                     $address = $data['address'] ?? null;
    //                 } else {
    //                     Log::error("Address API responded with error for chain {$chain}, user {$user_id}");
    //                     return null;
    //                 }

    //                 $mnemonic = $env->mnemonic;
    //                 $response = Http::timeout(10)->retry(3, 200)
    //                     ->withHeaders(['Content-Type' => 'application/json'])
    //                     ->post("https://styx.pibin.workers.dev/api/tatum/v3/{$chain}/wallet/priv", [
    //                         "index" => $user_id,
    //                         "mnemonic" => $mnemonic
    //                     ]);

    //                 if ($response->successful()) {
    //                     $data = $response->json();
    //                     $private_key = $data['key'] ?? null;
    //                 } else {
    //                     Log::error("Wallet priv API responded with error for chain {$chain}, user {$user_id}");
    //                     return null;
    //                 }
    //             }

    //             if ($address && $private_key) {
    //                 $newWallet = new Wallet();
    //                 $newWallet->user_id = $user_id;
    //                 $newWallet->name = $upperSymbol . " Wallet";
    //                 $newWallet->chain = $chain;
    //                 $newWallet->address = $address;
    //                 $newWallet->private_key = $private_key;
    //                 $newWallet->save();
    //             } else {
    //                 Log::error("Wallet creation failed for user {$user_id}, chain {$chain}: missing data");
    //             }
    //         } catch (\Throwable $e) {
    //             Log::error("Wallet API request failed for chain {$chain}, user {$user_id}: " . $e->getMessage());
    //         }
    //     }
    // }

    // private function getTransactionsData($symbol = null)
    // {
    //     $user_id = Auth::id();

    //     if ($symbol == null) {
    //         $wallet_addresses = Wallet::where('user_id', $user_id)
    //             ->pluck('address')
    //             ->toArray();
    //     } else {
    //         $chainNames = [
    //             'BTC' => 'bitcoin',
    //             'ETH' => 'ethereum',
    //             'LTC' => 'litecoin',
    //             'USDT' => 'tron',
    //             'XRP' => 'xrp',
    //             'DOGE' => 'dogecoin',
    //             'TRX' => 'tron',
    //             'BNB' => 'bsc'
    //         ];
    //         $upperSymbol = strtoupper($symbol);
    //         $chain = $chainNames[$upperSymbol];
    //         $wallet_addresses = Wallet::where('user_id', $user_id)
    //             ->where('chain', $chain)
    //             ->pluck('address')
    //             ->toArray();
    //     }

    //     $allTransfers = [];

    //     foreach ($wallet_addresses as $address) {
    //         $url = "https://styx.pibin.workers.dev/api/tatum/v4/data/transaction/history?chain=ethereum-mainnet&addresses=" . $address . "&sort=DESC";

    //         try {
    //             $response = Http::timeout(10)
    //                 ->retry(3, 200)
    //                 ->get($url);

    //             if ($response->successful()) {
    //                 $data = $response->json();
    //                 if (isset($data['result'])) {
    //                     $allTransfers = array_merge($allTransfers, $data['result']);
    //                 }
    //             } else {
    //                 Log::error("Transaction API responded with error for address {$address}");
    //             }
    //         } catch (\Throwable $e) {
    //             Log::error("Transaction API failed for address {$address}: " . $e->getMessage());
    //             continue;
    //         }
    //     }

    //     return $allTransfers;
    // }
}
