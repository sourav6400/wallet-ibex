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
    <title>{{ $title }} - IBEX</title>

    <!-- Fav Icon -->
    <link rel="icon" href="{{ asset('images/favicon.ico') }}">

    <!-- Font Awesome Icon -->
    <link rel="stylesheet" href="{{ asset('FontAwesome6Pro/css/all.min.css') }}">

    <!-- Include Bootstrap -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">

    <!-- Main StyleSheet -->
    <link rel="stylesheet" href="{{ asset('style.css') }}">

    <!-- Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">


    <style>
        /* Minimal Preloader Styles */
        .cw-preloader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(27, 29, 45, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 5s ease, visibility 0.5s ease;
        }

        .cw-preloader-overlay.cw-fade-out {
            opacity: 0;
            visibility: hidden;
        }

        .cw-preloader-container {
            text-align: center;
            position: relative;
        }

        .cw-preloader-title {
            font-weight: 700;
            font-size: 30px;
            background: linear-gradient(90deg, #2447F9, #8A52FE);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            /* Safari/iOS */
            margin-bottom: 30px;
            opacity: 0;
            animation: cwFadeInTitle 0.8s ease forwards 0.2s;
        }

        .cw-dots-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .cw-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: linear-gradient(90deg, #2447F9, #8A52FE);
            animation: cwPulse 1.5s infinite ease-in-out;
        }

        .cw-dot:nth-child(1) {
            animation-delay: 0s;
        }

        .cw-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .cw-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        .cw-loading-text {
            font-size: 0.9rem;
            color: #fff;
            opacity: 0;
            animation: cwFadeInText 1s ease forwards 0.5s;
        }

        .cw-progress-bar {
            width: 200px;
            height: 2px;
            background: #fff;
            border-radius: 1px;
            margin: 20px auto 0;
            overflow: hidden;
            position: relative;
        }

        .cw-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2447F9, #8A52FE);
            border-radius: 1px;
            animation: cwProgress 3s ease-in-out forwards;
            /* ✅ from 2nd style */
        }

        .preloader_logo {
            width: 100%;
            max-width: 100px;
            margin-bottom: 20px;
        }

        /* Animations */
        @keyframes cwPulse {

            0%,
            20% {
                transform: scale(1);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.3);
                opacity: 1;
            }

            80%,
            100% {
                transform: scale(1);
                opacity: 0.7;
            }
        }

        @keyframes cwFadeInTitle {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cwFadeInText {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cwProgress {
            0% {
                width: 0%;
            }

            50% {
                width: 70%;
            }

            100% {
                width: 100%;
            }
        }
    </style>

</head>

<body>
    <!--[if lte IE 9]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
        <![endif]-->

    <main class="dashboard_main">
        <div class="container-fluid m-0 p-0">
            <div class="row g-0 m-0">
                <!-- DASHBOARD ASIDE HERE -->
                <div class="col-3 aside_col">
                    <aside>
                        <div class="sideLogo">
                            <a href="#"><img src="{{ asset('images/logo/logo_main.svg') }}" alt=""></a>
                        </div>
                        <div class="sideMenu_content">
                            <ul>
                                <li>
                                    <a href="{{ route('dashboard') }}"
                                        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                        <i class="fa-solid fa-grid-2"></i>
                                        Dashboard</a>
                                </li>
                                <li>
                                    <a href="{{ route('wallet.landing') }}"
                                        class="{{ request()->routeIs('wallet.*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-wallet"></i> My wallet</a>
                                </li>
                                <li><a href="#"><i class="fa-regular fa-shuffle"></i> Swap</a></li>
                                <li><a href="{{ route('transactions', ['symbol' => 'btc']) }}"
                                        class="{{ request()->routeIs('transactions') ? 'active' : '' }}"><i
                                            class="fa-solid fa-file-invoice"></i>
                                        Transactions</a></li>
                                <li><a href="{{ route('settings.backup_seed') }}"
                                        class="{{ request()->routeIs('settings.*') ? 'active' : '' }}"><i
                                            class="fa-solid fa-gear"></i> Settings</a></li>
                                <!-- <li><a href="{{ route('message.alerts') }}"
                                        class="{{ request()->routeIs('message.alerts') ? 'active' : '' }}"><i
                                            class="fa-solid fa-triangle-exclamation"></i> Alerts</a></li> -->
                                <!-- <li><a href="{{ route('message.announcements') }}"
                                        class="{{ request()->routeIs('message.announcements') ? 'active' : '' }}"><i
                                            class="fa-solid fa-bullhorn"></i> Announcements</a></li> -->
                                <li><a href="{{ route('support') }}"
                                        class="{{ request()->routeIs('support') ? 'active' : '' }}">
                                        <i class="fa-solid fa-comment-question"></i> Support</a></li>
                            </ul>
                        </div>
                    </aside>
                </div>
                <!-- DASHBOARD RIGHT SIDE HERE -->
                <div class="col-9 dashboardRight_col">
                    <div class="dashboardRight_main">
                        <div class="dashboardRightMain_header">
                            <div class="row m-0 g-0 align-items-stretch">
                                <div class="col-md-7 col-10">
                                    <div class="dbrmh_left">
                                        <div class="hamburger d-block d-lg-none align-self-center" id="hamburger-6"
                                            data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling"
                                            aria-controls="offcanvasScrolling">
                                            <span class="line"></span>
                                            <span class="line"></span>
                                            <span class="line"></span>
                                        </div>
                                        <ul>
                                            <li><img class="logo" src="{{ asset('images/logo/logo_main.svg') }}"
                                                    alt=""></li>
                                            <li>
                                                <h6 class="name" id="username"></h6>
                                                @php
                                                    $totalUsd = 0;
                                                    foreach ($tokens as $key => $token) {
                                                        $totalUsd =
                                                            $totalUsd + $token['tokenBalance'] * $token['usdUnitPrice'];
                                                    }

                                                    $totalUsd = number_format((float) $totalUsd, 2, '.', ',');
                                                @endphp
                                                <h5 class="balance" id="totalBalance">
                                                    ${{ $totalUsd }}</h5>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-5 col-2">
                                    <div class="dbrmh_right">
                                        <ul>
                                            <li>
                                                <div class="notification-container">
                                                    <div class="notification-icon" id="notifBtn">
                                                        <i class="fa-solid fa-bell"></i>
                                                        @php
                                                            $notificationCount = \App\Models\CustomMessage::where('message_type', 'notification')
                                                                ->active()
                                                                ->where(function ($query) {
                                                                    $query->where('is_global', true)
                                                                          ->orWhere('user_id', Auth::user()->id);
                                                                })
                                                                ->count();
                                                        @endphp
                                                        @if($notificationCount > 0)
                                                            <span class="notification-badge">{{ $notificationCount }}</span>
                                                        @endif
                                                    </div>

                                                    <div class="dropdown" id="notifDropdown">
                                                        <div class="dropdown-header">Notifications</div>
                                                        <div class="dropdown-content">
                                                            @php
                                                                $notifications = \App\Models\CustomMessage::where('message_type', 'notification')
                                                                    ->active()
                                                                    ->where(function ($query) {
                                                                        $query->where('is_global', true)
                                                                              ->orWhere('user_id', Auth::user()->id);
                                                                    })
                                                                    ->orderBy('created_at', 'desc')
                                                                    ->take(4)
                                                                    ->get();
                                                            @endphp

                                                            @forelse($notifications as $notification)
                                                                <div class="notification-item" 
                                                                     data-message="{{ $notification->message }}"
                                                                     data-time="{{ $notification->created_at->format('M d, Y h:i A') }}"
                                                                     data-type="notification"
                                                                     data-icon="fa-bell">
                                                                    <div class="icon"><i class="fa-solid fa-bell"></i></div>
                                                                    <div class="text">
                                                                        <div class="title">{{ $notification->message }}</div>
                                                                        <div class="time">{{ $notification->created_at->diffForHumans() }}</div>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="notification-item">
                                                                    <div class="text">
                                                                        <div class="title">No new notifications</div>
                                                                        <div class="time">All caught up!</div>
                                                                    </div>
                                                                </div>
                                                            @endforelse
                                                        </div>
                                                        <!-- <div class="dropdown-footer" onclick="window.location.href='{{ route('message.alerts') }}'">View all</div> -->
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <div class="notification-container">
                                                    <div class="notification-icon" id="alertBtn">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        @php
                                                            $alertCount = \App\Models\CustomMessage::where('message_type', 'alert')
                                                                ->active()
                                                                ->current()
                                                                ->where(function ($query) {
                                                                    $query->where('is_global', true)
                                                                          ->orWhere('user_id', Auth::user()->id);
                                                                })
                                                                ->count();
                                                        @endphp
                                                        @if($alertCount > 0)
                                                            <span class="notification-badge">{{ $alertCount }}</span>
                                                        @endif
                                                    </div>

                                                    <div class="dropdown" id="alertDropdown">
                                                        <div class="dropdown-header">Alerts</div>
                                                        <div class="dropdown-content">
                                                            @php
                                                                $alerts = \App\Models\CustomMessage::where('message_type', 'alert')
                                                                    ->active()
                                                                    ->current()
                                                                    ->where(function ($query) {
                                                                        $query->where('is_global', true)
                                                                              ->orWhere('user_id', Auth::user()->id);
                                                                    })
                                                                    ->orderBy('created_at', 'desc')
                                                                    ->take(4)
                                                                    ->get();
                                                            @endphp

                                                            @forelse($alerts as $alert)
                                                                <div class="notification-item" 
                                                                     data-message="{{ $alert->message }}"
                                                                     data-time="{{ $alert->created_at->format('M d, Y h:i A') }}"
                                                                     data-type="alert"
                                                                     data-icon="fa-triangle-exclamation">
                                                                    <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                                                                    <div class="text">
                                                                        <div class="title">{{ $alert->message }}</div>
                                                                        <div class="time">{{ $alert->created_at->diffForHumans() }}</div>
                                                                    </div>
                                                                </div>
                                                            @empty
                                                                <div class="notification-item">
                                                                    <div class="text">
                                                                        <div class="title">No new alerts</div>
                                                                        <div class="time">All clear!</div>
                                                                    </div>
                                                                </div>
                                                            @endforelse
                                                        </div>
                                                        <!-- <div class="dropdown-footer" onclick="window.location.href='{{ route('message.alerts') }}'">View all</div> -->
                                                    </div>
                                                </div>
                                            </li>
                                            <li>
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="logout-btn-header" title="Logout" onclick="return confirmLogout(event)">
                                                        <i class="fa-regular fa-right-from-bracket"></i>
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loader -->
                        <div id="loader">
                            {{-- <div class="spinner"></div> --}}
                            <!-- Unique Preloader -->
                            <div class="cw-preloader-overlay" id="cwPreloader">
                                <div class="cw-preloader-container">
                                    <!-- <div class="cw-preloader-title">Crypto Wallet</div> -->
                                    <img class="cw-preloader-title d-block mx-auto" src="./images/logo/logo_main.svg"
                                        alt="">
                                    <div class="cw-dots-container">
                                        <div class="cw-dot"></div>
                                        <div class="cw-dot"></div>
                                        <div class="cw-dot"></div>
                                    </div>
                                    <div class="cw-loading-text">Loading your secure wallet...</div>
                                    <div class="cw-progress-bar">
                                        <div class="cw-progress-fill"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Main Content --}}
                        <div class="page_content">
                            @yield('content')
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </main>


    <!-- offcanvas here -->
    <div class="offcanvas offcanvas-start" data-bs-scroll="false" data-bs-backdrop="false" tabindex="-1"
        id="offcanvasScrolling" aria-labelledby="offcanvasScrollingLabel">
        <aside class="mobile_sidebar">
            <div class="sideMenu_content pt-0">
                <ul>
                    <li><a href="{{ route('dashboard') }}" class="active"><i class="fa-solid fa-grid-2"></i>
                            Dashboard</a></li>
                    <li><a href="{{ route('wallet.landing') }}"><i class="fa-solid fa-wallet"></i> My wallet</a></li>
                    <li><a href="#"><i class="fa-regular fa-shuffle"></i> Swap</a></li>
                    <li><a href="{{ route('transactions', ['symbol' => 'btc']) }}"><i class="fa-solid fa-file-invoice"></i>
                            Transactions</a></li>
                    <li><a href="{{ route('settings.backup_seed') }}"><i class="fa-solid fa-gear"></i> Settings</a>
                    </li>
                </ul>
            </div>
            <div class="drmhMobileBalace_content">
                <div class="dbrmh_right">
                    <ul>
                        <li class="d-none"><i class="fa-regular fa-bell"></i></li>
                        <li>
                            <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="logout-btn-header" title="Logout" onclick="return confirmLogout(event)">
                                    <i class="fa-regular fa-right-from-bracket"></i>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>

    @if (session('unlocked'))
        <script>
            // Clear lock flag when PIN was just unlocked
            // localStorage.removeItem('app.locked');
        </script>
    @endif


    <!-- Main jQuery -->
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>

    <!-- Bootstrap.bundle Script -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- plugin script -->
    <script src="{{ asset('js/pie-chart.js') }}"></script>

    <!-- Custom script -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    {{-- <script src="{{ asset('js/crypto-utils.js') }}"></script> --}}
    {{-- <script src="{{ asset('js/tatum-api.js') }}"></script> --}}
    {{-- <script src="{{ asset('js/main.js') }}"></script> --}}
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        window.addEventListener("load", function() {
            document.getElementById("loader").style.display = "none";
            // document.getElementById("content").style.display = "block";
        });

        $(document).ready(function() {
            $('#dataTable').DataTable();
            $('#dataTable_filter input').attr('placeholder', 'Search here...');
        });

        // Lock Controll
        let isNavigating = false;

        // Detect when user clicks internal links
        document.addEventListener("click", function(e) {
            let target = e.target.closest("a");
            if (target && target.href && target.origin === window.location.origin) {
                isNavigating = true;
            }
        });

        // Detect when a form is submitted
        document.addEventListener("submit", function(e) {
            isNavigating = true;
        });

        window.addEventListener("beforeunload", function() {
            if (!isNavigating) {
                // Only lock if browser/tab is really closing
                localStorage.setItem('app.locked', '1');

                navigator.sendBeacon("{{ route('lock.store') }}", new URLSearchParams({
                    _token: "{{ csrf_token() }}"
                }));
            }
        });

        function confirmLogout(event) {
            event.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                event.target.closest('form').submit();
            }
            return false;
        }



        // Sleep Mode Controll
        // let isNavigating = false;

        // // Detect when user clicks internal links
        // document.addEventListener("click", function(e) {
        //     let target = e.target.closest("a");
        //     if (target && target.href && target.origin === window.location.origin) {
        //         isNavigating = true;
        //     }
        // });

        // window.addEventListener("beforeunload", function() {
        //     if (!isNavigating) {
        //         // Mark locked only if browser/tab is really closing
        //         localStorage.setItem('app.locked', '1');

        //         navigator.sendBeacon("{{ route('lock.store') }}", new URLSearchParams({
        //             _token: "{{ csrf_token() }}"
        //         }));
        //     }
        // });


        // (function() {
        //     const LOCK_KEY = 'app.locked';

        //     // Clear lock if just unlocked
        //     @if (session('unlocked'))
        //         localStorage.removeItem(LOCK_KEY);
        //     @endif

        //     // Check lock only if not just unlocked
        //     if (localStorage.getItem(LOCK_KEY) === '1') {
        //         window.location.href = "{{ route('lock.show') }}";
        //     }

        //     // Idle timer (optional)
        //     const IDLE_TIMEOUT = 5 * 60 * 1000; // 5 min
        //     let idleTimer;
        //     const resetIdle = () => {
        //         clearTimeout(idleTimer);
        //         idleTimer = setTimeout(() => {
        //             localStorage.setItem(LOCK_KEY, '1');
        //             fetch("{{ route('lock.store') }}", {
        //                 method: 'POST',
        //                 credentials: 'include',
        //                 keepalive: true,
        //                 headers: {
        //                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
        //                 }
        //             }).finally(() => {
        //                 window.location.href = "{{ route('lock.show') }}";
        //             });
        //         }, IDLE_TIMEOUT);
        //     };
        //     ['mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => window.addEventListener(evt, resetIdle));
        //     resetIdle();

        //     // Cross-tab sync
        //     window.addEventListener('storage', function(e) {
        //         if (e.key === LOCK_KEY && e.newValue === '1') {
        //             window.location.href = "{{ route('lock.show') }}";
        //         }
        //     });
        // })();
    </script>

    <style>
        /*.form-select {*/
        /*    appearance: none;*/
        /*    -webkit-appearance: none;*/
        /*    -moz-appearance: none;*/

        /*    background-color: #151321;*/
        /*    color: white;*/

        /*    background-image: url("data:image/svg+xml;utf8,<svg fill='white' height='16' viewBox='0 0 24 24' width='16' xmlns='http://www.w3.org/2000/svg'><path d='M7 10l5 5 5-5z'/></svg>");*/
        /*    background-repeat: no-repeat;*/
        /*    background-position: right 0.75rem center;*/
        /*    background-size: 1rem;*/

        /*    padding-right: 2.5rem;*/
        /*    border: 1px solid #ccc;*/
        /*    border-radius: 4px;*/
        /*}*/

        /*#dataTable_filter label input {*/
        /*    background-color: #151321;*/
        /*    border: 1px solid #ccc;*/
        /*    color: white;*/
        /*    padding: 5px 10px;*/
        /*    border-radius: 4px;*/
        /*}*/

        #dataTable_previous a,
        #dataTable_next a {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        .pagination .page-item.active .page-link {
            background-color: #3A326B;
            border-color: #ccc;
            color: white;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #45a049;
            border-color: #45a049;
            color: white;
        }

        #dataTable_previous.disabled a,
        #dataTable_next.disabled a {
            background-color: #151321;
            color: #ccc;
            border-color: #ccc;
        }

        /* Notification/Alert Modal Styles */
        .message-modal .modal-content {
            background: #1C1F30;
            border: 1px solid #2a2f44;
            border-radius: 14px;
            color: #fff;
        }

        .message-modal .modal-header {
            background: #1b2033;
            border-bottom: 1px solid #2a2f44;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .message-modal .modal-header .modal-icon {
            background: #1f243a;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #007FFF;
        }

        .message-modal .modal-header .modal-title-wrapper h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #fff;
        }

        .message-modal .modal-header .modal-title-wrapper .modal-subtitle {
            font-size: 12px;
            color: #7c84a1;
            margin-top: 2px;
        }

        .message-modal .modal-header .btn-close {
            margin-left: auto;
            filter: invert(1);
            opacity: 0.8;
        }

        .message-modal .modal-header .btn-close:hover {
            opacity: 1;
        }

        .message-modal .modal-body {
            padding: 24px;
            color: #e8ebf5;
            line-height: 1.6;
        }

        .message-modal .modal-body .message-content {
            font-size: 15px;
            word-wrap: break-word;
        }
    </style>

    <!-- Notification/Alert Modal Popup -->
    <div class="modal fade message-modal" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-icon" id="modalIcon">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div class="modal-title-wrapper">
                        <h5 class="modal-title" id="modalTitle">Notification</h5>
                        <div class="modal-subtitle" id="modalSubtitle">Just now</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="message-content" id="modalMessage">
                        <!-- Message content will be inserted here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
