{{-- Logo MPPS - Gobierno Bolivariano de Venezuela (PNG) --}}
@php
    $logoPath = public_path('images/logo-mpps.png');
    $logoData = file_get_contents($logoPath);
    $logoBase64 = base64_encode($logoData);
@endphp
<img src="data:image/png;base64,{{ $logoBase64 }}" style="width: 100px; height: 60px;" alt="Logo MPPS" />
