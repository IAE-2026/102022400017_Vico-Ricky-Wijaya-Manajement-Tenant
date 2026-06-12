<?php
/**
 * Script untuk membuat tabel-tabel yang dibutuhkan langsung via PDO SQLite
 * Dijalankan karena php artisan migrate hang akibat file locking pada Windows Docker bind mount
 */

$dbPath = '/tmp/database.sqlite';

try {
    $pdo = new PDO("sqlite:$dbPath", null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Koneksi SQLite berhasil di: $dbPath\n";

    // Tabel: migrations (wajib ada untuk Laravel)
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL
    )");
    echo "[OK] Tabel: migrations\n";

    // Tabel: tenants
    $pdo->exec("CREATE TABLE IF NOT EXISTS tenants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        verified_at DATETIME,
        created_at DATETIME,
        updated_at DATETIME
    )");
    echo "[OK] Tabel: tenants\n";

    // Tabel: contracts
    $pdo->exec("CREATE TABLE IF NOT EXISTS contracts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tenant_id INTEGER NOT NULL,
        contract_number VARCHAR(255),
        status VARCHAR(20) DEFAULT 'draft',
        start_date DATE,
        end_date DATE,
        monthly_rent DECIMAL(15,2),
        terms TEXT,
        approved_at DATETIME,
        created_at DATETIME,
        updated_at DATETIME,
        FOREIGN KEY (tenant_id) REFERENCES tenants(id)
    )");
    echo "[OK] Tabel: contracts\n";

    // Tabel: sso_users (Modul 1 Tugas 3)
    $pdo->exec("CREATE TABLE IF NOT EXISTS sso_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email VARCHAR(255) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        sso_subject VARCHAR(255),
        role VARCHAR(50) DEFAULT 'warga',
        jwt_payload TEXT,
        last_login DATETIME,
        created_at DATETIME,
        updated_at DATETIME
    )");
    echo "[OK] Tabel: sso_users (Modul 1 SSO)\n";

    // Tabel: soap_audit_logs (Modul 2 Tugas 3)
    $pdo->exec("CREATE TABLE IF NOT EXISTS soap_audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        team_id VARCHAR(50) DEFAULT 'TEAM-08',
        activity_name VARCHAR(255) NOT NULL,
        log_content TEXT,
        receipt_number VARCHAR(255),
        status VARCHAR(20) DEFAULT 'success',
        created_at DATETIME,
        updated_at DATETIME
    )");
    echo "[OK] Tabel: soap_audit_logs (Modul 2 SOAP)\n";

    // Insert tenant dummy supaya bisa langsung test
    $count = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO tenants (name, email, phone, address, status, created_at, updated_at)
            VALUES ('Vico Ricky', 'vico@tenant.com', '08123456789', 'Bandung', 'pending', datetime('now'), datetime('now'))");
        $pdo->exec("INSERT INTO contracts (tenant_id, contract_number, status, start_date, end_date, monthly_rent, terms, created_at, updated_at)
            VALUES (1, 'DRAFT-2026-001', 'draft', '2026-07-01', '2027-07-01', 2500000, 'Standard terms and conditions', datetime('now'), datetime('now'))");
        echo "[OK] Data dummy tenant (id=1) dan kontrak berhasil diinsert\n";
    }

    echo "\n=== Semua tabel berhasil dibuat! SQLite siap digunakan. ===\n";
    echo "Path DB: $dbPath\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
