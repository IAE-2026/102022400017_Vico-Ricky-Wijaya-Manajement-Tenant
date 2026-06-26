$baseUrl = "http://localhost:8000"
$nim = "102022400017"
$score = 0
$total = 9

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "   MINI GRADER - PENGUJIAN TUGAS 3 TENANT" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan

# 1. Endpoint menolak request tanpa X-IAE-KEY (Harus 401)
try {
    $r1 = Invoke-WebRequest -Uri "$baseUrl/api/v1/tenants" -Method GET -UseBasicParsing
    Write-Host "[GAGAL] 1. Menolak request tanpa X-IAE-KEY" -ForegroundColor Red
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 401) {
        Write-Host "[LULUS] 1. Menolak request tanpa X-IAE-KEY (Status 401)" -ForegroundColor Green; $score++
    } else {
        Write-Host "[GAGAL] 1. Menolak request tanpa X-IAE-KEY" -ForegroundColor Red
    }
}

# 2. Request dengan X-IAE-KEY (NIM) berhasil (Harus 200)
try {
    $h2 = @{"X-IAE-KEY"=$nim}
    $r2 = Invoke-WebRequest -Uri "$baseUrl/api/v1/tenants" -Method GET -Headers $h2 -UseBasicParsing
    if ($r2.StatusCode -eq 200) {
        Write-Host "[LULUS] 2. Request dengan X-IAE-KEY ($nim) berhasil (Status 200)" -ForegroundColor Green; $score++
    }
} catch {
    Write-Host "[GAGAL] 2. Request dengan X-IAE-KEY ($nim) berhasil" -ForegroundColor Red
}

# 3. GET /api/v1/ -> 200 + JSON wrapper
try {
    if ($r2.StatusCode -eq 200 -and ($r2.Content | ConvertFrom-Json).status -eq "success") {
        Write-Host "[LULUS] 3. GET /api/v1/tenants -> 200 + JSON wrapper Valid" -ForegroundColor Green; $score++
    }
} catch {
    Write-Host "[GAGAL] 3. GET /api/v1/tenants -> 200 + JSON wrapper" -ForegroundColor Red
}

# 4. GET /api/v1/{id} -> 404 + error wrapper
try {
    $r4 = Invoke-WebRequest -Uri "$baseUrl/api/v1/tenants/999999" -Method GET -Headers $h2 -UseBasicParsing
    Write-Host "[GAGAL] 4. GET /api/v1/{id} tidak valid -> 404 + error wrapper" -ForegroundColor Red
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 404) {
        $r4body = [System.IO.StreamReader]::new($_.Exception.Response.GetResponseStream()).ReadToEnd()
        if (($r4body | ConvertFrom-Json).status -eq "error") {
            Write-Host "[LULUS] 4. GET /api/v1/{id} tidak valid -> 404 + Error wrapper JSON Valid" -ForegroundColor Green; $score++
        }
    } else {
        Write-Host "[GAGAL] 4. GET /api/v1/{id} tidak valid -> 404 + error wrapper" -ForegroundColor Red
    }
}

# 5. POST /api/v1/ -> 201/422 + JSON wrapper
try {
    $h5 = @{"X-IAE-KEY"=$nim; "Content-Type"="application/json"}
    $rand = Get-Random -Minimum 10000 -Maximum 99999
    $body = "{`"name`":`"Bot Test`",`"email`":`"bot$rand@test.com`",`"phone`":`"000`",`"id_number`":`"999$rand`",`"address`":`"Jl`"}"
    $r5 = Invoke-WebRequest -Uri "$baseUrl/api/v1/tenants" -Method POST -Headers $h5 -Body $body -UseBasicParsing
    if ($r5.StatusCode -eq 201) {
        Write-Host "[LULUS] 5. POST /api/v1/tenants -> 201 + JSON wrapper Valid" -ForegroundColor Green; $score++
    }
} catch {
    if ($_.Exception.Response.StatusCode.Value__ -eq 422) {
        Write-Host "[LULUS] 5. POST /api/v1/tenants -> 422 + JSON wrapper Valid" -ForegroundColor Green; $score++
    } else {
        Write-Host "[GAGAL] 5. POST /api/v1/tenants -> 201 + JSON wrapper" -ForegroundColor Red
    }
}

# 6. Swagger UI dapat diakses
try {
    $r6 = Invoke-WebRequest -Uri "$baseUrl/docs" -Method GET -UseBasicParsing
    if ($r6.StatusCode -eq 200) {
        Write-Host "[LULUS] 6. Swagger UI dapat diakses (Status 200)" -ForegroundColor Green; $score++
    }
} catch {
    Write-Host "[GAGAL] 6. Swagger UI dapat diakses" -ForegroundColor Red
}

# 7. Swagger mencerminkan endpoint REST
try {
    $r7 = Invoke-WebRequest -Uri "$baseUrl/docs/api-docs" -Method GET -UseBasicParsing
    if ($r7.StatusCode -eq 200 -and $r7.Content -match "/tenants") {
        Write-Host "[LULUS] 7. Swagger mencerminkan endpoint REST (/tenants terbaca)" -ForegroundColor Green; $score++
    }
} catch {
    Write-Host "[GAGAL] 7. Swagger mencerminkan endpoint REST" -ForegroundColor Red
}

# 8. GraphQL Playground dapat diakses
try {
    $r8 = Invoke-WebRequest -Uri "$baseUrl/graphql-playground" -Method GET -UseBasicParsing
    if ($r8.StatusCode -eq 200 -and $r8.Content -match "graphql") {
        Write-Host "[LULUS] 8. GraphQL Playground dapat diakses (Status 200)" -ForegroundColor Green; $score++
    }
} catch {
    Write-Host "[GAGAL] 8. GraphQL Playground dapat diakses" -ForegroundColor Red
}

# 9. Query GraphQL (introspection) berhasil
try {
    $h9 = @{"Content-Type"="application/json"}
    $b9 = '{"query":"{__schema{queryType{name}}}"}'
    $r9 = Invoke-WebRequest -Uri "$baseUrl/graphql" -Method POST -Headers $h9 -Body $b9 -UseBasicParsing
    if ($r9.StatusCode -eq 200 -and $r9.Content -match "__schema") {
        Write-Host "[LULUS] 9. Query GraphQL (introspection) berhasil" -ForegroundColor Green; $score++
    }
} catch {
    Write-Host "[GAGAL] 9. Query GraphQL (introspection) berhasil" -ForegroundColor Red
}

Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "HASIL AKHIR: $score / $total BERHASIL" -ForegroundColor Yellow
if ($score -eq $total) {
    Write-Host "SELAMAT! Sistem Anda 100% Siap Dinilai Grader!" -ForegroundColor Green
} else {
    Write-Host "Masih ada yang gagal. Periksa kembali." -ForegroundColor Red
}
Write-Host "===============================================" -ForegroundColor Cyan
