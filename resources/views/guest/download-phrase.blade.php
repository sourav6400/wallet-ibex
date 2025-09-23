@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper createAnAccount position-relative">
        <div class="createAnAccount_card">
            <ol class="steps">
                <li class="step is-active" data-step="1"></li>
                <li class="step is-active" data-step="2"></li>
                <li class="step is-active" data-step="3"></li>
                <li class="step is-active" data-step="4"></li>
                <li class="step is-active" data-step="5"></li>
            </ol>
            <div class="card_titlebar mt-3">
                <img class="d-block mx-auto" src="images/logo/logo.png" alt="">
                <h3>Download Seed Phrase</h3>
                <p>Download your seed phrase as a backup file</p>
            </div>
            <div class="download_seedPhrase">
                <i class="fas fa-download"></i>
                <button type="button" onclick="downloadSeedPhrase()" class="btn-download">Download Seed Phrase</button>
                <p>You can skip the download if you've already backed up your seed phrase manually</p>
            </div>
            <div class="setup-actions card_btn_wrapper">
                <button onclick="goBack()" class="btn-secondary">
                    Back
                </button>
                <form action="{{ route('wallet.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="wallet_pin" value="{{ $wallet_pin }}">
                    <input type="hidden" name="phrase" value="{{ $phrase }}">
                    <button type="submit" class="btn-primary" style="width: 10rem;">Continue</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const username = sessionStorage.getItem('walletUsername');
            document.getElementById('walletUsername').textContent = username;
        });

        function downloadSeedPhrase() {
            const text = @json($phrase); // safely pass PHP string to JS
            const blob = new Blob([text], {
                type: 'text/plain'
            });
            const link = document.createElement('a');
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
            link.href = URL.createObjectURL(blob);
            link.download = `crypto-wallet-backup-${timestamp}.txt`;
            link.click();

            // document.getElementById('createWalletBtn').disabled = false;
        }

        function downloadSeedPhrase_() {
            const seedPhrase = sessionStorage.getItem('seedPhrase');
            const username = sessionStorage.getItem('walletUsername');
            const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');

            const content = `CRYPTO WALLET SEED PHRASE BACKUP
                            ============================================

                            Username: ${username}
                            Created: ${new Date().toLocaleString()}

                            SEED PHRASE (12 WORDS):
                            ${seedPhrase}

                            ============================================
                            IMPORTANT SECURITY NOTES:
                            - Keep this file secure and private
                            - Never share your seed phrase with anyone
                            - You need this phrase to restore your wallet
                            - Store multiple copies in different secure locations
                            ============================================`;

            const blob = new Blob([content], {
                type: 'text/plain'
            });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `crypto-wallet-backup-${timestamp}.txt`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            // Update button to show success
            const button = document.querySelector('.btn-download');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Downloaded!';
            button.style.background = '#61BA61';

            setTimeout(() => {
                button.innerHTML = originalText;
                button.style.background = '';
            }, 2000);
        }

        function skipDownload() {
            completeSetup();
        }

        function goBack() {
            window.location.href = 'word-seed-phrase.html';
        }

        async function completeSetup() {
            // Show loading
            const button = document.querySelector('.btn-primary');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Wallet...';
            button.disabled = true;

            try {
                // Create wallet and store in database
                await createWalletInDatabase();

                // Set session storage for auto-login
                const expiryDate = new Date();
                expiryDate.setFullYear(expiryDate.getFullYear() + 1); // 1 year
                localStorage.setItem('walletSession', JSON.stringify({
                    username: sessionStorage.getItem('walletUsername'),
                    expiry: expiryDate.getTime(),
                    lastLogin: Date.now()
                }));

                // Clear session storage
                sessionStorage.clear();

                // Redirect to dashboard
                window.location.href = 'dashboard.html';
            } catch (error) {
                console.error('Error creating wallet:', error);
                button.innerHTML = 'Error - Try Again';
                button.disabled = false;
                button.style.background = '#f44336';
            }
        }

        async function createWalletInDatabase() {
            const walletData = {
                username: sessionStorage.getItem('walletUsername'),
                pin: sessionStorage.getItem('walletPin'),
                seedPhrase: sessionStorage.getItem('seedPhrase'),
                createdAt: new Date().toISOString()
            };

            // Generate wallets for all supported currencies
            const wallets = await generateWalletsFromSeed(walletData.seedPhrase);
            walletData.wallets = wallets;

            // Store in localStorage (simulating database)
            const allWallets = JSON.parse(localStorage.getItem('cryptoWallets') || '[]');
            allWallets.push(walletData);
            localStorage.setItem('cryptoWallets', JSON.stringify(allWallets));

            return walletData;
        }
    </script>
@endsection
