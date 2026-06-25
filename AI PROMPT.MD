# User Inputs

### 1.
Pada File PDF Tugas Besar  Tugas 3 - The Enterprise Digital City baca ketentuan untuk TUGAS 3 Saja, dan pada file URL dan Akun Tugas IAE merupakan Endpoint yang dimiliki pada SSO yang disediakan, Pada Kontrak Kelompok 8 saya (Vico) memiliki aktivitas manajement tenant. Saya mau kamu untuk mensamakan kodingan laravel yang sudah saya berikan  berdasarkan ketentuan TUGAS 3 saja lalu berikan langkah langkah untuk mengerjakan

### 2.
Continue

### 3.
Emang harus membaca di browser ya?bukannya sudah dikirim kan?

### 4.
PS C:\Users\ASUS\Downloads\102022400017_Vico-Ricky-Wijaya-Manajement-Tenant> docker exec tenant-service-app php artisan migrate
Could not open input file: artisan

### 5.
Continue

### 6.
Lanjutkan

### 7.
coba baca dulu file URL dan Akun Tugas IAE disana sudah tertera email dan password dan aku dikasih email warga17, coba step by step dari situ dari mengambil bearer token

### 8.
Mengambil token bearer telrbih dahulu di postman dengan URL dan Akun Tugas IAE

### 9.
Selanjutnya ngapain setelah mendapatkan bearer tokennya

### 10.
ini adalah identitas untuk masuk ke SSO nya , coba tinjau kode yang sudah ada berdasarkan ketentuan tugas 3 dan URL dan Akun Tugas IAE

### 11.
kenapa terjadi error seperti ini?, emang di ketentuan tugasnya menggunakan token IAE yang itu?

### 12.
Jadi dibagian .env persisnya pada DB_CONNECTION=mysql problem utamanya adalah hal tersebut tolong bantu saya jadikan menjadi sqllite

### 13.
Lanjutkan

### 14.
Coba endpoint API yang sesuai dengan aktivitas saya saja (Vico)

### 15.
Coba endpoint di postman beserta bodynya

### 16.
Mengapa headernya menggunakan X-IAE-KEY, tidak usah gunakan X-IAE-KEY ikuti saja ketentuan yang sudah ada

### 17.
Kenapa URL nya seperti itu dan apakah itu sudah menjadi hal penting dalam aktivitas probis saya (Vico)

### 18.
ini endpointnya sesuaikan

### 19.
Pada bagian no 1 Body (Form-data) buat menjadi Raw

### 20.
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>TEAM-07</iae:TeamID>
            <iae:ActivityName>VehicleCreated</iae:ActivityName>
            <iae:LogContent><![CDATA[{"license_plate":"D 9999 MAN","type":"Truck","brand":"Hino","status":"active"}]]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>

COBA BUATKAN TAPI SESUAIKAN SAMA DATA YANG TADI UDAH DIBUAT

### 21.
"name": "Vico Ricky",
    "email": "vico@tenant.com",
    "phone": "08123456789",
    "id_number": "33740101019900022",
    "address": "Jl. Merdeka No.1, Bandung",
    "occupation": "Software Engineer",
    "emergency_contact": "08129999999"
Buatkan 10 body yang berbeda

### 22.
kenapa massagebroker tidak bertambah saat aku mengesend endpoint nya

### 23.
siapa pengirimnya? karen contohnya disana seperti Team-07

### 24.
apakah benar yang ini?

### 25.
Bisakah kamu menggabungkan seluruh prompt saya? karena saya akan menjadikkanya kedalam file md
### 9.
Okee nampaknya sudah semua, tolong bantu saya untuk Mengupdate Hasil pengerjaan saya di github saya sebelumnya

