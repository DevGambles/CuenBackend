<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Cuenca verde</title>

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>

    <body cz-shortcut-listen="true">

        <div id="app">
            <br><br>
            <!-- Page Content -->
            <section>
                <div class="container">
                    <div class="row">
                        <div class="center-block">
                            <div class="col-md-12 text-center">
                                <center>
                                    <img src="{{ asset('images/Logo_cuenca.png') }}"/>
                                </center>
                                <h1>
                                    <b>404</b> - Lo sentimos
                                </h1>
                                <p>Lo sentimos, la página que está buscando no se pudo encontrar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Scripts -->
            <script src="{{ asset('js/app.js') }}"></script>
        </div>

    </body>

</html>