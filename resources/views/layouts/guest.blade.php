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
    <title>{{$title}} - IBEX</title>

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
    @if ($errors->has('account_disabled'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first('account_disabled') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    {{-- Main Content --}}
    <div class="page_content">
        @yield('content')
    </div>

    <!-- Main jQuery -->
    <script src="{{ asset('js/jquery-3.4.1.min.js') }}"></script>

    <!-- Bootstrap.bundle Script -->
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- Custom jQuery -->
    <script src="{{ asset('js/scripts.js') }}"></script>
</body>

</html>
