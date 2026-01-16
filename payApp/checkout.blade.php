<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STV Play - Checkout</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/stvplay-favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/stvplay-favicon.png') }}">

    <!-- Meta Pixel Code -->
<script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1427926855166575');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=1427926855166575&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->

    <style>
        :root {
            --primary-color: #F94D23;
            --secondary-color: #FFBB00;
            --dark-color: #000000;
            --semidark-color: #1b1b1b;
            --light-color: #f8f9fa;
        }

        ::placeholder {
            color: white !important;
            opacity: 1 !important;
            /* Garante que o branco não fica translúcido */
        }

        /* Compatibilidade com navegadores */
        input::placeholder {
            color: white !important;
        }

        input::-webkit-input-placeholder {
            color: white !important;
        }

        input:-moz-placeholder {
            color: white !important;
        }

        input::-moz-placeholder {
            color: white !important;
        }

        input:-ms-input-placeholder {
            color: white !important;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-color {
            background-color: var(--secondary-color);
        }

        .bg-color:hover {
            background-color: var(--primary-color);
        }

        body {
            background: linear-gradient(135deg, #313131 0%, #222222 100%);
            min-height: 100vh;
        }

        .banner-stv {
            background-image: url('{{ asset('assets/images/bg-banner-stvplay.png') }}');
            background-size: contain;
            background-position: right;
            background-repeat: no-repeat;
            background-color: var(--dark-color);
        }

        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }

        /* Gradient text effect */
        .gradient-text {
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .card-bg {
            background: var(--dark-color);
            border-radius: 10px;
        }

        /* START PAYMENT METHOD CSS */
        .payment-methods {
            display: flex;
            gap: 1px;
            border-radius: 50rem;
            overflow: hidden;
            margin: 1rem 0;
        }

        .payment-option {
            flex: 1;
            position: relative;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .payment-option:first-child {
            border-radius: 50rem 0 0 50rem;
        }

        .payment-option:last-child {
            border-radius: 0 50rem 50rem 0;
        }

        .payment-option:hover {
            background: #e9ecef;
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .payment-option input[type="radio"]:checked+.option-content {
            background: var(--primary-color);
            color: white;
        }

        .option-content {
            display: flex;
            align-items: center;
            gap: 0.15rem;
            padding: 0.7rem 0.7rem;
            transition: all 0.2s ease;
        }

        .option-icon {
            width: 24px;
            height: 24px;
        }

        /* END PAYMENT METHOD CSS */



        .list-group {
            background: transparent !important;
            border: none !important;
            padding: 1rem 0 !important;
            border-radius: 0 !important;
        }

        .list-group-item,
        .bg-body-tertiary {
            background-color: transparent !important;
            border: none;
            padding: 0.5rem 0;
        }

        .form-control {
            color: white !important;
            background-color: #2d2d2d !important;
            background-clip: padding-box;
            border: none !important;
        }

        .input-group-text {
            background-color: 2d2d2d !important;
            border: none !important;
        }

        .form-select:disabled {
            background-color: 2d2d2d !important;
            border: none !important;
        }

        body {
            font-family: 'Geologica', sans-serif !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        a,
        li,
        blockquote,
        pre,
        code,
        .text {
            font-family: 'Geologica', sans-serif !important;
        }

        .stv-alert {
            color: #FFBB00 !important;
        }

        .stv-alert:hover {
            text-decoration: underline !important;
        }

        .stv-container {
            display: block;
            position: relative;
            padding-left: 35px;
            margin-bottom: 12px;
            cursor: pointer;
            /* font-size: 22px; */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-black shadow sticky-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand d-flex align-items-center" href="#">
                <svg version="1.1" id="Layer_1" style="height: 40px;" xmlns="http://www.w3.org/2000/svg"
                    xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 172.5 61.5"
                    style="enable-background:new 0 0 172.5 61.5;" xml:space="preserve">
                    <style type="text/css">
                        .st0 {
                            opacity: 0.25;
                        }

                        .st1 {
                            fill: #FFFFFF;
                        }
                    </style>
                    <g class="st0"></g>
                    <g>
                        <path class="st1"
                            d="M82.5,28.8c-3.5-1.2-5-1.9-5-3.5c0-1.2,1.3-2.4,3.9-2.4c2.6,0,4.6,0.7,5.6,1.2l1.4-4.5c-1.6-0.7-3.8-1.2-6.9-1.2c-6.3,0-10.2,3.2-10.2,7.4c0,3.6,3,5.9,7.5,7.3c3.2,1,4.5,1.9,4.5,3.5c0,1.6-1.5,2.7-4.3,2.7c-2.6,0-5.2-0.8-6.8-1.5L71,42.4c1.5,0.8,4.6,1.5,7.7,1.5c7.4,0,10.9-3.5,10.9-7.7C89.5,32.7,87.3,30.4,82.5,28.8z" />
                        <path class="st1"
                            d="M109.6,33.9c-0.4,1.5-0.8,2.9-1.1,4.4h-0.1c-0.3-1.4-0.6-2.8-1.1-4.4l-2.6-8.3h-7.8v-4.8l-6,1.5v3.3h-2.6v4.1h2.6V37c0,2.5,0.6,4.3,1.7,5.3c1,0.9,2.6,1.5,4.6,1.5c1.7,0,3.2-0.2,3.9-0.5l0-4.2c-0.6,0.1-1,0.1-1.8,0.1c-1.8,0-2.4-1-2.4-3.2v-6.5h2.8l5.5,13.9h6.1l7.3-18h-6.4L109.6,33.9z" />
                        <path class="st1"
                            d="M132.8,25c-1.2-1.1-3.1-1.7-5.7-1.7c-2.1,0-3.7,0.2-5,0.4v19.9h2.6v-8.1c0.6,0.2,1.3,0.2,2.1,0.2c2.5,0,4.8-0.7,6.2-2.2c1-1,1.6-2.5,1.6-4.3C134.6,27.5,133.9,26,132.8,25z M126.9,33.7c-0.9,0-1.6-0.1-2.2-0.2v-7.8c0.4-0.1,1.3-0.2,2.5-0.2c2.9,0,4.8,1.3,4.8,3.9C132,32.2,130.1,33.7,126.9,33.7z" />
                        <rect x="137.8" y="22.4" class="st1" width="2.6" height="21.2" />
                        <path class="st1"
                            d="M155.1,34.8c0-2.9-1.1-5.9-5.5-5.9c-1.8,0-3.6,0.5-4.8,1.3l0.6,1.7c1-0.7,2.4-1.1,3.8-1.1c3,0,3.3,2.2,3.3,3.3v0.3c-5.6,0-8.7,1.9-8.7,5.4c0,2.1,1.5,4.2,4.4,4.2c2.1,0,3.6-1,4.4-2.2h0.1l0.2,1.8h2.4c-0.2-1-0.2-2.2-0.2-3.5V34.8z M152.5,38.8c0,0.3-0.1,0.6-0.2,0.8c-0.4,1.2-1.6,2.4-3.5,2.4c-1.3,0-2.5-0.8-2.5-2.5c0-2.8,3.3-3.3,6.1-3.3V38.8z" />
                        <path class="st1"
                            d="M168,29.2l-2.9,8.5c-0.4,1-0.7,2.2-0.9,3h-0.1c-0.2-0.9-0.6-2-0.9-3l-3.2-8.6h-2.9l5.4,13.3c0.1,0.3,0.2,0.5,0.2,0.7c0,0.1-0.1,0.4-0.2,0.6c-0.6,1.3-1.5,2.4-2.2,2.9c-0.8,0.7-1.6,1.1-2.3,1.3l0.7,2.2c0.7-0.1,1.9-0.6,3.2-1.7c1.8-1.6,3.1-4.1,5-9.1l3.9-10.3H168z" />
                        <path class="st1"
                            d="M31.1,1.7c-16.2,0-29.3,13-29.3,29l0,0h0v0c0,16,13.1,29,29.3,29c16.2,0,29.3-13,29.3-29v0h0v0C60.4,14.7,47.2,1.7,31.1,1.7z M2,30.8L2,30.8L2,30.8C2,23.3,9.6,17,20.3,14.4c-5.5,3.5-9.2,9.5-9.2,16.4c0,6.9,3.7,13,9.2,16.4C9.6,44.6,2,38.2,2,30.8z M24.6,43.4c-0.5,0.3-1.1,0.2-1.6-0.1c-0.4-0.3-0.6-0.7-0.6-1.2V19.2c0-0.5,0.3-1,0.6-1.2c0.4-0.3,1-0.4,1.6-0.1l19.6,11.4c0.5,0.3,0.7,0.8,0.7,1.3c0,0.5-0.3,0.9-0.7,1.2L24.6,43.4z M60.1,30.8L60.1,30.8L60.1,30.8c0,7.5-7.6,13.8-18.3,16.4c5.5-3.5,9.2-9.5,9.2-16.4c0-6.9-3.7-13-9.2-16.4C52.5,17,60.1,23.3,60.1,30.8z" />
                    </g>
                </svg>
            </a>

            <!-- Right side menu -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <!-- User dropdown -->
                <div class="dropdown">
                    <button class="btn dropdown-toggle d-flex align-items-center" type="button" id="userDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        {{-- <img class="h-8 w-8 rounded-circle border border-indigo-100" style="height: 30px;"
                            src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                            alt="User profile"> --}}

                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                            class="bi bi-person-circle text-white" viewBox="0 0 16 16">
                            <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0" />
                            <path fill-rule="evenodd"
                                d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1" />
                        </svg>
                        <span class="ms-2 d-none d-md-inline text-white"
                            id="userName">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link class="dropdown-item text-danger" :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();" id="logoutBtn">
                                    <i class="fas fa-sign-out-alt"></i> {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </li>
                        @if (auth()->user()->can('isAdmin') || auth()->user()->can('isManager'))
                            <li>
                                <x-dropdown-link class="dropdown-item text-danger" :href="route('dashboard')">
                                    <i class="bi bi-speedometer"></i> {{ __('Dashboard') }}
                                </x-dropdown-link>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </nav>


    @if ($errors->has('token_error'))
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="alert alert-danger">
                    {{ $errors->first('token_error') }}
                </div>
                <div>
                </div>
    @endif

    <div class="container py-5 d-none" id="loader">
        <div class="text-center">
            @include('elements.loader')
            <div class="alert alert-warning alert-dismissible fade show d-none" id="verifique_mpesa" role="alert">
                Verifique o seu telefone para inserir o seu .<strong>PIN</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div class="alert alert-danger d-none" id="error_mpesa">
            </div>
        </div>
    </div>

    <div class="container py-5">

        <div class="row justify-content-center">
            <div class="col">
                <div class="alert alert-success d-none" id="success-display">
                    Pagamento realizado com sucesso!
                </div>

                @if (session('success'))
                    <div class="alert alert-success">
                        Pagamento realizado com sucesso!
                        <!-- Access other data -->
                        {{-- @dump(session('subscription'), session('payment'), session('user'))  --}}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        Error: {{ session('error') }}
                        @if (session('subscription'))
                            <!-- Show subscription details if available -->
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="row justify-content-center d-none mb-5" id="local-error">
            <div class="col">
                <div class="alert alert-danger alert-dismissible fade show stv-my" role="alert">
                    <span id="error-display">Ocorreu um erro com a sua operação. Entre em contacto com a nossa equipa
                        de atendimento pelo email suporte@stvplay.co.mz</span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="row g-5 justify-content-center" id="pacotes">
            <div class="col-md-12 col-lg-8 order-md-last">
                <div class="card-bg" style="padding: 30px;">
                    <h4 class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-white">Resumo do Pedido</span>
                        {{-- <span class="badge bg-primary rounded-pill">3</span> --}}
                    </h4>
                    <ul class="list-group mb-3">
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0 text-white"><?php echo isset($pacote['name']) ? htmlspecialchars($pacote['name']) : 'Package Name'; ?></h6>
                                {{-- <small class="text-body-secondary">Brief description</small> --}}
                            </div>
                            <span class="text-white">
                                <strong><?php echo isset($pacote['recurring_price']) ? htmlspecialchars($pacote['recurring_price']) : 0; ?>MT</strong>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between bg-body-tertiary d-none"
                            id="discountRow">
                            <div class="text-white">
                                {{-- <h6 class="my-0">Promo code</h6> --}}<small><strong>Valor do Desconto</strong></small>
                            </div>
                            <span class="text-white" id="discountValue"><strong>−$5</strong></span>
                        </li>

                        <li class="list-group-item d-flex justify-content-between text-white" id="nodiscount">
                            <span>Total (MT)</span>
                            <strong><?php echo isset($pacote['recurring_price']) ? htmlspecialchars($pacote['recurring_price']) : 0; ?>MT</strong>
                        </li>

                        <li class="list-group-item d-flex justify-content-between text-white d-none"
                            id="afterdiscount">
                            <span>Total (MT)</span>
                            <strong>0MT</strong>
                        </li>
                    </ul>

                    <div class="payment-methods">
                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="mpesa" checked>
                            <div class="option-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    fill="currentColor" class="bi bi-phone-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M3 2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zm6 11a1 1 0 1 0-2 0 1 1 0 0 0 2 0" />
                                </svg>
                                <span class="small">M-Pesa</span>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="emola">
                            <div class="option-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    fill="currentColor" class="bi bi-phone-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M3 2a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zm6 11a1 1 0 1 0-2 0 1 1 0 0 0 2 0" />
                                </svg>
                                <span class="small">Emola</span>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="qrcode" disabled>
                            <div class="option-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-qr-code" viewBox="0 0 16 16">
                                    <path d="M2 2h2v2H2z"/>
                                    <path d="M6 0v6H0V0zM5 1H1v4h4zM4 12H2v2h2z"/>
                                    <path d="M6 10v6H0v-6zm-5 1v4h4v-4zm11-9h2v2h-2z"/>
                                    <path d="M10 0v6h6V0zm5 1v4h-4V1zM8 1V0h1v2H8v2H7V1zm0 5V4h1v2zM6 8V7h1V6h1v2h1V7h5v1h-4v1H7V8zm0 0v1H2V8H1v1H0V7h3v1zm10 1h-1V7h1zm-1 0h-1v2h2v-1h-1zm-4 0h2v1h-1v1h-1zm2 3v-1h-1v1h-1v1H9v1h3v-2zm0 0h3v1h-2v1h-1zm-4-1v1h1v-2H7v1z"/>
                                    <path d="M7 12h1v3h4v1H7zm9 2v2h-3v-1h2v-1z"/>
                                </svg>
                                <span style="padding-left: 5px;" class="small">QR Code IZI</span>
                            </div>
                        </label>

                        <label class="payment-option">
                            <input type="radio" name="payment_method" value="cartao">
                            <div class="option-content">
                                <svg class="option-icon" viewBox="0 0 24 24" fill="currentColor">
                                    <path
                                        d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" />
                                </svg>
                                <span class="small">Cartão</span>
                            </div>
                        </label>
                    </div>

                    <div style="margin: 25px 0 !important;">
                        <div class="col-auto" id="visualize_mpesa">
                            <label class="visually-hidden" for="numero_mpesa">Número M-Pesa</label>
                            <div class="input-group">
                                <div class="input-group-text">M-Pesa</div>
                                <input type="number" id="numero_mpesa" class="form-control"
                                    placeholder="Número M-Pesa">
                            </div>
                        </div>

                        <div class="col-auto d-none" id="visualize_emola">
                            <label class="visually-hidden" for="numero_emola">Número Emola</label>
                            <div class="input-group">
                                <div class="input-group-text">EMOLA</div>
                                <input type="number" id="numero_emola" class="form-control"
                                    placeholder="Número Emola">
                            </div>
                        </div>
                    </div>

                    <div class="input-group d-flex justify-content-between py-3">
                        <input type="text" id="couponCode" class="form-control" placeholder="Cupão de desconto">
                        <button id="redeemCoupon" class="btn btn-secondary bg-color">Resgatar</button>
                    </div>
                    <div id="couponAlert" class="mt-2"></div>

                    <div class="pt-4">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="termsCheck">
                            <label class="form-check-label text-white" for="termsCheck">
                                Tomei conhecimento e aceito os <a href="{{ route('terms.of.use') }}"
                                    class="stv-alert">Termos e Condições </a>de uso da plataforma
                            </label>
                        </div>
                        <button class="btn bg-color btn-lg w-100 py-3" id="completePurchaseBtn">
                            <i class="fas fa-lock me-2"></i> Completar a compra
                        </button>
                        <p class="text-center text-white mt-3">
                            <i class="fas fa-shield-alt me-2"></i> Encriptação SSL segura de 256 bits
                        </p>
                    </div>
                </div>
            </div>

            {{--
            <div class="col-md-12 col-lg-7">
                <div class="card-bg" style="padding: 30px;">
                    <h4 class="mb-3 text-white">Endereço de Cobrança (Opcional)</h4>
                    <form class="needs-validation" novalidate="">
                        <div class="row g-3">
                            <div class="col-12">
                                <input type="text" class="form-control" id="address" placeholder="Endereço"
                                    required="" name="street">
                                <div class="invalid-feedback text-white">
                                    Por favor adicione o seu endereço (Opcional)
                                </div>
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control" id="address2" placeholder="Endereço 2"
                                    name="attention">
                            </div>
                            <div class="col-md-5">
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Selecionar...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="state" name="state" required disabled>
                                    <option value="">Escolha primeiro o país</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" id="zip" name="zip"
                                    placeholder="Caixa Postal" required="">
                                <div class="invalid-feedback">
                                    Caixa Postal.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div> --}}
        </div>

        @php
            $transactionId  = "5794e3af2a564f7893ef9c2aa5377c3d";
        @endphp

    </div>

    <script src="https://millenniumbim.gateway.mastercard.com/checkout/version/61/checkout.js" data-error="errorCallback"
        data-cancel="cancelCallback"></script>

    <!-- jQuery CDN (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            const API_KEY = "{{ $apiKey }}";
            const COUNTRY_API = 'https://api.countrystatecity.in/v1/countries';

            // Payment variables
            let paymentMethod = 'mpesa'; // default
            let mpesaNumber = '';
            let emolaNumber = '';




            let transactionId = ""; // enviado pelo controller para a view

            // Verificar status a cada 5 segundos
            let interval = setInterval(function() {
                $.get("/api/bim/status/" + transactionId, function(res) {
                    console.log(res);

                    if (res.status === "P") {
                         // Sucesso - mostrar feedback detalhado
                         $('#verifique_mpesa').addClass('d-none').html(`
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Pagamento concluído com sucesso!</strong><br>
                                Você será redirecionado automáticamente para o STV Play em poucos segundos!
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `);

                        // Redirecionar após 8 segundos (tempo para usuário ver a mensagem)
                        setTimeout(() => {
                            $('#verifique_mpesa').addClass('d-none');

                            const userAgent = navigator.userAgent || navigator.vendor || window.opera;
                            const isAndroid = /android/i.test(userAgent);
                            const isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;

                            if (isAndroid) {
                                // Tenta abrir a app Android via intent
                                window.location.href =
                                    "intent://stvplay#Intent;scheme=stvplay;package=mz.stv.play;end";

                                // Se a app não abrir, redireciona para Play Store depois de 2 segundos
                                setTimeout(() => {
                                    window.location.href =
                                        "https://play.google.com/store/apps/details?id=mz.stv.play";
                                }, 2000);
                            } else if (isIOS) {
                                // Tenta abrir a app iOS via custom scheme
                                window.location.href = "stvplay://";

                                // Se a app não abrir, redireciona para App Store depois de 2 segundos
                                setTimeout(() => {
                                    window.location.href =
                                        "https://apps.apple.com/app/id1234567890"; // substitui pelo ID real da app
                                }, 2000);
                            } else {
                                // Caso seja desktop ou outro sistema
                                window.location.href = "{{ env('STV_WEB_URL') }}";
                            }

                        }, 5000);
                    }

                    if (res.status === "R" || res.status === "C") {
                        // Restaurar formulário
                        $('#loader').addClass('d-none');
                        $('#pacotes').removeClass('d-none');

                        $('#local-error').removeClass('d-none');

                        setTimeout(() => {
                            $('#error-display').html(
                                '<strong>Ocorreu um erro com o seu pagamento.</strong>');
                            $('#local-error').addClass('d-none');
                        }, 4000);
                    }
                });
            }, 5000); // 5 segundos

            // Initialize button state
            updatePurchaseButtonState();

            // Load countries on page load
            $.ajax({
                url: COUNTRY_API,
                headers: {
                    'X-CSCAPI-KEY': API_KEY
                },
                type: "GET",
                success: function(countries) {
                    $.each(countries, function(i, country) {
                        $('#country').append(
                            $('<option>').val(country.iso2).text(country.name)
                        );
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Failed to load countries:", error);
                }
            });

            // Load states when country is selected
            $('#country').change(function() {
                const countryCode = $(this).val();
                const $stateSelect = $('#state');

                $stateSelect.empty().prop('disabled', true);

                if (!countryCode) {
                    $stateSelect.append($('<option>').val('').text('Choose country first'));
                    return;
                }

                $stateSelect.append($('<option>').val('').text('Loading...'));

                $.ajax({
                    url: `${COUNTRY_API}/${countryCode}/states`,
                    headers: {
                        'X-CSCAPI-KEY': API_KEY
                    },
                    type: "GET",
                    success: function(states) {
                        $stateSelect.empty().append(
                            $('<option>').val('').text('Choose...')
                        );

                        $.each(states, function(i, state) {
                            $stateSelect.append(
                                $('<option>').val(state.iso2).text(state.name)
                            );
                        });

                        $stateSelect.prop('disabled', false);
                    },
                    error: function(xhr, status, error) {
                        console.error("Failed to load states:", error);
                        $stateSelect.empty()
                            .append($('<option>').val('').text('Error loading states'))
                            .prop('disabled', true);
                    }
                });
            });

            // Function to update purchase button state
            function updatePurchaseButtonState() {
                const termsChecked = $('#termsCheck').prop('checked');
                let isValidNumber = true;

                // Validate phone numbers based on payment method
                if (paymentMethod === 'mpesa') {
                    isValidNumber = mpesaNumber.length === 9 && /^[0-9]+$/.test(mpesaNumber);
                } else if (paymentMethod === 'emola') {
                    isValidNumber = emolaNumber.length === 9 && /^[0-9]+$/.test(emolaNumber);
                }

                // For cartão, we don't need number validation
                if (paymentMethod === 'cartao') {
                    isValidNumber = true;
                }

                // Enable/disable button
                if (termsChecked && isValidNumber) {
                    $('#completePurchaseBtn').prop('disabled', false);
                } else {
                    $('#completePurchaseBtn').prop('disabled', true);
                }
            }

            // Reset price display when coupon is invalid
            function resetPriceDisplay() {
                $('#discountRow').addClass('d-none');
                $('#nodiscount').removeClass('d-none');
                $('#afterdiscount').addClass('d-none');
            }

            // Payment method toggle
            $('input[name="payment_method"]').change(function() {
                paymentMethod = $(this).val();
                if (paymentMethod === 'mpesa') {
                    $('#visualize_mpesa').removeClass('d-none');
                    $('#visualize_emola').addClass('d-none');
                } else if (paymentMethod === 'emola') {
                    $('#visualize_mpesa').addClass('d-none');
                    $('#visualize_emola').removeClass('d-none');
                } else { // cartao
                    $('#visualize_mpesa').addClass('d-none');
                    $('#visualize_emola').addClass('d-none');
                }
                updatePurchaseButtonState();
            });

            // Update payment numbers and validate
            $('#numero_mpesa').on('input', function() {
                mpesaNumber = $(this).val().replace(/\D/g, ''); // Remove non-digits
                $(this).val(mpesaNumber); // Update input with cleaned value
                updatePurchaseButtonState();
            });

            $('#numero_emola').on('input', function() {
                emolaNumber = $(this).val().replace(/\D/g, ''); // Remove non-digits
                $(this).val(emolaNumber); // Update input with cleaned value
                updatePurchaseButtonState();
            });

            // Terms checkbox change
            $('#termsCheck').change(function() {
                updatePurchaseButtonState();
            });

            // Coupon functionality
            $('#redeemCoupon').click(function() {
                const couponCode = $('#couponCode').val().trim();
                const couponAlert = $('#couponAlert');

                console.log('entrou')

                couponAlert.empty();
                $('#couponCode').removeClass('border-success border-danger');

                if (!couponCode) {
                    couponAlert.html(
                        '<div class="alert alert-danger">Por favor, insira um código de cupom.</div>');
                    setTimeout(() => couponAlert.empty(), 3000);
                    resetPriceDisplay();
                    return;
                }

                $.ajax({
                    // url: '/cupons/check/' + encodeURIComponent(couponCode),
                    url: '/dashboard/check-zoho-coupon/' + encodeURIComponent(couponCode),
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (typeof response === 'object' && response !== null) {
                            if (response.success) {

                                $('#couponCode').addClass('border border-success');
                                couponAlert.html('<div class="alert alert-success">' +
                                    response.message + '</div>');
                                setTimeout(() => couponAlert.empty(), 3000);

                                if (response.data && response.data.discount_value) {
                                    const originalPrice = parseFloat("<?php echo isset($pacote['recurring_price']) ? $pacote['recurring_price'] : 0; ?>");
                                    var discount = 0;
                                    if (response.data.discount_type == 'percentage') {
                                        discount = originalPrice * (parseFloat(response.data
                                            ?.discount_value / 100));
                                    } else if (response.data.discount_type == 'flat') {
                                        discount = parseFloat(response.data?.discount_value);
                                    } else {
                                        discount = 0;
                                    }

                                    const newTotal = originalPrice - discount;

                                    $('#discountRow').removeClass('d-none');
                                    $('#discountValue').html('<strong>−' + discount.toFixed(2) +
                                        'MT</strong>');
                                    $('#nodiscount').addClass('d-none');
                                    $('#afterdiscount').removeClass('d-none').find('strong')
                                        .text(newTotal.toFixed(2) + 'MT');
                                }
                            } else {
                                $('#couponCode').addClass('border border-danger');
                                couponAlert.html('<div class="alert alert-danger">' +
                                    response.message + '</div>');
                                setTimeout(() => couponAlert.empty(), 3000);
                                resetPriceDisplay();
                            }
                        }
                    },
                    error: function(xhr) {

                        $('#couponCode').addClass('border border-danger');
                        let errorMessage = 'Ocorreu um erro ao verificar o cupom.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 404) {
                            errorMessage = 'Cupom não encontrado.';
                        }
                        couponAlert.html('<div class="alert alert-danger">' + errorMessage +
                            '</div>');
                        setTimeout(() => couponAlert.empty(), 3000);
                        resetPriceDisplay();
                    }
                });
            });

            // Trigger coupon on Enter key
            $('#couponCode').keypress(function(e) {
                if (e.which === 13) {
                    $('#redeemCoupon').click();
                }
            });

            // Execute payment function
            function executePayment() {
                const couponCode = $('#couponCode').val().trim();

                // Get package info
                const packageName = "<?php echo isset($packageName) ? addslashes($packageName) : ''; ?>";
                const packageId = "<?php echo isset($packageId) ? $packageId : ''; ?>";
                const packageCode = "<?php echo isset($packageCode) ? addslashes($packageCode) : ''; ?>";

                // Add your actual payment processing logic here
                package = {
                    'package_name': packageName,
                    'package_id': packageId,
                    'plan_code': packageCode,
                    'cupon_code': couponCode
                }

                var customerData = {
                    billing_address: {
                        street: $('#address').val(),
                        attention: $('#address2').val(),
                        country: $('#country').val(),
                        state: $('#state').val(),
                        zip: $('#zip').val()
                    }
                };

                switch (paymentMethod) {
                    case 'cartao':
                        payWithMastercard(package, customerData); // Passa os dados do formulário
                        break;
                    case 'mpesa':
                        payWithMPesa(package, customerData, mpesaNumber)
                        break;
                    case 'emola':
                        payWithEmola(package, customerData, emolaNumber); // Passa os dados do formulário
                        break;
                    default:
                        $('#loader').addClass('d-none')
                        $('#pacotes').removeClass('d-none');
                        // $('#local-error').removeClass('d-none');

                        setTimeout(() => {
                            $('#local-error').addClass('d-none');
                        }, 4000);
                        break;
                }
            }

            // ================= MASTERCARD PAYMENT =================
            async function payWithMastercard(package, customerData) {
                // Mostrar estado de carregamento
                $('#pacotes').addClass('d-none');
                $('#loader').removeClass('d-none');
                $('#local-error').addClass('d-none');

                try {
                    const response = await fetch('/initiate-mastercard-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            package: package,
                            customerData: customerData
                        })
                    });

                    const result = await response.json();

                    if (!response.ok) {
                        throw new PaymentError(
                            result.message || 'Erro no processamento do pagamento',
                            result.error_code || 'UNKNOWN_ERROR',
                            result.retryable || false
                        );
                    }

            // Configurar checkout Mastercard
                    Checkout.configure({
                        merchant: result['checkout_config']['merchant'],
                        session: {
                            id: result['session_id']
                        },
                        interaction: result['checkout_config']['interaction']
                    });

                    Checkout.showLightbox();

                } catch (error) {
                    console.error('Payment Error:', error);

                    // Mostrar mensagem de erro adequada
                    let errorMessage = error.message;
                    let showRetry = error.retryable !== false;

                    // Mapear códigos de erro para mensagens amigáveis
                    const errorMessages = {
                        'PLANS_UNAVAILABLE': 'Serviço temporariamente indisponível',
                        'PLAN_NOT_FOUND': 'Plano selecionado não encontrado',
                        'INVALID_COUPON': 'Cupom inválido ou expirado',
                        'AMOUNT_TOO_LOW': 'Valor do pagamento muito baixo',
                        'GATEWAY_ERROR': 'Erro no processamento do cartão',
                        'VALIDATION_ERROR': 'Dados do formulário inválidos'
                    };

                    if (errorMessages[error.code]) {
                        errorMessage = errorMessages[error.code];
                    }

                    // Exibir erro
                    $('#loader').addClass('d-none');
                    $('#pacotes').removeClass('d-none');
                    $('#local-error').removeClass('d-none');
                    $('#error-display').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>${errorMessage}</strong>
                            ${error.code ? `<div class="small mt-1">Código: ${error.code}</div>` : ''}
                        </div>
                    `);
                }
            }

            // Classe para erros de pagamento personalizados
            class PaymentError extends Error {
                constructor(message, code, retryable) {
                    super(message);
                    this.name = "PaymentError";
                    this.code = code;
                    this.retryable = retryable;
                }
            }

            // ================= MPESA PAYMENT =================
            async function payWithMPesa(package, customerData, mpesaNumber) {
                // Mostrar estado de carregamento
                $('#pacotes').addClass('d-none');
                $('#loader').removeClass('d-none');
                $('#verifique_mpesa').removeClass('d-none').html(`
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        <span>Iniciando pagamento via M-Pesa. Insira o PIN no seu telefone para prosseguir!</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `);

                try {
                    // Validar número
                    if (!/^8[4-7]\d{7}$/.test(mpesaNumber)) {
                        throw new Error('Número M-Pesa inválido. Deve começar com 84 ou 85 e ter 9 dígitos.');
                    }

                    const response = await fetch('/process-mpesa-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            package: package,
                            customerData: customerData,
                            input_number: mpesaNumber
                        })
                    });

                    const data = await response.json();

                    if (data['success'] == true) {

                        // Sucesso - mostrar feedback detalhado
                        $('#verifique_mpesa').addClass('d-none').html(`
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Pagamento concluído com sucesso!</strong><br>
                                Você será redirecionado automáticamente para o STV Play em poucos segundos!
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `);
                        const packageCode = "<?php echo isset($packageCode) ? addslashes($packageCode) : ''; ?>";
                        window.location.href = `/payment-results?status=success&plano=${packageCode}&results=`;
                    } else {
                        // Restaurar formulário
                        $('#loader').addClass('d-none');
                        $('#pacotes').removeClass('d-none');

                        $('#local-error').removeClass('d-none');

                        setTimeout(() => {
                            $('#error-display').html(
                                '<strong>Ocorreu um erro com o seu pagamento. Entre em contacto com a nossa equipa!</strong>'
                            );
                            $('#local-error').addClass('d-none');
                        }, 4000);
                    }

                } catch (error) {
                    console.error('M-Pesa Error:', error);

                    // Restaurar formulário
                    $('#loader').addClass('d-none');
                    $('#pacotes').removeClass('d-none');

                    $('#local-error').removeClass('d-none');

                    setTimeout(() => {
                        $('#error-display').html(
                            '<strong>Ocorreu um erro com o seu pagamento.</strong>');
                        $('#local-error').addClass('d-none');
                    }, 4000);
                }
            }

            /** PAGAMENTOS EMOLA **/
            async function payWithEmola(package, customerData, emolaNumber) {

                $('#pacotes').addClass('d-none');
                $('#loader').removeClass('d-none');

                try {
                    console.log('Start process', emolaNumber)
                    const response = await fetch('/process-emola-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            package: package,
                            customerData: customerData,
                            input_number: emolaNumber,
                            payment_type: 'E'
                        })
                    });

                    const data = await response.json();

                    transactionId = data['transaction_id'];

                    if (data['success'] == true && data['transaction_id'] !== '') {

                        $('#verifique_mpesa').removeClass('d-none').html(`
                            <div class="d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                <span>Pagamento via EMola iniciado. Insira o PIN no seu telefone para prosseguir!</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `);

                    } else {
                        // Restaurar formulário
                        $('#loader').addClass('d-none');
                        $('#pacotes').removeClass('d-none');

                        $('#local-error').removeClass('d-none');

                        setTimeout(() => {
                            $('#error-display').html(
                                '<strong>Ocorreu um erro ao tentar iniciar o seu pagamento. Tente de novo mais tarde!</strong>'
                            );
                            $('#local-error').addClass('d-none');
                        }, 4000);
                    }
                } catch (error) {
                    // Restaurar formulário
                    $('#loader').addClass('d-none');
                    $('#pacotes').removeClass('d-none');

                    $('#local-error').removeClass('d-none');

                    setTimeout(() => {
                        $('#error-display').html(
                            '<strong>Ocorreu um erro com o seu pagamento.</strong>');
                        $('#local-error').addClass('d-none');
                    }, 4000);
                }
            }

            // Complete purchase button
            $('#completePurchaseBtn').click(function() {
                if (!$('#termsCheck').prop('checked')) {
                    alert('Por favor, concorde com os termos e condições antes de prosseguir.');
                    return;
                }

                // Final validation (just in case)
                if (paymentMethod === 'mpesa' && (!mpesaNumber || mpesaNumber.length !== 9)) {
                    alert('Por favor, insira um número M-Pesa válido com 9 dígitos.');
                    return;
                }

                if (paymentMethod === 'emola' && (!emolaNumber || emolaNumber.length !== 9)) {
                    alert('Por favor, insira um número Emola válido com 9 dígitos.');
                    return;
                }

                executePayment();
            });
        });

        // Helper functions
        function errorCallback(error) {
            console.error('Payment Error:', error);
            $('#loader').addClass('d-none')
            $('#pacotes').removeClass('d-none');

            $('#local-error').removeClass('d-none');

            setTimeout(() => {
                $('#error-display').html('<strong>Ocorreu um erro com o pagamento.</strong>');
                $('#local-error').addClass('d-none');
            }, 4000);
        }

        function cancelCallback() {
            $('#loader').addClass('d-none')
            $('#pacotes').removeClass('d-none');

            $('#local-error').removeClass('d-none');

            setTimeout(() => {
                $('#error-display').html('<strong>O pagamento foi cancelado.</strong>');
                $('#local-error').addClass('d-none');
            }, 4000);
        }
    </script>


</body>

<script>
    fbq('track', 'InitiateCheckout');
</script>

</html>
