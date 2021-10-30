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
            <div class="centerDiv">
                <div id="url-input">
                    <form action="/" method="GET">
                        <input  type="url" name="url" id="url" placeholder="Copy url to this field... ">
                        <button type="submit" class="btn">
                            OK
                        </button>
                    </form>
                </div>
                <div id="url-info" style="padding-top: 20px">
                    <h3>Results from:</h3>
                    @if (isset($url))
                        <a href="{{$url}}">{{$url}}</a>
                    @endif
                </div>

                @if (isset($data['image']))
                    <img id="AdvertisementImage" src="{{url('images/AdvertisementThumbnails/' . "Thumbnail " . $data['image'])}}" alt="{{ $data['image'] }}">
                @endif

                <div>
                    <table>
                        <tr>
                            <th>Field</th>
                            <th>Parsed data</th>
                        </tr>
                        <tr>
                            <td>title</td>
                            <td>
                                @if (isset($data['title']))
                                    {{ $data['title'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>year</td>
                            <td>
                                @if (isset($data['year']))
                                    {{ $data['year'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>mileage</td>
                            <td>
                                @if (isset($data['mileage']))
                                    {{ $data['mileage'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>price</td>
                            <td>
                                @if (isset($data['price']))
                                    {{ $data['price'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>make_model</td>
                            <td>
                                @if (isset($data['make_model']))
                                    {{ $data['make_model'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>fuel</td>
                            <td>
                                @if (isset($data['fuel']))
                                    {{ $data['fuel'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>body_type</td>
                            <td>
                                @if (isset($data['body_type']))
                                    {{ $data['body_type'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>views</td>
                            <td>
                                @if (isset($data['views']))
                                    {{ $data['views'] }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>description</td>
                            <td>
                                @if (isset($data['description']))
                                    {{ $data['description'] }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
