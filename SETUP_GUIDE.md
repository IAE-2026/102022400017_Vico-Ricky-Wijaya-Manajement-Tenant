# 🛠️ Panduan Setup Lengkap — Tenant Management Service

Panduan ini menjelaskan cara menjalankan service dari nol hingga semua fitur berjalan.

---

## Step 1: Upload ke Repository GitHub

### Jika repository sudah ada (clone dari organisasi dosen)

```bash
# 1. Clone repo kosong dari organisasi
git clone https://github.com/[NAMA_ORGANISASI]/[NIM]_Tenant-Service.git
cd [NIM]_Tenant-Service

# 2. Copy semua file dari folder ini ke dalam folder repo
#    (copy semua file kecuali .git)

# 3. Push ke GitHub
git add .
git commit -m "feat: initial tenant management service setup"
git push origin main
```

---

## Step 2: Konfigurasi Environment

```bash
# Salin file .env
cp .env.example .env
```

Edit file `.env`, sesuaikan bagian ini:
```env
APP_NAME="Tenant Service"
APP_URL=http://localhost:8000

# DATABASE (biarkan default jika pakai docker-compose)
DB_HOST=db
DB_DATABASE=tenant_service
DB_USERNAME=tenant_user
DB_PASSWORD=secret

# ⚠️ WAJIB: Ganti dengan NIM Anda
IAE_API_KEY=1234567890
```

---

## Step 3: Jalankan Docker

```bash
# Build dan jalankan semua container
docker-compose up -d --build

# Cek apakah container berjalan
docker ps
```

Seharusnya muncul 3 container:
- `tenant-service-app` (PHP-FPM)
- `tenant-service-nginx` (Web Server, port 8000)
- `tenant-service-db` (MySQL, port 3306)

---

## Step 4: Setup Laravel

Jalankan perintah berikut **satu kali** setelah container pertama kali dijalankan:

```bash
# 1. Generate APP_KEY (wajib!)
docker exec tenant-service-app php artisan key:generate

# 2. Tunggu DB siap (~10 detik), lalu jalankan migrasi
docker exec tenant-service-app php artisan migrate

# 3. Isi data contoh (opsional, untuk demo)
docker exec tenant-service-app php artisan db:seed

# 4. Buat symbolic link storage
docker exec tenant-service-app php artisan storage:link

# 5. Generate dokumentasi Swagger
docker exec tenant-service-app php artisan l5-swagger:generate
```

---

## Step 5: Verifikasi Semua Berjalan

### Health Check
Buka browser: `http://localhost:8000/api/v1/health`

Respons yang diharapkan:
```json
{
  "status": "success",
  "message": "Tenant Service is running",
  "data": { "service": "Tenant-Service", "version": "v1" }
}
```

### Swagger UI
Buka: `http://localhost:8000/api/documentation`

### GraphQL Playground
Buka: `http://localhost:8000/graphql-playground`

Di Playground, klik **HTTP HEADERS** di bawah dan tambahkan:
```json
{
  "X-IAE-KEY": "NIM_ANDA"
}
```

---

## Step 6: Testing dengan Postman

1. Buka Postman
2. Import file `Tenant-Service.postman_collection.json`
3. Set variable `nim` dengan NIM Anda
4. Jalankan request satu per satu

---

## Troubleshooting

### Container tidak mau start
```bash
docker-compose down
docker-compose up -d --build
```

### Error "could not find driver" (PDO MySQL)
```bash
docker exec tenant-service-app php artisan config:clear
docker exec tenant-service-app php artisan cache:clear
```

### Migration gagal (connection refused)
Tunggu 15-20 detik setelah `docker-compose up` lalu coba lagi. MySQL butuh waktu untuk inisialisasi.

### Swagger tidak muncul
```bash
docker exec tenant-service-app php artisan l5-swagger:generate
```

### Storage permission error
```bash
docker exec tenant-service-app chmod -R 775 /var/www/storage
docker exec tenant-service-app chown -R www-data:www-data /var/www/storage
```

---

## Checklist Penilaian

- [ ] `docker-compose up` berhasil (semua 3 container running)
- [ ] `GET /api/v1/tenants` — mengembalikan list + pagination
- [ ] `GET /api/v1/tenants/{id}` — mengembalikan detail + contracts
- [ ] `POST /api/v1/tenants` — membuat tenant baru + draft contract
- [ ] Response menggunakan wrapper `{status, message, data, meta}`
- [ ] `X-IAE-KEY` bekerja (401 tanpa key, 403 key salah)
- [ ] `GET /api/v1/tenants/9999` mengembalikan 404 dengan format benar
- [ ] Swagger UI dapat diakses di `/api/documentation`
- [ ] GraphQL query `tenants` berhasil di Playground
- [ ] GraphQL query `tenant(id: 1)` berhasil di Playground
