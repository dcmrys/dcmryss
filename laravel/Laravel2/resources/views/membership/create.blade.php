<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/novelle-logo.svg') }}?v=3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <title>Membership application | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/css/burgundy.css'])
</head>
<body class="auth-body application-body">
    <main class="application-page">
        <header class="auth-header application-header">
            <a class="auth-brand" href="{{ route('login') }}" aria-label="Novelle home">
                <img class="auth-brand-mark" src="{{ asset('images/novelle-logo.svg') }}?v=3" alt="">
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
                    <div id="map" class="application-map" role="region" aria-label="Choose a location on the map"></div>
                    <div class="application-form-grid application-coordinates">
                        <label class="application-field"><span>Latitude</span><input id="lat" name="latitude" type="number" step="any" min="-90" max="90" value="{{ old('latitude', '14.5995') }}"></label>
                        <label class="application-field"><span>Longitude</span><input id="lon" name="longitude" type="number" step="any" min="-180" max="180" value="{{ old('longitude', '120.9842') }}"></label>
                    </div>
                    <p class="application-weather" role="status"><span id="weather-icon" class="application-weather-icon" aria-hidden="true">⛅</span><span class="application-weather-copy"><span id="weather">Weather has not been loaded.</span> · Weather data: <a href="https://open-meteo.com/" target="_blank" rel="noopener">Open-Meteo</a></span></p>
                </div>

                <div class="application-submit"><p>We’ll send your reference number to the email address above.</p><button type="submit">Submit application <span aria-hidden="true">→</span></button></div>
            </form>
        </section>
        <footer class="application-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></footer>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
    <script>
        const latInput = document.getElementById('lat');
        const lonInput = document.getElementById('lon');
        const weatherPanel = document.getElementById('weather');
        const weatherIcon = document.getElementById('weather-icon');
        const weatherUrl = @json(route('weather.show'));
        const weatherIcons = {sun: '☀️', 'partly-cloudy': '⛅', cloud: '☁️', rain: '🌧️', snow: '❄️', storm: '⛈️'};
        function readCoordinates() {
            if (latInput.value === '' || lonInput.value === '') return null;
            const lat = Number(latInput.value), lon = Number(lonInput.value);
            if (!Number.isFinite(lat) || !Number.isFinite(lon) || Math.abs(lat) > 90 || Math.abs(lon) > 180) return null;
            return [lat, lon];
        }
        async function loadWeather() {
            const coordinates = readCoordinates();
            if (!coordinates) {
                weatherPanel.textContent = 'Enter valid coordinates to load weather.';
                return;
            }
            const [lat, lon] = coordinates;
            weatherPanel.textContent = 'Loading weather...';
            try {
                const response = await fetch(`${weatherUrl}?lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lon)}`);
                if (!response.ok) throw new Error('Weather unavailable');
                const weather = await response.json();
                weatherIcon.textContent = weatherIcons[weather.icon] || '☁️';
            weatherPanel.textContent = `${weather.place}: ${weather.temperature}°C, ${weather.description}, humidity ${weather.humidity}%`;
            } catch {
                weatherIcon.textContent = '☁️';
                weatherPanel.textContent = 'Weather is currently unavailable.';
            }
        }
        const initialCoordinates = readCoordinates() || [14.5995, 120.9842];
        const map = L.map('map').setView(initialCoordinates, 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        const marker = L.marker(initialCoordinates).addTo(map);

        function setLocation(lat, lon, recenter = false) {
            latInput.value = lat.toFixed(7);
            lonInput.value = lon.toFixed(7);
            marker.setLatLng([lat, lon]);
            if (recenter) map.setView([lat, lon], Math.max(map.getZoom(), 13));
            loadWeather();
        }
        function updateFromCoordinates() {
            const coordinates = readCoordinates();
            if (!coordinates) {
                weatherPanel.textContent = 'Enter valid coordinates to load weather.';
                return;
            }
            setLocation(coordinates[0], coordinates[1], true);
        }
        map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng));
        latInput.addEventListener('change', updateFromCoordinates);
        lonInput.addEventListener('change', updateFromCoordinates);
        loadWeather();
    </script>
</body>
</html>
