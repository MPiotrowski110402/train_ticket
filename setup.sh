#!/usr/bin/env bash
set -e

# =========================================================
# Railway Booking - skrypt inicjalizacyjny
# Uruchom z katalogu głównego projektu (tam gdzie docker-compose.yml):
#   chmod +x setup.sh && ./setup.sh
# =========================================================

echo "==> [1/8] Budowanie obrazów Dockera..."
docker compose build

echo "==> [2/8] Tworzenie projektu Laravel w ./src (jeśli jeszcze nie istnieje)..."
if [ ! -f "./src/artisan" ]; then
    docker compose run --rm app composer create-project laravel/laravel .
else
    echo "    Laravel już istnieje w ./src, pomijam."
fi

echo "==> [3/8] Instalacja pakietów (Livewire, Reverb, Predis)..."
docker compose run --rm app composer require livewire/livewire laravel/reverb predis/predis

echo "==> [4/8] Instalacja Reverb (config broadcasting + klucze w .env)..."
docker compose run --rm app php artisan install:broadcasting --reverb

echo "==> [5/8] Tworzenie pliku bazy SQLite..."
touch ./src/database/database.sqlite

echo "==> [6/8] Kopiowanie przygotowanych migracji do projektu..."
cp -f ./prepared-migrations/*.php ./src/database/migrations/ 2>/dev/null || true

echo "==> [7/8] Start wszystkich kontenerów..."
docker compose up -d

echo "==> [8/8] Generowanie klucza aplikacji i uruchomienie migracji..."
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate

echo ""
echo "GOTOWE. Pamiętaj, żeby ręcznie dopisać w src/.env wartości z pliku env.snippet"
echo "(Redis, Reverb, SQLite) - patrz env.snippet w tym katalogu."
echo ""
echo "Aplikacja: http://localhost:8000"
echo "Reverb (WS): ws://localhost:8080"
