# Tenant Management Service

> **BBK2HAB3 - Integrasi Aplikasi Enterprise | Tugas 2**  
> Service untuk manajemen data penyewa (tenant) dalam ekosistem Enterprise.

---

## 📋 Deskripsi Service

Service ini menangani seluruh siklus manajemen penyewa, mulai dari pendaftaran mandiri, upload dokumen identitas, validasi oleh sistem, hingga pembuatan draft kontrak secara otomatis.

### Alur Bisnis

```
Penyewa Daftar (POST /api/v1/tenants)
       ↓
Sistem Simpan Profil + Validasi Data
       ↓
Sistem Buat Draft Kontrak Otomatis
       ↓
Admin Listing Tenant (GET /api/v1/tenants)
       ↓
Admin Lihat Detail (GET /api/v1/tenants/{id})
       ↓
Admin Verifikasi (PATCH /api/v1/tenants/{id}/verify)
```

---

## 🚀 Cara Menjalankan

### Prasyarat
- Docker & Docker Compose terinstall
- Git

### Langkah Setup

**1. Clone repository ini**
```bash
git clone https://github.com/[ORGANISASI]/[NIM]_Tenant-Service.git
cd [NIM]_Tenant-Service
```

**2. Salin file environment**
```bash
cp .env.example .env
```

**3. Edit `.env` — isi NIM Anda sebagai API Key**
```env
IAE_API_KEY=YOUR_NIM_HERE      # Ganti dengan NIM Anda!
APP_KEY=                        # Akan diisi otomatis di langkah 5
```

**4. Jalankan Docker**
```bash
docker-compose up -d --build
```

**5. Setup aplikasi Laravel (jalankan satu kali)**
```bash
# Generate APP_KEY
docker exec tenant-service-app php artisan key:generate

# Jalankan migrasi database
docker exec tenant-service-app php artisan migrate

# (Opsional) Isi data contoh
docker exec tenant-service-app php artisan db:seed

# Generate storage link
docker exec tenant-service-app php artisan storage:link

# Generate Swagger docs
docker exec tenant-service-app php artisan l5-swagger:generate
```

**6. Akses Service**

| URL | Keterangan |
|-----|------------|
| `http://localhost:8000/api/v1/health` | Health check |
| `http://localhost:8000/api/documentation` | Swagger UI |
| `http://localhost:8000/graphql-playground` | GraphQL Playground |
| `http://localhost:8000/graphql` | GraphQL Endpoint |

---

## 🔐 Autentikasi (Standard Integration Contract IAE-T2)

Semua endpoint wajib menyertakan header berikut:

```
X-IAE-KEY: [NIM_ANDA]
```

**Contoh di Postman / cURL:**
```bash
curl -H "X-IAE-KEY: 1234567890" http://localhost:8000/api/v1/tenants
```

---

## 📡 REST API Endpoints

Base URL: `http://localhost:8000/api/v1`

### 1. [Collection] GET /tenants — Daftar Semua Tenant

```bash
curl -X GET "http://localhost:8000/api/v1/tenants" \
     -H "X-IAE-KEY: YOUR_NIM" \
     -H "Content-Type: application/json"
```

**Query Parameters (opsional):**
- `status` : Filter berdasarkan status (`pending`, `verified`, `rejected`)
- `per_page` : Jumlah data per halaman (default: 15)
- `page` : Halaman ke-

**Response 200:**
```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Budi Santoso",
      "email": "budi@email.com",
      "phone": "081234567890",
      "status": "pending",
      "created_at": "2024-01-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "service_name": "Tenant-Service",
    "api_version": "v1",
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 2. [Resource] GET /tenants/{id} — Detail Tenant

```bash
curl -X GET "http://localhost:8000/api/v1/tenants/1" \
     -H "X-IAE-KEY: YOUR_NIM"
```

**Response 200:**
```json
{
  "status": "success",
  "message": "Data retrieved successfully",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@email.com",
    "status": "pending",
    "contracts": [
      {
        "id": 1,
        "contract_number": "DRAFT-ABCD1234-1",
        "status": "draft"
      }
    ]
  },
  "meta": {
    "service_name": "Tenant-Service",
    "api_version": "v1"
  }
}
```

**Response 404:**
```json
{
  "status": "error",
  "message": "Tenant not found",
  "errors": null
}
```

---

### 3. [Action] POST /tenants — Daftar Penyewa Baru

```bash
curl -X POST "http://localhost:8000/api/v1/tenants" \
     -H "X-IAE-KEY: YOUR_NIM" \
     -F "name=Budi Santoso" \
     -F "email=budi@email.com" \
     -F "phone=081234567890" \
     -F "id_number=3374010101990001" \
     -F "address=Jl. Merdeka No.1, Bandung" \
     -F "occupation=Software Engineer" \
     -F "emergency_contact=081299999999" \
     -F "document=@/path/to/ktp.jpg"
```

**Response 201:**
```json
{
  "status": "success",
  "message": "Tenant registered successfully. Your application is pending review.",
  "data": {
    "id": 1,
    "name": "Budi Santoso",
    "status": "pending",
    "contracts": [...]
  },
  "meta": {
    "service_name": "Tenant-Service",
    "api_version": "v1",
    "contract_draft": "DRAFT-ABCD1234-1"
  }
}
```

---

### 4. [Action] PATCH /tenants/{id}/verify — Admin Verifikasi Tenant

```bash
curl -X PATCH "http://localhost:8000/api/v1/tenants/1/verify" \
     -H "X-IAE-KEY: YOUR_NIM" \
     -H "Content-Type: application/json" \
     -d '{"status": "verified", "notes": "Dokumen lengkap dan valid"}'
```

---

## 🔮 GraphQL

Endpoint: `http://localhost:8000/graphql`  
Playground: `http://localhost:8000/graphql-playground`

> **Catatan:** Sertakan header `X-IAE-KEY: YOUR_NIM` di GraphQL Playground (menu HTTP Headers).

### Contoh Query — Daftar Tenant

```graphql
query GetTenants {
  tenants(first: 10) {
    data {
      id
      name
      email
      status
      contracts {
        contract_number
        status
      }
    }
    paginatorInfo {
      total
      currentPage
      lastPage
    }
  }
}
```

### Contoh Query — Detail Tenant

```graphql
query GetTenant {
  tenant(id: 1) {
    id
    name
    email
    phone
    id_number
    address
    occupation
    status
    verified_at
    contracts {
      id
      contract_number
      status
      approved_at
    }
  }
}
```

### Contoh Query — Filter by Status

```graphql
query GetPendingTenants {
  tenants(status: "pending", first: 5) {
    data {
      id
      name
      email
      status
      created_at
    }
  }
}
```

---

## 📦 Struktur Proyek

```
tenant-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   └── TenantController.php   # REST API Controller
│   │   └── Middleware/
│   │       └── CheckApiKey.php        # X-IAE-KEY Middleware
│   └── Models/
│       ├── Tenant.php                 # Model Tenant
│       └── Contract.php              # Model Kontrak
├── database/
│   ├── migrations/
│   │   ├── ..._create_tenants_table.php
│   │   └── ..._create_contracts_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── graphql/
│   └── schema.graphql                # GraphQL Schema (Lighthouse)
├── routes/
│   ├── api.php                       # REST API Routes
│   └── web.php
├── docker/
│   └── nginx.conf
├── docker-compose.yml
├── Dockerfile
├── .env.example
└── README.md
```

---

## 📊 Standard Integration Contract Compliance

| Requirement | Status | Keterangan |
|-------------|--------|------------|
| Protokol HTTP/1.1 | ✅ | Default Laravel |
| Format JSON + UTF-8 | ✅ | Semua response JSON |
| Wrapper `status` + `message` + `data` + `meta` | ✅ | Diterapkan di semua endpoint |
| X-IAE-KEY Header Auth | ✅ | Middleware `CheckApiKey` |
| GET /api/v1/tenants (Collection) | ✅ | Dengan pagination & filter |
| GET /api/v1/tenants/{id} (Resource) | ✅ | Dengan relasi kontrak |
| POST /api/v1/tenants (Action) | ✅ | Dengan upload dokumen |
| Swagger/OpenAPI Documentation | ✅ | Akses `/api/documentation` |
| GraphQL Query | ✅ | Via Lighthouse, playground tersedia |
| Docker | ✅ | docker-compose.yml |

---

## 📝 AI Prompting Log

Lihat file `AI_PROMPT_LOG.md` untuk rekap log prompting dengan AI.
