<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <title>{{ (isset($metadata['title']) ? $metadata['title'] . ' - ' : '') . config('app.name', 'Laravel') }}</title>
    <link rel="shortcut icon" href="{{ Vite::asset('resources/drezoc/images/favicon.ico') }}">

    @include('partials.styles')
    @stack('styles')
</head>
