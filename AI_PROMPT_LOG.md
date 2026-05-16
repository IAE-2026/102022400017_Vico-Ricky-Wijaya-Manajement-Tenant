# 🤖 AI Prompting Log — Tugas 2 IAE

**Service:** Tenant Management Service  
**Mahasiswa:** Vico Ricky Wijaya  
**NIM:** 102022400017  
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise  
**Tanggal Pengerjaan:** 17 Mei 2026  
**AI Tool:** Claude

---

## Sesi 1 — Pembuatan Struktur Project

### Prompt 1
**Input:**
```
Saya diberi tugas seperti diatas dan harus disesuaikan dengan standarisasinya sesuai pdf tersebut, 
yang dimana saya harus membuat service Manajement Tenant dengan fitur seperti ini:

Sistem mengharuskan penyewa membuat akun Data Diri POST /api/v1/[resource]
Penyewa daftarkan diri + upload data (Post)
Sistem menyimpan profil tenant dan memvalidasi
Sistem membuat Draft Kontrak yang sudah disetujui dengan Data Penyewa (Service C)
Admin melakukan Listing terhadap Tenant (Service C) (Get) Mengambil daftar data
Admin dapat mendapatkan info tertentu dari tenant (Service C) (Get) Mengambil data spesifik

Berdasarkan dari penjelasan saya diatas tolong bantu saya mengerjakan tugas tersebut yang dimana 
saya sudah membuat repository
jadi bantu saya mengerjakan ini hingga seluruh standarisasi dan tugasnya tercapai
```

---

## Sesi 2 — Setup dan Troubleshooting

### Prompt 2
**Input:**
```
terjadi error seperti ini (cp .env.example .env — Cannot find path)
```

---

### Prompt 3
**Input:**
```
tampilan ls pada folder sekarang (hanya ada folder tenant-service)
```

---

### Prompt 4
**Input:**
```
terjadi error saat aku memasukkan docker-compose up -d --build 
(unable to get image nginx:alpine — failed to connect to docker API)
```

---

### Prompt 5
**Input:**
```
failed to solve: process "/bin/sh -c chown -R www-data..." did not complete successfully
```

---

### Prompt 6
**Input:**
```
berikut tampilan setelah docker-compose up -d --build dan saat docker ps 
sudah menampilkan 3 container apakah sudah benar?
```

---

### Prompt 7
**Input:**
```
berikut adalah tampilan saat saya mengrun docker exec tenant-service-app composer install 
(100% selesai tapi ada Could not open input file: artisan)
```

---

### Prompt 8
**Input:**
```
hasil saat php artisan --version (Could not open input file: artisan)
```

---

### Prompt 9
**Input:**
```
hasil saat php artisan --version (bootstrap/cache directory must be present and writable)
```

---

### Prompt 10
**Input:**
```
terjadi error saat mengrun php artisan l5-swagger:generate 
(Class App\Http\Controllers\Controller not found)
```

---

### Prompt 11
**Input:**
```
terjadi error saat php artisan cache:clear 
(Table tenant_service.cache doesn't exist)
```

---

### Prompt 12
**Input:**
```
apakah error ini terjadi karena xampp saya belum saya hidupkan atau 
pada pengerjaan ini tidak diperlukan penggunaan xampp?
```

---

### Prompt 13
**Input:**
```
masih terjadi error swagger (HTTP 500) saat membuka /api/documentation
```

---

### Prompt 14
**Input:**
```
Swagger muncul tapi "Failed to load API definition" 
(Internal Server Error /docs)
```

---

### Prompt 15
**Input:**
```
ditampilkan bahwa api key tidak ditampilkan apakah hal tersebut masuk standarisasi?
```

---

### Prompt 16
**Input:**
```
response 403 Forbidden saat execute di Swagger (Invalid API Key)
```

---

### Prompt 17
**Input:**
```
GraphQL Playground muncul tapi "Failed to find class TenantPaginator"
```

---

### Prompt 18
**Input:**
```
saat aku mengklik run button nya tidak bisa apakah itu error?
```

---

### Prompt 19
**Input:**
```
apakah akan baik baik saja atau error? (git add . dengan warning LF/CRLF)