<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SoapAuditLog;

/**
 * SOAP Audit Service — Modul 2: Legacy SOAP/XML Client
 * Mengirim audit log transaksi kritis ke server IAE dalam format SOAP/XML
 */
class SoapAuditService
{
    protected string $baseUrl;
    protected string $teamId = 'TEAM-08'; // Kelompok 8

    public function __construct()
    {
        $this->baseUrl = config('iae.sso_url', 'https://iae-sso.virtualfri.id');
    }

    /**
     * Kirim audit log ke IAE Central via SOAP
     *
     * @param string $bearerToken  JWT token dari SSO
     * @param string $activityName Nama aktivitas bisnis
     * @param array  $logData      Data transaksi dalam format array (akan dikonversi ke JSON)
     */
    public function sendAudit(string $bearerToken, string $activityName, array $logData): array
    {
        // Transformasi data JSON → XML SOAP Envelope
        $soapEnvelope = $this->buildSoapEnvelope($activityName, $logData);

        Log::info('[SOAP] Mengirim audit', [
            'activity' => $activityName,
            'team'     => $this->teamId,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$bearerToken}",
                    'Content-Type'  => 'text/xml; charset=UTF-8',
                    'SOAPAction'    => '""',
                ])
                ->withBody($soapEnvelope, 'text/xml')
                ->post("{$this->baseUrl}/soap/v1/audit");

            $receiptNumber = $this->extractReceiptNumber($response->body());

            if ($response->successful() || $receiptNumber) {
                Log::info('[SOAP] Audit berhasil', [
                    'receipt' => $receiptNumber,
                    'status'  => $response->status(),
                ]);

                // Simpan receipt number ke database
                $this->saveAuditLog($activityName, $logData, $receiptNumber, 'success');

                return [
                    'success'        => true,
                    'receipt_number' => $receiptNumber,
                    'status_code'    => $response->status(),
                    'response_body'  => $response->body(),
                ];
            }

            Log::warning('[SOAP] Audit gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            $this->saveAuditLog($activityName, $logData, null, 'failed');

            return [
                'success'     => false,
                'message'     => 'SOAP audit gagal',
                'status_code' => $response->status(),
                'response'    => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('[SOAP] Exception', ['error' => $e->getMessage()]);
            $this->saveAuditLog($activityName, $logData, null, 'error');

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Build SOAP XML Envelope sesuai format IAE-T3
     * Transformasi: array PHP → JSON string → CDATA di dalam XML
     */
    public function buildSoapEnvelope(string $activityName, array $logData): string
    {
        $jsonContent = json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
    <soap:Body>
        <iae:AuditRequest>
            <iae:TeamID>{$this->teamId}</iae:TeamID>
            <iae:ActivityName>{$activityName}</iae:ActivityName>
            <iae:LogContent><![CDATA[{$jsonContent}]]></iae:LogContent>
        </iae:AuditRequest>
    </soap:Body>
</soap:Envelope>
XML;
    }

    /**
     * Ekstrak ReceiptNumber dari response SOAP XML
     */
    private function extractReceiptNumber(string $xmlBody): ?string
    {
        try {
            // Coba parse XML
            $xml = simplexml_load_string($xmlBody);
            if ($xml) {
                // Cari ReceiptNumber di berbagai kemungkinan namespace
                $namespaces = $xml->getNamespaces(true);
                foreach ($namespaces as $prefix => $ns) {
                    $xml->registerXPathNamespace($prefix ?: 'ns', $ns);
                }

                $results = $xml->xpath('//*[local-name()="ReceiptNumber"]');
                if (!empty($results)) {
                    return (string) $results[0];
                }
            }

            // Fallback: regex jika XML tidak bisa di-parse
            if (preg_match('/<[^>]*ReceiptNumber[^>]*>([^<]+)</', $xmlBody, $matches)) {
                return $matches[1];
            }

            // Cari format IAE-LOG-XXXX
            if (preg_match('/IAE-LOG-\d{4}-[A-Z0-9]+/', $xmlBody, $matches)) {
                return $matches[0];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Simpan audit log ke database lokal
     */
    private function saveAuditLog(
        string $activityName,
        array $logData,
        ?string $receiptNumber,
        string $status
    ): void {
        try {
            SoapAuditLog::create([
                'team_id'        => $this->teamId,
                'activity_name'  => $activityName,
                'log_content'    => json_encode($logData),
                'receipt_number' => $receiptNumber,
                'status'         => $status,
            ]);
        } catch (\Exception $e) {
            Log::warning('[SOAP] Gagal simpan audit log lokal: ' . $e->getMessage());
        }
    }
}
