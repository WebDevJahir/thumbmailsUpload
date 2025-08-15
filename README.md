# 📸 Thumbnail Processing Dashboard

A **Laravel 12** backend paired with a **React** frontend for real-time image thumbnail processing. Users can submit image URLs, monitor their processing status, and receive instant notifications via Pusher.

---

## 🚀 Features

-   Upload and process image URLs (one per line)
-   Real-time status updates (Processed, Pending, Failed)
-   Status filtering
-   Notification dropdown for updates
-   Responsive UI with Shopify Polaris
-   Queue-based job processing

---

## 🛠️ Prerequisites

-   **Node.js** (v18.x or higher)
-   **PHP** (v8.2 or higher)
-   **Composer**
-   **MySQL** or compatible DB
-   **Git**
-   **Pusher Account**

---

## ⚙️ Installation

### 1. Clone the Repository

```bash
git clone https://github.com/WebDevJahir/thumbnail-processor.git
cd thumbnail-processor
```

### 2. Backend Setup (Laravel)

Install PHP dependencies:

```bash
composer install
```

Copy and configure environment:

```bash
cp .env.example .env
```

Edit `.env` and update:

```
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=your_pusher_cluster

APP_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

Generate app key:

```bash
php artisan key:generate
```

Migrate and seed database:

```bash
php artisan migrate --seed
```

Install Sanctum & Broadcasting:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

composer require laravel/broadcasting
php artisan vendor:publish --tag=laravel-assets
```

### 3. Frontend Setup (React + Vite)

Install Node dependencies:

```bash
npm install
```

Ensure Polaris compatibility:

```bash
npm install @shopify/polaris@^10.0.0
```

### 4. Configure Pusher

Sign up at [Pusher](https://pusher.com/) and get your app credentials.

Ensure your `vite.config.js` includes:

```js
import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [react()],
    server: {
        hmr: { host: "localhost" },
    },
});
```

### 5. Run the Project

Start Laravel backend:

```bash
php artisan serve
```

Start React frontend:

```bash
npm run dev
```

Run queue worker (in a separate terminal):

```bash
php artisan queue:work --queue=priority-3,priority-2,priority-1
```

---

## 🔐 Access the App

Visit: [http://localhost:5173/login](http://localhost:5173/login)

Use default credentials (e.g., `free@example.com`, password: `password`) or register a new user.

---

## 🧭 Usage

-   **Login:** Access the dashboard with your credentials
-   **Dashboard:** Paste image URLs (one per line), filter by status, view real-time updates and notifications
-   **Logout:** Click the logout button in the header

---

## 🧪 Troubleshooting

-   **404 Errors:** Ensure `APP_URL` in `.env` matches your Laravel server
-   **Pusher Issues:** Check credentials and Pusher debug console
-   **Polaris Errors:** Update to Polaris ^10.0.0 and clear cache:

    ```bash
    rm -rf node_modules/.vite
    npm run dev
    ```

-   **Database Errors:** Verify with:

    ```bash
    php artisan migrate:status
    ```

---

## 🙏 Acknowledgements

-   Built with Laravel 12
-   UI powered by Shopify Polaris
-   Real-time events
