<!DOCTYPE html>
<html lang="en-US">

<head>
    <!-- Meta setup -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="">
    <meta name="decription" content="">
    <meta name="designer" content="">

    <!-- Title -->
    <title>Enter PIN - IBEX</title>

    <!-- Fav Icon -->
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <!-- Include Bootstrap -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">

    <!-- Main StyleSheet -->
    <link rel="stylesheet" href="{{ asset('style.css') }}">

    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

</head>

<body>
    <!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->

    <div class="onboarding_wrapper walletRestoreV2 createAnAccount position-relative">
        <div class="createAnAccount_card">
            <div class="createAnAccount_header createNewWallet mb-5">
                <img src="{{ asset('images/logo/logo_main.svg') }}" alt="">
            </div>
            <div class="createAnAccount_body">
                <form method="POST" action="{{ route('lock.unlock') }}" class="walletRestoreV2_form openWalletByPin">
                    @csrf
                    <div class="form_input position-relative pt-4 mb-5">
                        <input name="pin" maxlength="6" pattern="\d{6}" class="mb-0" type="password"
                            id="password" value="" placeholder="PIN" onkeyup="isGood(this.value)" required>
                        <i class="toggle-password fa fa-fw fa-eye-slash"></i>
                    </div>
                    @error('pin')
                        <div class="text-red-600 text-sm mb-2">{{ $message }}</div>
                    @enderror
                    <div class="row m-0 g-0">
                        <div class="col-12">
                            <div class="form_btn mt-5">
                                <button type="submit">Open Wallet</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form_btn mt-2">
                                <button type="button" class="back my-0 fw-normal border-0" data-bs-toggle="modal"
                                    data-bs-target="#restoreWalletWarningModal">Restore Wallet</button>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form_btn">
                                <button type="button" class="back my-0 fw-normal border-0" data-bs-toggle="modal"
                                    data-bs-target="#createWalletWarningModal">Create Wallet</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade warning_modal" id="restoreWalletWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <h5 class="modal-title">Warning You already have a wallet</h5>
                <p>
                    Make sure that you have a backup of your 12 word seed Phrase. <br>
                    Creating the new wallet will erase all your previous local data.
                </p>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button type="button" class="btn-continue" onclick="location.href='{{ route('wallet.forward_to_restore_wallet') }}'">CONTINUE</button>
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">CANCEL</button>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade warning_modal" id="createWalletWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <h5 class="modal-title">Warning You already have a wallet</h5>
                <p>
                    Make sure that you have a backup of your 12 word seed Phrase. <br>
                    Creating the new wallet will erase all your previous local data.
                </p>

                <div class="d-flex justify-content-center gap-3 mt-3">
                    <button type="button" class="btn-continue" onclick="location.href='{{ route('wallet.forward_to_create_wallet') }}'">CONTINUE</button>
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">CANCEL</button>
                </div>

            </div>
        </div>
    </div>


    <!-- Main jQuery -->
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>

    <!-- Bootstrap.bundle Script -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- Custom jQuery -->
    <script src="{{ asset('js/scripts.js') }}"></script>

</body>

</html>

