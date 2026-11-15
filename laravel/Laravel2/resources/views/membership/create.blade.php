<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Membership application | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/burgundy.css'])
</head>
<body class="auth-body application-body">
    <main class="application-page">
        <header class="auth-header application-header">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="Novelle home">
                <span class="auth-brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg></span>
                <span>Novelle<span class="auth-brand-dot">.</span></span>
            </a>
            <a class="auth-header-link" href="{{ route('login') }}">Sign in <span aria-hidden="true">→</span></a>
        </header>

        <div class="application-heading">
            <h1>Membership application<span>.</span></h1>
            <p>Join the Novelle community. Complete the form below and we’ll email you a reference number.</p>
        </div>

        <section class="application-card" aria-label="Membership application form">
            @if(session('success'))<div class="application-alert application-alert-success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="application-alert application-alert-error" role="alert"><strong>Please check your information.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('membership.store') }}" class="application-form">
                @csrf
                <div class="application-section">
                    <div class="application-section-heading"><span class="application-number">01</span><div><h2>Membership</h2><p>Select a category for your application.</p></div></div>
                    <label class="application-field"><span>Membership category <em>*</em></span><select name="membership_category_id" required><option value="">Choose a category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('membership_category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                </div>

                <div class="application-section">
                    <div class="application-section-heading"><span class="application-number">02</span><div><h2>Your details</h2><p>Tell us how to reach you.</p></div></div>
                    <div class="application-form-grid">
                        <label class="application-field"><span>Full name <em>*</em></span><input name="full_name" value="{{ old('full_name') }}" maxlength="150" autocomplete="name" required></label>
                        <label class="application-field"><span>Email address <em>*</em></span><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required></label>
                        <label class="application-field"><span>Phone</span><input name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel"></label>
                        <label class="application-field"><span>Address</span><input name="address" value="{{ old('address') }}" maxlength="255" autocomplete="street-address"></label>
                    </div>
                </div>

                <div class="application-section">
                    <div class="application-section-heading"><span class="application-number">03</span><div><h2>Location</h2><p>Choose a point on the map or enter coordinates. This location is saved with your application.</p></div></div>
                    @if(config('services.google_maps.key'))
                        <div id="map" class="application-map" role="region" aria-label="Choose a location on the map"></div>
                    @else
                        <div class="application-map-fallback"><span aria-hidden="true">⌖</span><strong>Set your location</strong><p>Enter latitude and longitude below to choose your location.</p></div>
                    @endif
                    <div class="application-form-grid application-coordinates">
                        <label class="application-field"><span>Latitude</span><input id="lat" name="latitude" type="number" step="any" min="-90" max="90" value="{{ old('latitude', '14.5995') }}"></label>
                        <label class="application-field"><span>Longitude</span><input id="lon" name="longitude" type="number" step="any" min="-180" max="180" value="{{ old('longitude', '120.9842') }}"></label>
                    </div>
                    <p id="weather" class="application-weather" role="status">Weather has not been loaded.</p>
                </div>

                <div class="application-submit"><p>We’ll send your reference number to the email address above.</p><button type="submit">Submit application <span aria-hidden="true">→</span></button></div>
            </form>
        </section>
        <footer class="application-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></footer>
    </main>
    <script>
        const latInput = document.getElementById('lat');
        const lonInput = document.getElementById('lon');
        const weatherPanel = document.getElementById('weather');
        const weatherUrl = @json(route('weather.show'));
        let map, marker;
        async function loadWeather() {
            const lat = Number(latInput.value), lon = Number(lonInput.value);
            if (latInput.value === '' || lonInput.value === '' || !Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return;
            weatherPanel.textContent = 'Loading weather...';
            try {
                const response = await fetch(`${weatherUrl}?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}`);
                if (!response.ok) throw new Error('Weather unavailable');
                const weather = await response.json();
                weatherPanel.textContent = `${weather.place}: ${weather.temperature}°C, ${weather.description}, humidity ${weather.humidity}%`;
            } catch {
                weatherPanel.textContent = 'Weather is currently unavailable.';
            }
        }
        function setLocation(lat, lon) {
            latInput.value = lat.toFixed(7);
            lonInput.value = lon.toFixed(7);
            if (map) {
                if (marker) marker.setMap(null);
                marker = new google.maps.Marker({position: {lat, lng: lon}, map});
            }
            loadWeather();
        }
        function initMap() {
            const lat = Number(latInput.value), lon = Number(lonInput.value);
            map = new google.maps.Map(document.getElementById('map'), {center: {lat, lng: lon}, zoom: 13});
            map.addListener('click', event => setLocation(event.latLng.lat(), event.latLng.lng()));
            setLocation(lat, lon);
        }
        window.initMap = initMap;
        latInput.addEventListener('change', loadWeather);
        lonInput.addEventListener('change', loadWeather);
        @unless(config('services.google_maps.key'))loadWeather();@endunless
    </script>
    @if(config('services.google_maps.key'))
        <script async src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&amp;loading=async&amp;callback=initMap"></script>
    @endif
</body>
</html>
