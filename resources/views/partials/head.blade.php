<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

<title>{{ $title ?? config('app.name', 'Gestor de contactos') }}</title>
<meta name="description" content="Gestor de contactos: personas, segmentos e importación de CSV en un solo lugar." />

<link rel="icon" type="image/svg+xml" href="/favicon.svg" />

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=fraunces:400,500,600,700|manrope:400,500,600,700,800" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
