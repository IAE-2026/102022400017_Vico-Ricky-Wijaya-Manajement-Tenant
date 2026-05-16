# 🤖 AI Prompting Log — Tugas 2 IAE

**Service:** Tenant Management Service  
**Mahasiswa:** [Nama Anda]  
**NIM:** [NIM Anda]  
**Mata Kuliah:** BBK2HAB3 - Integrasi Aplikasi Enterprise  

---

## Log Sesi 1 — Arsitektur & Setup Project

**Tanggal:** [Tanggal]  
**Tool AI:** Claude (Anthropic)

### Prompt 1
**Input:**
```
Saya diberi tugas membuat service Manajemen Tenant untuk matakuliah Integrasi Aplikasi Enterprise.
Service harus memiliki:
- POST /api/v1/tenants (penyewa daftar diri + upload dokumen)
- GET /api/v1/tenants (admin listing)
- GET /api/v1/tenants/{id} (admin ambil detail)
- Mengikuti Standard Integration Contract IAE-T2 (wrapper JSON, X-IAE-KEY auth)
- Swagger documentation
- GraphQL dengan Lighthouse
- Docker

Bantu saya membuat struktur project Laravel yang lengkap.
```

**Output AI:**
- Membuat struktur folder project Laravel 11
- Membuat `docker-compose.yml` dan `Dockerfile` untuk containerisasi
- Membuat nginx config di `docker/nginx.conf`

**Evaluasi:**
- Output sesuai kebutuhan
- Struktur folder mengikuti konvensi Laravel

---

### Prompt 2
**Input:**
```
Sekarang buat migration untuk tabel tenants dan contracts.
Tabel tenants harus punya: name, email, phone, id_number, address, occupation, 
emergency_contact, document_path, status (pending/verified/rejected), notes, verified_at.
Tabel contracts harus terhubung ke tenants dengan foreign key.
```

**Output AI:**
- Migration `create_tenants_table` dengan semua kolom yang diminta + softDeletes
- Migration `create_contracts_table` dengan foreign key ke tenants

**Evaluasi:**
- Foreign key constraint sudah benar
- Enum status sesuai alur bisnis

---

### Prompt 3
**Input:**
```
Buat Middleware untuk validasi X-IAE-KEY sesuai Standard Integration Contract IAE-T2.
Header key: X-IAE-KEY, value: NIM Mahasiswa.
Response error harus mengikuti wrapper format: {status, message, errors}.
```

**Output AI:**
- `CheckApiKey.php` middleware
- Return 401 jika header tidak ada
- Return 403 jika API key salah

**Evaluasi:**
- Sudah sesuai dengan spec IAE-T2
- Error response menggunakan wrapper yang benar

---

### Prompt 4
**Input:**
```
Buat TenantController dengan:
1. index() - listing semua tenant dengan pagination dan filter status
2. show($id) - detail tenant dengan relasi contracts
3. store() - registrasi tenant baru, upload dokumen, dan auto-create draft contract
4. verify() - admin verifikasi tenant

Semua response harus menggunakan wrapper JSON IAE-T2.
Tambahkan Swagger/OpenAPI annotations yang lengkap.
```

**Output AI:**
- Controller lengkap dengan 4 method
- Swagger annotations `@OA\Get`, `@OA\Post`, `@OA\Patch`
- Validasi input dengan `Validator::make()`
- Auto-generate contract number dengan `Str::random()`

**Evaluasi:**
- Status code sudah benar (200, 201, 404, 422)
- Swagger annotations mencakup semua endpoint

---

### Prompt 5
**Input:**
```
Buat GraphQL schema untuk Lighthouse dengan:
- Query tenants (list dengan filter)  
- Query tenant(id) (detail)
- Type Tenant dengan relasi contracts
- Type Contract dengan relasi tenant
- Enum untuk status
```

**Output AI:**
- `graphql/schema.graphql` lengkap
- Pagination menggunakan `@paginate`
- Relasi `@hasMany` dan `@belongsTo`
- Guard `@guard(with: ["iae-key"])`

**Evaluasi:**
- Schema mengikuti konvensi Lighthouse
- Tipe data sudah tepat

---

### Prompt 6
**Input:**
```
Buat README.md yang komprehensif dengan:
- Instruksi setup Docker step by step
- Dokumentasi semua endpoint dengan contoh curl
- Contoh query GraphQL
- Tabel compliance Standard Integration Contract
```

**Output AI:**
- README.md lengkap dengan semua instruksi
- Contoh cURL untuk setiap endpoint
- Contoh GraphQL query
- Tabel compliance IAE-T2

**Evaluasi:**
- Dokumentasi jelas dan mudah diikuti
- Mencakup semua requirement tugas

---

## Refleksi

### Apa yang dipelajari dari penggunaan AI:
1. AI sangat membantu dalam men-generate boilerplate code yang konsisten
2. Perlu tetap memvalidasi output AI terhadap requirement spesifik (IAE-T2 contract)
3. Prompting yang spesifik menghasilkan output yang lebih tepat sasaran
4. AI membantu memahami best practice Laravel (middleware, model relationship, dll)

### Bagian yang dikerjakan sendiri (tanpa AI):
- Memahami alur bisnis tenant management
- Menyesuaikan contract number format
- Testing endpoint di Postman
- Konfigurasi `.env` sesuai NIM

---

*Log ini dibuat sebagai bukti penggunaan AI secara transparan sesuai ketentuan tugas.*
