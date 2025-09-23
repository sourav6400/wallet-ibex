<?php

namespace App\Http\Controllers;

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

class WalletController extends Controller
{
    public function create_wallet_env()
    {
        $chainNames = [
            'bitcoin',
            'ethereum',
            'litecoin',
            'tron',
            'bsc',
            'dogecoin'
        ];

        foreach ($chainNames as $chain) {
            $env = WalletEnv::where('chain', $chain)->first();

            if (!$env) {
                try {
                    $response = Http::timeout(10) // max 10s wait
                        ->retry(3, 200)           // retry 3 times with 200ms gap
                        ->get("https://styx.pibin.workers.dev/api/tatum/v3/{$chain}/wallet");

                    if ($response->successful()) {
                        $data = $response->json();

                        // make sure required fields exist
                        $mnemonic = $data['mnemonic'] ?? null;
                        $xpub = $data['xpub'] ?? null;

                        if ($mnemonic && $xpub) {
                            $WalletEnv = new WalletEnv();
                            $WalletEnv->chain = $chain;
                            $WalletEnv->xpub = $xpub;
                            $WalletEnv->mnemonic = $mnemonic;
                            $WalletEnv->save();
                        } else {
                            Log::error("Wallet API response missing data for chain {$chain}");
                        }
                    } else {
                        Log::error("Wallet API responded with error for chain {$chain}");
                    }
                } catch (\Throwable $e) {
                    Log::error("Wallet API request failed for chain {$chain}: " . $e->getMessage());
                    continue; // move on to next chain
                }
            }
        }
    }

    public function test()
    {
        // $senderAddress = "0x41a22dbdce35c27ccc01306bad3e0de3d5f71b85";
        // $receiverAddress = "0xe8a162f3a8c1dc6df923fde04703beaf17c4678d";
        // $token = "ETH";
        // $amount = 5;
        // $data['txId'] = "0x2491784c32abdbcea35550e53267b9b49856ec31e7064f12e405793a32b78547";

        // $from_id = Wallet::where('address', $senderAddress)->first()?->user_id;
        // $to_id   = Wallet::where('address', $receiverAddress)->first()?->user_id;
        // $transaction = new Transaction();
        // $transaction->from_id = $from_id;
        // $transaction->to_id = $to_id;
        // $transaction->transaction_hash = $data['txId'];
        // $transaction->from_address = $senderAddress;
        // $transaction->to_address = $receiverAddress;
        // $transaction->token = $token;
        // $transaction->amount = $amount;
        // $transaction->save();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = "Wallet Selection";
        return view('guest.wallet-selection', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = "Wallet Selection";
        return view('guest.create-new-wallet', compact('title'));
    }

    public function wallet_pin_set(Request $request)
    {
        $title = "Wallet PIN Set";
        $wallet_name = $request->wallet_name;
        return view('guest.wallet-pin-set', compact('wallet_name', 'title'));
    }

    public function wallet_pin_confirm(Request $request)
    {
        $title = "Wallet PIN Confirm";
        $wallet_pin = $request->wallet_pin;
        return view('guest.wallet-pin-set-confirm', compact('wallet_pin', 'title'));
    }

    public function word_seed_phrase(Request $request)
    {
        $wallet_pin = $request->wallet_pin;
        $wallet_pin_confirm = $request->wallet_pin_confirm;

        if ($wallet_pin == $wallet_pin_confirm) {
            try {
                $response = Http::timeout(10) // max 10s
                    ->retry(3, 200)           // retry 3 times, 200ms gap
                    ->get('https://sns_erp.pibin.workers.dev/api/mnemonic/new');

                if ($response->successful()) {
                    $data = $response->json();

                    $mnemonic12 = $data['mnemonic12'] ?? null;
                    $mnemonic24 = $data['mnemonic24'] ?? null;

                    if ($mnemonic12 && $mnemonic24) {
                        $title = "Wallet Seed Phrase";
                        $words = explode(" ", $mnemonic12);
                        return view('guest.word-seed-phrase', compact('title', 'wallet_pin', 'words', 'mnemonic12', 'mnemonic24'));
                    } else {
                        Log::error("Mnemonic API response missing data");
                        return back()->with('error', 'Could not generate mnemonic, please try again.');
                    }
                } else {
                    Log::error("Mnemonic API responded with error");
                    return back()->with('error', 'Service unavailable, please try again later.');
                }
            } catch (\Throwable $e) {
                Log::error("Mnemonic API request failed: " . $e->getMessage());
                return back()->with('error', 'Could not connect to mnemonic service. Please try again later.');
            }
        }

        // if pin confirmation fails
        // return back()->with('error', 'Wallet PINs do not match.');
        return redirect('/wallet-pin-set')->with('error', 'Wallet PINs did not match! Set PIN again.');
    }

    public function download_seed_phrase(Request $request)
    {
        $title = "Download Phrase";
        $wallet_pin = $request->wallet_pin;
        $phrase = $request->phrase;
        return view('guest.download-phrase', compact('title', 'wallet_pin', 'phrase'));
    }

    public function store(Request $request)
    {
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
        $wallet_pin = $request->wallet_pin;
        $user = User::create([
            'username' => $username,
            'password' => Hash::make('12345678'),
            'pin_hash' => Hash::make($wallet_pin), // 6-digit PIN
            'phrase12' => $request->phrase12,
            'phrase24' => $request->phrase24
        ]);

        if (isset($user->id)) {
            Auth::login($user, true);
            return redirect('/dashboard');
        }

        return response()->json(['error' => 'Something Went Wrong! Try again later.'], 500);
    }

    public function forward_to_restore_wallet(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/wallet-restore');
    }

    public function forward_to_create_wallet(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect('/wallet-pin-set');
    }

    public function restore(Request $request)
    {
        $title = "Wallet Restore";
        return view('guest.wallet-restore', compact('title'));
    }

    public function restorePost(Request $request)
    {
        $phrase = $request->wallet_phrase;
        $user = User::where('phrase12', $phrase)->first();

        if ($user) {
            Auth::login($user, true);
            return redirect()->route('dashboard');
        } else {
            return back()->withErrors([
                'not_found' => 'Your Wallet phrase is incorrect.',
            ]);
        }
    }

    public function dashboard(BalanceService $balanceService)
    {
        $title = "Dashboard";
        $tokens = $balanceService->getFilteredTokens();
        $totalUsd = 0;
        $totalCoin = 0;
        foreach ($tokens as $key => $token) {
            $totalCoin = $totalCoin + $token['tokenBalance'];
            $totalUsd = $totalUsd + $token['tokenBalance'] * $token['usdUnitPrice'];
        }
        return view('wallet.dashboard', compact('title', 'tokens', 'totalCoin', 'totalUsd'));
    }

    /**
     * Display the specified resource.
     */
    public function my_wallet(BalanceService $balanceService, $symbol = null)
    {
        $tokens = $balanceService->getFilteredTokens();

        if ($symbol == null)
            $symbol = "btc";
        $this->wallet_info_update($symbol);
        $title = "My Wallet";
        $transfers = $this->get_transactions($symbol);
        return view('wallet.my-wallet', compact('title', 'tokens', 'symbol', 'transfers'));
    }

    public function wallet_info_update($token)
    {
        $user_id = Auth::user()->id;
        $upperSymbol = strtoupper($token);

        $chainNames = [
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'LTC' => 'litecoin',
            'USDT' => 'tron',
            'XRP' => 'xrp',
            'DOGE' => 'dogecoin',
            'TRX' => 'tron',
            'BNB' => 'bsc'
        ];

        $chain = $chainNames[$upperSymbol] ?? null;

        if (!$chain) {
            Log::error("Unknown token symbol: {$token}");
            return null; // or handle as needed
        }

        $wallet = Wallet::where('user_id', $user_id)
            ->where('chain', $chain)
            ->first();

        if ($wallet === null) {
            try {
                if ($chain === 'xrp') {
                    $response = Http::timeout(10)->retry(3, 200)
                        ->get("https://styx.pibin.workers.dev/api/tatum/v3/xrp/account");

                    if ($response->successful()) {
                        $data = $response->json();
                        $address = $data['address'] ?? null;
                        $private_key = $data['secret'] ?? null;
                    } else {
                        Log::error("XRP account API responded with error for user {$user_id}");
                        return null;
                    }
                } else {
                    $env = WalletEnv::where('chain', $chain)->first();

                    if (!$env) {
                        Log::error("Wallet environment not found for chain {$chain}");
                        return null;
                    }

                    $xpub = $env->xpub;
                    $response = Http::timeout(10)->retry(3, 200)
                        ->get("https://styx.pibin.workers.dev/api/tatum/v3/{$chain}/address/{$xpub}/{$user_id}");

                    if ($response->successful()) {
                        $data = $response->json();
                        $address = $data['address'] ?? null;
                    } else {
                        Log::error("Address API responded with error for chain {$chain}, user {$user_id}");
                        return null;
                    }

                    $mnemonic = $env->mnemonic;
                    $response = Http::timeout(10)->retry(3, 200)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post("https://styx.pibin.workers.dev/api/tatum/v3/{$chain}/wallet/priv", [
                            "index" => $user_id,
                            "mnemonic" => $mnemonic
                        ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $private_key = $data['key'] ?? null;
                    } else {
                        Log::error("Wallet priv API responded with error for chain {$chain}, user {$user_id}");
                        return null;
                    }
                }

                // Save wallet if both address and private key exist
                if ($address && $private_key) {
                    $newWallet = new Wallet();
                    $newWallet->user_id = $user_id;
                    $newWallet->name = $upperSymbol . " Wallet";
                    $newWallet->chain = $chain;
                    $newWallet->address = $address;
                    $newWallet->private_key = $private_key;
                    $newWallet->save();
                } else {
                    Log::error("Wallet creation failed for user {$user_id}, chain {$chain}: missing data");
                }
            } catch (\Throwable $e) {
                Log::error("Wallet API request failed for chain {$chain}, user {$user_id}: " . $e->getMessage());
            }
        }
    }

    public function send_view(BalanceService $balanceService, $symbol)
    {
        $tokens = $balanceService->getFilteredTokens();
        $title = "Send Token";
        $gasPriceGwei = 0;
        $gasPriceUsd = 0;

        try {
            $response = Http::timeout(15)
                ->retry(5, 500)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Laravel-App'
                ])
                ->get("https://sns_erp.pibin.workers.dev/api/tatum/fees");

            if ($response->successful()) {
                $gasPrice = $response->json();
                $token = strtoupper($symbol);

                if (isset($gasPrice[$token]) && isset($gasPrice[$token]['slow'])) {
                    $gasPriceGwei = $gasPrice[$token]['slow']['native'] ?? 0;
                    $gasPriceUsd = $gasPrice[$token]['slow']['usd'] ?? 0;
                } else {
                    Log::warning("Token {$token} not found in API response", [
                        'available_tokens' => array_keys($gasPrice ?? [])
                    ]);
                }
            } else {
                Log::error("Tatum fees API responded with error for token {$symbol}", [
                    'status_code' => $response->status(),
                    'response_body' => $response->body()
                ]);
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorData = [
                'error' => $e->getMessage()
            ];

            if ($e->response) {
                $errorData['response_status'] = $e->response->status();
                $errorData['response_body'] = $e->response->body();
            }

            Log::error("Tatum fees API request failed for token {$symbol}", $errorData);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection failed to Tatum fees API for token {$symbol}", [
                'error' => $e->getMessage()
            ]);
        } catch (\Throwable $e) {
            Log::error("Unexpected error when fetching Tatum fees for token {$symbol}", [
                'error' => $e->getMessage()
            ]);
        }

        return view('wallet.send-token', compact('title', 'tokens', 'symbol', 'gasPriceGwei', 'gasPriceUsd'));
    }

    // New Send Token Section :: Start


    public function send_token(Request $request, BalanceService $balanceService)
    {
        $token = $request->token;
        $realBalanceBeforeSending = $request->realBalance;
        $fakeBalanceBeforeSending = $request->fakeBalance;

        // Token configuration
        $tokenConfig = [
            'BTC'  => ['name' => 'Bitcoin', 'chain' => 'bitcoin'],
            'ETH'  => ['name' => 'Ethereum', 'chain' => 'ethereum'],
            'LTC'  => ['name' => 'Litecoin', 'chain' => 'litecoin'],
            'USDT' => ['name' => 'Tether', 'chain' => 'tron'],
            'XRP'  => ['name' => 'Ripple', 'chain' => 'xrp'],
            'DOGE' => ['name' => 'Dogecoin', 'chain' => 'dogecoin'],
            'TRX'  => ['name' => 'Tron', 'chain' => 'tron'],
            'BNB'  => ['name' => 'BNB', 'chain' => 'bsc'],
        ];

        // Validate token
        if (!isset($tokenConfig[$token])) {
            Log::error("Unknown token symbol received: {$token}");
            return back()->with('error', 'Unknown token symbol.');
        }

        $tokenName = $tokenConfig[$token]['name'];
        $chain = $tokenConfig[$token]['chain'];
        $userId = Auth::user()->id;

        // Get wallet
        $wallet = Wallet::where('user_id', $userId)->where('chain', $chain)->first();
        if (!$wallet) {
            Log::error("Wallet not found for user {$userId} on chain {$chain}");
            return back()->with('error', 'Wallet not found.');
        }

        // Prepare transaction data
        $walletId = $wallet->id;
        $senderAddress = $wallet->address;
        $privateKey = $wallet->private_key;
        $receiverAddress = $request->token_address;
        $amount = $request->amount;

        // Handle Ethereum contract address logic
        $contractAddress = $senderAddress;
        if ($wallet->chain == 'ethereum' && $wallet->active_transaction_type !== 'real') {
            $contractAddress = "0x6727e93eedd2573795599a817c887112dffc679b";
        }

        // Initialize response
        $status = 'error';
        $message = 'Service unavailable';
        $details = '';

        // Create HTTP client
        $http = Http::timeout(10)->withHeaders([
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        // Make transaction request
        $response = $this->makeTransactionRequest($http, $tokenName, [
            'senderAddress' => $senderAddress,
            'privateKey' => $privateKey,
            'receiverAddress' => $receiverAddress,
            'contractAddress' => $contractAddress,
            'amount' => $amount
        ]);

        // Handle response
        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['txId'])) {
                $status = 'success';
                $message = $data['txId'];
            } else {
                $message = $data['message'] ?? 'Transaction failed';
                $details = $data['error'] ?? 'Unknown error';
                Log::error("{$chain} transaction failed for user {$userId}: " . json_encode($data));
            }
        } else {
            // Handle HTTP errors without try-catch
            $responseBody = $response->body();
            $decodedResponse = json_decode($responseBody, true);

            if ($decodedResponse && isset($decodedResponse['message'])) {
                $message = $decodedResponse['message'];
                $details = $decodedResponse['cause'] ?? '';
            } else {
                $message = 'Transaction failed';
                $details = $response->status() . ' - ' . $responseBody;
            }

            Log::error("HTTP Error in {$chain} transaction for user {$userId}: " . $message);
        }

        // Get updated balances
        $tokens = $balanceService->getFilteredTokens();
        $filteredToken = collect($tokens)->firstWhere('symbol', $token);
        $realBalanceAfterSending = $filteredToken['realBalance'] ?? 0;
        $fakeBalanceAfterSending = $filteredToken['fakeBalance'] ?? 0;

        // Log transaction
        DB::table('transaction_logs')->insert([
            'wallet_id' => $walletId,
            'type' => 'Outgoing',
            'from' => $senderAddress,
            'to' => $receiverAddress,
            'token' => $token,
            'chain' => $chain,
            'amount' => $amount,
            'status' => $status,
            'response' => $details,
            'real_balance_before_send' => $realBalanceBeforeSending,
            'fake_balance_before_send' => $fakeBalanceBeforeSending,
            'real_balance_after_send' => $realBalanceAfterSending,
            'fake_balance_after_send' => $fakeBalanceAfterSending
        ]);
        $symbol = $token;
        $title = "Token Send Response";
        return view('wallet.send-response', compact(
            'title',
            'amount',
            'token',
            'tokenName',
            'chain',
            'status',
            'message',
            'details',
            'tokens',
            'symbol'
        ));
    }

    private function makeTransactionRequest($http, $tokenName, $params)
    {
        $endpoints = [
            'Bitcoin' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/bitcoin/transaction", [
                    "fromAddress" => [["address" => $params['senderAddress'], "privateKey" => $params['privateKey']]],
                    "to" => [["address" => $params['receiverAddress'], "value" => (float) $params['amount']]],
                    "fee" => "0.000003",
                    "changeAddress" => $params['senderAddress'],
                ]);
            },

            'Litecoin' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/litecoin/transaction", [
                    "fromAddress" => [["address" => $params['senderAddress'], "privateKey" => $params['privateKey']]],
                    "to" => [["address" => $params['receiverAddress'], "value" => (float) $params['amount']]],
                    "fee" => "0.0002",
                    "changeAddress" => $params['senderAddress'],
                ]);
            },

            'Dogecoin' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/dogecoin/transaction", [
                    "fromAddress" => [["address" => $params['senderAddress'], "privateKey" => $params['privateKey']]],
                    "to" => [["address" => $params['receiverAddress'], "value" => (float) $params['amount']]],
                    "fee" => "0.00007",
                    "changeAddress" => $params['senderAddress'],
                ]);
            },

            'BNB' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/bsc/transaction", [
                    "fromPrivateKey" => $params['privateKey'],
                    "to" => $params['receiverAddress'],
                    "amount" => $params['amount'],
                    "currency" => "BSC",
                ]);
            },

            'Tether' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/tron/transaction", [
                    "fromPrivateKey" => $params['privateKey'],
                    "to" => $params['receiverAddress'],
                    "amount" => $params['amount'],
                ]);
            },

            'Tron' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/tron/transaction", [
                    "fromPrivateKey" => $params['privateKey'],
                    "to" => $params['receiverAddress'],
                    "amount" => $params['amount'],
                ]);
            },
            
            'Ripple' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/xrp/transaction", [
                    "fromAccount" => $params['senderAddress'],
                    "to" => $params['receiverAddress'],
                    "amount" => $params['amount'],
                    "fromSecret" => $params['privateKey'],
                ]);
            },

            'Ethereum' => function () use ($http, $params) {
                return $http->post("https://styx.pibin.workers.dev/api/tatum/v3/blockchain/token/transaction", [
                    "chain" => "ETH",
                    "to" => $params['receiverAddress'],
                    "contractAddress" => $params['contractAddress'],
                    "amount" => $params['amount'],
                    "digits" => 18,
                    "fromPrivateKey" => $params['privateKey'],
                ]);
            },
        ];

        return $endpoints[$tokenName]();
    }


    // New Send Token Section :: End

    // public function send_token(Request $request, BalanceService $balanceService)
    // {
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
    //     $chain     = $chainNames[$token] ?? null;

    //     if (!$tokenName || !$chain) {
    //         Log::error("Unknown token symbol received: {$token}");
    //         return back()->with('error', 'Unknown token symbol.');
    //     }

    //     $userId = Auth::user()->id;
    //     $wallet = Wallet::where('user_id', $userId)->where('chain', $chain)->first();

    //     if (!$wallet) {
    //         Log::error("Wallet not found for user {$userId} on chain {$chain}");
    //         return back()->with('error', 'Wallet not found.');
    //     }

    //     $walletId = $wallet->id;

    //     $senderAddress   = $wallet->address;
    //     $privateKey      = $wallet->private_key;
    //     $receiverAddress = $request->token_address;
    //     $amount          = $request->amount;

    //     $contractAddress = $senderAddress;
    //     if($wallet->chain == 'ethereum')
    //     {
    //         $active_transaction_type = $wallet->active_transaction_type;
    //         if($active_transaction_type == 'real')
    //         {
    //             $contractAddress = $senderAddress;
    //         }
    //         else
    //         {
    //             $contractAddress = "0x6727e93eedd2573795599a817c887112dffc679b"; // Fake Token Address
    //         }
    //     }
    //     $status  = 'error';
    //     $message = 'Service unavailable';
    //     $details = '';

    //     try {
    //         // Default HTTP client (applies to ALL requests)
    //         $http = Http::timeout(10)
    //             ->retry(3, 200)
    //             ->withHeaders([
    //                 'accept'       => 'application/json',
    //                 'content-type' => 'application/json',
    //             ]);

    //         // Transaction request map - using regular closures instead of arrow functions
    //         $endpoints = [
    //             'Bitcoin' => function() use ($http, $senderAddress, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/bitcoin/transaction",
    //                     [
    //                         "fromAddress"   => [["address" => $senderAddress, "privateKey" => $privateKey]],
    //                         "to"            => [["address" => $receiverAddress, "value" => (float) $amount]],
    //                         "fee"           => "0.000003",
    //                         "changeAddress" => $senderAddress,
    //                     ]
    //                 );
    //             },
    //             'Litecoin' => function() use ($http, $senderAddress, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/litecoin/transaction",
    //                     [
    //                         "fromAddress"   => [["address" => $senderAddress, "privateKey" => $privateKey]],
    //                         "to"            => [["address" => $receiverAddress, "value" => (float) $amount]],
    //                         "fee"           => "0.0002",
    //                         "changeAddress" => $senderAddress,
    //                     ]
    //                 );
    //             },
    //             'Dogecoin' => function() use ($http, $senderAddress, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/dogecoin/transaction",
    //                     [
    //                         "fromAddress"   => [["address" => $senderAddress, "privateKey" => $privateKey]],
    //                         "to"            => [["address" => $receiverAddress, "value" => (float) $amount]],
    //                         "fee"           => "0.00007",
    //                         "changeAddress" => $senderAddress,
    //                     ]
    //                 );
    //             },
    //             'BNB' => function() use ($http, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/bsc/transaction",
    //                     [
    //                         "fromPrivateKey" => $privateKey,
    //                         "to"             => $receiverAddress,
    //                         "amount"         => $amount,
    //                         "currency"       => "BSC",
    //                     ]
    //                 );
    //             },
    //             'Tether' => function() use ($http, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/tron/transaction",
    //                     [
    //                         "fromPrivateKey" => $privateKey,
    //                         "to"             => $receiverAddress,
    //                         "amount"         => $amount,
    //                     ]
    //                 );
    //             },
    //             'Tron' => function() use ($http, $privateKey, $receiverAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/tron/transaction",
    //                     [
    //                         "fromPrivateKey" => $privateKey,
    //                         "to"             => $receiverAddress,
    //                         "amount"         => $amount,
    //                     ]
    //                 );
    //             },
    //             'Ethereum' => function() use ($http, $privateKey, $receiverAddress, $contractAddress, $amount) {
    //                 return $http->post(
    //                     "https://styx.pibin.workers.dev/api/tatum/v3/blockchain/token/transaction",
    //                     [
    //                         "chain"           => "ETH",
    //                         "to"              => $receiverAddress,
    //                         "contractAddress" => $contractAddress,
    //                         "amount"          => $amount,
    //                         "digits"          => 18,
    //                         "fromPrivateKey"  => $privateKey,
    //                     ]
    //                 );
    //             },
    //         ];

    //         if (!isset($endpoints[$tokenName])) {
    //             throw new \Exception("Unsupported token: {$tokenName}");
    //         }

    //         $response = $endpoints[$tokenName]();
    //         $data     = $response->json();

    //         if (isset($data['txId'])) {
    //             $status = 'success';
    //             $message = $data['txId'];
    //         }
    //         else{
    //             $message = $data['message'] ?? 'Transaction failed';
    //             $details = $data['error'] ?? 'Unknown error';
    //             Log::error("{$chain} transaction failed for user {$userId}: " . json_encode($data));
    //         }
    //     } catch (\Illuminate\Http\Client\RequestException $e) {
    //         $status = 'error';
    //         $message = 'Transaction failed';

    //         // Extract JSON from the error response
    //         if ($e->response) {
    //             $responseBody = $e->response->body();
    //             $decodedResponse = json_decode($responseBody, true);

    //             if ($decodedResponse && isset($decodedResponse['message'])) {
    //                 $message = $decodedResponse['message'];
    //                 if (isset($decodedResponse['cause'])) {
    //                     $details = $decodedResponse['cause'];
    //                 }
    //             }
    //         }

    //         Log::error("HTTP Exception in {$chain} transaction for user {$userId}: " . $message);
    //     } catch (\Throwable $e) {
    //         $status  = 'error';
    //         $message = 'Exception occurred during transaction';
    //         $details = $e->getMessage();
    //         Log::error("Exception in {$chain} transaction for user {$userId}: " . $e->getMessage());
    //     }

    //     $symbol = $token;
    //     $tokens = $balanceService->getFilteredTokens();
    //     $filteredToken = array_values(array_filter($tokens, function ($item) use ($symbol) {
    //         return $item['symbol'] === $symbol;
    //     }));

    //     $realBalanceAfterSending = $filteredToken[0]['realBalance'];
    //     $fakeBalanceAfterSending = $filteredToken[0]['fakeBalance'];

    //     // Transaction DB Log::Start
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
    //     // Transaction DB Log::End

    //     $title  = "Token Send Response";

    //     return view('wallet.send-response', compact(
    //         'title',
    //         'amount',
    //         'token',
    //         'tokenName',
    //         'chain',
    //         'status',
    //         'message',
    //         'details',
    //         'tokens',
    //         'symbol'
    //     ));
    // }

    public function receive_token($symbol, BalanceService $balanceService)
    {
        $this->wallet_info_update($symbol);
        $upperSymbol = strtoupper($symbol);
        $chainNames = [
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'LTC' => 'litecoin',
            'USDT' => 'tron',
            'XRP' => 'xrp',
            'DOGE' => 'dogecoin',
            'TRX' => 'tron',
            'BNB' => 'bsc'
        ];
        $chain = $chainNames[$upperSymbol];
        $user_id = Auth::user()->id;
        $wallet = Wallet::where('user_id', $user_id)->where('chain', $chain)->first();
        $wallet_address = $wallet->address ?? null;
        $tokens = $balanceService->getFilteredTokens();
        $title = "Receive Token";
        return view('wallet.receive-token', compact('title', 'symbol', 'tokens', 'wallet_address'));
    }

    public function transactions(BalanceService $balanceService)
    {
        $title = "Transactions";
        $tokens = $balanceService->getFilteredTokens();
        $transfers = $this->get_transactions();
        // dd($transfers);
        return view('wallet.transactions', compact('title', 'tokens', 'transfers'));
    }

    public function get_transactions($symbol = null)
    {
        $user_id = Auth::user()->id;
        if ($symbol == null) {
            $wallet_addresses = Wallet::where('user_id', $user_id)
                ->pluck('address') // only fetch "address" column
                ->toArray();
        } else {
            $chainNames = [
                'BTC' => 'bitcoin',
                'ETH' => 'ethereum',
                'LTC' => 'litecoin',
                'USDT' => 'tron',
                'XRP' => 'xrp',
                'DOGE' => 'dogecoin',
                'TRX' => 'tron',
                'BNB' => 'bsc'
            ];
            $upperSymbol = strtoupper($symbol);
            $chain = $chainNames[$upperSymbol];
            $wallet_addresses = Wallet::where('user_id', $user_id)
                ->where('chain', $chain)
                ->pluck('address')
                ->toArray();
        }

        $allTransfers = [];

        foreach ($wallet_addresses as $address) {
            $url = "https://sns_erp.pibin.workers.dev/api/alchemy/" . $address;

            $url = "https://styx.pibin.workers.dev/api/tatum/v4/data/transaction/history?chain=ethereum-mainnet&addresses=" . $address . "&sort=DESC";

            try {
                $response = Http::timeout(10) // wait max 10 seconds
                    ->retry(3, 200)           // retry 3 times, wait 200ms between
                    ->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    if (isset($data['result'])) {
                        $allTransfers = array_merge(
                            $allTransfers,
                            $data['result']
                        );
                    }
                } else {
                    Log::error("Alchemy transfers API responded with error for address {$address}");
                }
            } catch (\Throwable $e) {
                // Catch server down, timeout, connection issues etc.
                Log::error("Alchemy transfers API failed for address {$address}: " . $e->getMessage());
                continue; // move on to next wallet
            }
        }

        // $allTransfers now contains merged transfers from all wallets (even if some failed)
        return $allTransfers;
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
