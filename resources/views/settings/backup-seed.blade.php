@extends('layouts.app')
@section('content')
    <div class="dashboardRight_main">
        <div class="dashboardRightMain_body p-0">
            <div class="settingsMain_wrapper">
                <div class="settingsMain_header">
                    <ul>
                        <li><a href="{{ route('settings.backup_seed') }}" class="active">Private Keys</a></li>
                        <li><a href="{{ route('settings.change_pin_view') }}">Security</a></li>
                    </ul>
                </div>
                <div class="settingsFaq_wrapper privateKeyForm_wrapper">
                    <p>Never share your 12-word backup phrase or private keys with anyone. Avoid entering your information
                        on any web wallets, online forms, or websites impersonating IBEX Wallet. Sharing this information
                        puts your funds at risk of permanent loss.</p>
                    <div class="createAnAccount_body">
                        <form action="">
                            <div class="form_input position-relative pt-4 mb-5">
                                <input class="mb-0" type="password" id="password" value="" placeholder="PIN"
                                    minlength="6" maxlength="6" pattern="\d{6}">
                                <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                            </div>
                            <div class="row m-0 g-0">
                                <div class="col-lg-12">
                                    <div class="form_btn">
                                        <button type="button" id="showKeysBtn">SHOW PRIVATE KEYS</button>
                                        <small class="mt-5" id="pinError" style="color:red; display:none;"></small>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade seedPhrase_modal5" id="exampleModal5" tabindex="-1" aria-labelledby="exampleModalLabel5"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative">
                <div class="seed-phrase-container">
                    <div class="seed-phrase-grid" id="seedPhraseGrid">
                        <!-- Seed phrase words will be generated here -->
                        <div class="seedPhraseProcess_item_wrapper mb-10">
                            <span class="sendPage_closeBtn" role="button" data-bs-dismiss="modal"><i
                                    class="fa-regular fa-xmark"></i></span>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('showKeysBtn').addEventListener('click', function() {
            let pin = document.getElementById('password').value;
            let errorEl = document.getElementById('pinError');

            errorEl.style.display = 'none';
            errorEl.textContent = '';

            if (pin.length !== 6) {
                errorEl.textContent = "PIN must be 6 digits.";
                errorEl.style.display = 'block';
                return;
            }

            fetch("{{ route('settings.check_pin') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        pin: pin
                    })
                })
                .then(response => response.json().then(data => ({
                    status: response.status,
                    body: data
                })))
                .then(({
                    status,
                    body
                }) => {
                    if (status === 200 && body.success) {
                        // PIN correct -> show modal
                        var modal = new bootstrap.Modal(document.getElementById('exampleModal5'));
                        modal.show();
                    } else {
                        errorEl.textContent = body.message || "Something went wrong.";
                        errorEl.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error(err);
                    errorEl.textContent = "Server error. Please try again.";
                    errorEl.style.display = 'block';
                });
        });

        function copySeedPhrase() {
            let words = Array.from(document.querySelectorAll('.seedPhraseProcess_item h5'))
                .map(el => el.innerText.trim());

            let seedPhrase = words.join(' ');

            navigator.clipboard.writeText(seedPhrase)
                .then(() => {
                    const button = document.querySelector('.btn-copy');
                    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                    button.style.background = '#61BA61';
                })
                .catch(err => {
                    console.error("Failed to copy seed phrase: ", err);
                });
        }
    </script>
@endsection
