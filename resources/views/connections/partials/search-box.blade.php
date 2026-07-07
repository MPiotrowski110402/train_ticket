<form class="search-box" method="GET" action="{{ route('connections') }}">

    @php
        $selectedFromId = (string) ($filters['from'] ?? '');
        $selectedToId = (string) ($filters['to'] ?? '');

        $selectedFrom = $cities->firstWhere('id', (int) $selectedFromId);
        $selectedTo = $cities->firstWhere('id', (int) $selectedToId);
    @endphp

    <div class="route-input">

        <div class="field-group">
            <label for="fromCityButton">Skąd</label>

            <div class="city-picker" data-city-picker="from">
                <input
                    type="hidden"
                    name="from"
                    id="fromCity"
                    value="{{ $selectedFromId }}"
                >

                <button
                    type="button"
                    id="fromCityButton"
                    class="city-picker-trigger"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                >
                    <span class="city-picker-value">
                        {{ $selectedFrom ? $selectedFrom->name . ' Główne' : 'Wybierz miasto początkowe' }}
                    </span>

                    <span class="city-picker-chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="city-picker-menu" role="listbox" aria-label="Miasto początkowe">
                    @foreach ($cities as $city)
                        <button
                            type="button"
                            class="city-picker-option @selected((string) $city->id === $selectedFromId)"
                            role="option"
                            aria-selected="{{ (string) $city->id === $selectedFromId ? 'true' : 'false' }}"
                            data-value="{{ $city->id }}"
                            data-label="{{ $city->name }} Główne"
                        >
                            <span class="city-picker-pin">●</span>
                            {{ $city->name }} Główne
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <button
            type="button"
            class="swap-button"
            id="swapCities"
            aria-label="Zamień miasta"
            title="Zamień miasta"
        >
            ⇄
        </button>

        <div class="field-group">
            <label for="toCityButton">Dokąd</label>

            <div class="city-picker" data-city-picker="to">
                <input
                    type="hidden"
                    name="to"
                    id="toCity"
                    value="{{ $selectedToId }}"
                >

                <button
                    type="button"
                    id="toCityButton"
                    class="city-picker-trigger"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                >
                    <span class="city-picker-value">
                        {{ $selectedTo ? $selectedTo->name . ' Główne' : 'Wybierz miasto docelowe' }}
                    </span>

                    <span class="city-picker-chevron" aria-hidden="true">⌄</span>
                </button>

                <div class="city-picker-menu" role="listbox" aria-label="Miasto docelowe">
                    @foreach ($cities as $city)
                        <button
                            type="button"
                            class="city-picker-option @selected((string) $city->id === $selectedToId)"
                            role="option"
                            aria-selected="{{ (string) $city->id === $selectedToId ? 'true' : 'false' }}"
                            data-value="{{ $city->id }}"
                            data-label="{{ $city->name }} Główne"
                        >
                            <span class="city-picker-pin">●</span>
                            {{ $city->name }} Główne
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    <div class="route-options">

        <div>
            <label for="travelDate">Data</label>

            <input
                id="travelDate"
                type="date"
                name="date"
                min="{{ now()->toDateString() }}"
                value="{{ $filters['date'] ?? '' }}"
            >
        </div>

        <div>
            <label for="passengers">Pasażerowie</label>

            <select id="passengers" name="passengers">
                <option value="1">1 osoba</option>
                <option value="2">2 osoby</option>
                <option value="3">3 osoby</option>
                <option value="4">4 osoby</option>
            </select>
        </div>

        <button type="submit">
            🔎 Szukaj połączeń
        </button>

    </div>

</form>