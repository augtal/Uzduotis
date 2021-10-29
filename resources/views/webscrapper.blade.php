<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Uzduotis</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <script type="text/javascript" src="{{ asset('js/scripts.js') }}"></script>
    </head>
    <body>
        <div class="container">
            <div>
                <div style="width:300px; margin:0 auto;">
                    <form action="/" method="GET">
                        <input  type="url" name="url" id="url" placeholder="Copy url to this field... ">
                        <button type="submit" class="btn">
                            OK
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
