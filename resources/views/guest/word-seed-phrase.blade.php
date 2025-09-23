@extends('layouts.guest')
@section('content')
    <div class="onboarding_wrapper createAnAccount position-relative px-4">
        <div class="walletSelection_wrapper seedPhraseProcess">
            <div class="seedPhraseProcess_content">
                <ol class="steps">
                    <li class="step is-active" data-step="1"></li>
                    <li class="step is-active" data-step="2"></li>
                    <li class="step is-active" data-step="3"></li>
                    <li class="step is-active" data-step="4"></li>
                    <li class="step" data-step="5"></li>
                </ol>
                <div class="card_titlebar mt-4">
                    <img class="d-block mx-auto mb-3" src="images/logo/logo.png" alt="">
                    <h3>Backup Your Wallet</h3>
                    <p>Write down all 12 words in sequence and keep them in a safe place.</p>
                </div>
                <div class="seed-phrase-container">
                    <div class="seed-phrase-grid" id="seedPhraseGrid">
                        <!-- Seed phrase words will be generated here -->
                        <div class="seedPhraseProcess_item_wrapper mb-10">
                            <div class="row g-lg-4 g-3 m-0">
                                @foreach ($words as $key => $word)
                                    <div class="col-lg-3 col-6">
                                        <div class="seedPhraseProcess_item">
                                            <h6 class="number"></h6>
                                            <h5>{{ $word }}</h5>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="seed-phrase-actions">
                        <button onclick="copySeedPhrase()" class="btn-copy">
                            <i class="fas fa-copy"></i> Copy Seed Phrase
                        </button>
                        <!--<a href="" class="btn-regenerate">-->
                        <!--    <i class="fas fa-sync-alt"></i> Generate New-->
                        <!--</a>-->
                    </div>
                </div>

                <div class="confirmation-section">
                    <label class="checkbox-container">
                        <input type="checkbox" id="confirmBackup" required>
                        <span class="checkmark"></span>
                        I’ve safely backed up my seed phrase.
                    </label>
                </div>
                <div class="card_btn_wrapper">
                    <button type="button" class="back">Back</button>
                    <form action="{{ route('wallet.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="wallet_pin" value="{{ $wallet_pin }}">
                        <input type="hidden" name="phrase12" value="{{ $mnemonic12 }}">
                        <input type="hidden" name="phrase24" value="{{ $mnemonic24 }}">
                        <button type="submit" id="continueBtn" disabled style="width: 10rem;">Continue</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        let seedPhrase = '';

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('confirmBackup').addEventListener('change', function() {
                document.getElementById('continueBtn').disabled = !this.checked;
            });
        });

        function copySeedPhrase() {
            // Get all seed phrase words
            let words = Array.from(document.querySelectorAll('.seedPhraseProcess_item h5'))
                .map(el => el.innerText.trim());

            // Join them into a single sentence
            let seedPhrase = words.join(' ');

            // Copy to clipboard
            navigator.clipboard.writeText(seedPhrase)
                .then(() => {
                    // alert("Seed phrase copied to clipboard!");
                    const button = document.querySelector('.btn-copy');
                    const originalText = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    button.style.background = '#61BA61';
                })
                .catch(err => {
                    console.error("Failed to copy seed phrase: ", err);
                });
        }

        // function copySeedPhrase() {
        //     navigator.clipboard.writeText(seedPhrase).then(() => {
        //         const button = document.querySelector('.btn-copy');
        //         const originalText = button.innerHTML;
        //         button.innerHTML = '<i class="fas fa-check"></i> Copied!';
        //         button.style.background = '#61BA61';

        //         setTimeout(() => {
        //             button.innerHTML = originalText;
        //             button.style.background = '';
        //         }, 2000);
        //     }).catch(err => {
        //         console.error('Failed to copy: ', err);
        //     });
        // }

        function goBack() {
            window.location.href = 'wallet-pin-set-confirm.html';
        }

        function nextStep() {
            if (document.getElementById('confirmBackup').checked) {
                window.location.href = 'generate-seed-download.html';
            }
        }
    </script>
@endsection
