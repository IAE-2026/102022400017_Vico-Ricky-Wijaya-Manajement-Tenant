<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Tenant Management Service API",
 *     description="API untuk manajemen penyewa (tenant) - BBK2HAB3 Integrasi Aplikasi Enterprise Tugas 2",
 *     @OA\Contact(email="your.email@student.telkomuniversity.ac.id"),
 * )
 *
 * @OA\Server(
 *     url="/api/v1",
 *     description="Tenant Service API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="X-IAE-KEY",
 *     type="apiKey",
 *     in="header",
 *     name="X-IAE-KEY",
 *     description="API Key authentication. Value: NIM Mahasiswa"
 * )
 *
 * @OA\Tag(name="Tenants", description="Endpoint manajemen data penyewa")
 * @OA\Tag(name="Contracts", description="Endpoint manajemen kontrak penyewa")
 */
class TenantController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tenants",
     *     summary="Mengambil daftar semua tenant",
     *     description="Admin dapat melihat daftar semua penyewa beserta status dan kontrak terbaru mereka.",
     *     tags={"Tenants"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status (pending, verified, rejected)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","verified","rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Jumlah data per halaman (default: 15)",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Halaman ke-",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Daftar tenant berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Tenant")),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="service_name", type="string", example="Tenant-Service"),
     *                 @OA\Property(property="api_version", type="string", example="v1"),
     *                 @OA\Property(property="total", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="API Key tidak ditemukan"),
     *     @OA\Response(response=403, description="API Key tidak valid")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::with('latestContract');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $tenants = $query->paginate($perPage);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data retrieved successfully',
            'data'    => $tenants->items(),
            'meta'    => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
                'total'        => $tenants->total(),
                'per_page'     => $tenants->perPage(),
                'current_page' => $tenants->currentPage(),
                'last_page'    => $tenants->lastPage(),
            ],
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/tenants/{id}",
     *     summary="Mengambil detail tenant berdasarkan ID",
     *     description="Admin dapat melihat detail informasi seorang penyewa beserta seluruh kontraknya.",
     *     tags={"Tenants"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID Tenant",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Data tenant berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Data retrieved successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/TenantDetail"),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="service_name", type="string", example="Tenant-Service"),
     *                 @OA\Property(property="api_version", type="string", example="v1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tenant tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Tenant not found"),
     *             @OA\Property(property="errors", type="null", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="API Key tidak ditemukan"),
     *     @OA\Response(response=403, description="API Key tidak valid")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $tenant = Tenant::with('contracts')->find($id);

        if (! $tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tenant not found',
                'errors'  => null,
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Data retrieved successfully',
            'data'    => $tenant,
            'meta'    => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/tenants",
     *     summary="Mendaftarkan penyewa baru",
     *     description="Penyewa mendaftarkan diri dengan mengisi data diri dan upload dokumen identitas.",
     *     tags={"Tenants"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","email","phone","id_number","address"},
     *                 @OA\Property(property="name", type="string", example="Budi Santoso"),
     *                 @OA\Property(property="email", type="string", format="email", example="budi@email.com"),
     *                 @OA\Property(property="phone", type="string", example="08123456789"),
     *                 @OA\Property(property="id_number", type="string", example="3374010101990001", description="NIK KTP"),
     *                 @OA\Property(property="address", type="string", example="Jl. Merdeka No.1, Bandung"),
     *                 @OA\Property(property="occupation", type="string", example="Software Engineer"),
     *                 @OA\Property(property="emergency_contact", type="string", example="08129999999"),
     *                 @OA\Property(property="document", type="string", format="binary", description="Foto/scan KTP (jpg, jpeg, png, pdf - maks 5MB)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pendaftaran tenant berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Tenant registered successfully. Your application is pending review."),
     *             @OA\Property(property="data", ref="#/components/schemas/TenantDetail"),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="service_name", type="string", example="Tenant-Service"),
     *                 @OA\Property(property="api_version", type="string", example="v1")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validasi gagal",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="API Key tidak ditemukan"),
     *     @OA\Response(response=403, description="API Key tidak valid")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|unique:tenants,email',
            'phone'             => 'required|string|max:20',
            'id_number'         => 'required|string|max:20|unique:tenants,id_number',
            'address'           => 'required|string',
            'occupation'        => 'nullable|string|max:100',
            'emergency_contact' => 'nullable|string|max:20',
            'document'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $documentPath         = null;
        $documentOriginalName = null;

        if ($request->hasFile('document')) {
            $file                 = $request->file('document');
            $documentOriginalName = $file->getClientOriginalName();
            $documentPath         = $file->store('documents/tenants', 'public');
        }

        $tenant = Tenant::create([
            'name'                  => $request->name,
            'email'                 => $request->email,
            'phone'                 => $request->phone,
            'id_number'             => $request->id_number,
            'address'               => $request->address,
            'occupation'            => $request->occupation,
            'emergency_contact'     => $request->emergency_contact,
            'document_path'         => $documentPath,
            'document_original_name'=> $documentOriginalName,
            'status'                => 'pending',
        ]);

        // Auto-generate draft contract when tenant registers
        $contract = Contract::create([
            'tenant_id'       => $tenant->id,
            'contract_number' => 'DRAFT-' . strtoupper(Str::random(8)) . '-' . $tenant->id,
            'status'          => 'draft',
            'terms'           => 'Kontrak ini adalah draft yang dibuat secara otomatis setelah pendaftaran tenant. Menunggu verifikasi admin.',
        ]);

        $tenant->load('contracts');

        return response()->json([
            'status'  => 'success',
            'message' => 'Tenant registered successfully. Your application is pending review.',
            'data'    => $tenant,
            'meta'    => [
                'service_name'   => 'Tenant-Service',
                'api_version'    => 'v1',
                'contract_draft' => $contract->contract_number,
            ],
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/tenants/{id}/verify",
     *     summary="Admin memverifikasi tenant",
     *     description="Admin mengubah status tenant menjadi verified atau rejected.",
     *     tags={"Tenants"},
     *     security={{"X-IAE-KEY":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"verified","rejected"}, example="verified"),
     *             @OA\Property(property="notes", type="string", example="Dokumen lengkap dan valid")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status tenant berhasil diperbarui"),
     *     @OA\Response(response=404, description="Tenant tidak ditemukan"),
     *     @OA\Response(response=401, description="API Key tidak ditemukan"),
     *     @OA\Response(response=403, description="API Key tidak valid")
     * )
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $tenant = Tenant::find($id);

        if (! $tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tenant not found',
                'errors'  => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:verified,rejected',
            'notes'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $tenant->update([
            'status'      => $request->status,
            'notes'       => $request->notes,
            'verified_at' => $request->status === 'verified' ? now() : null,
        ]);

        // Update contract status if tenant is verified
        if ($request->status === 'verified') {
            $tenant->latestContract()->update(['status' => 'approved', 'approved_at' => now()]);
        }

        $tenant->load('contracts');

        return response()->json([
            'status'  => 'success',
            'message' => 'Tenant status updated successfully',
            'data'    => $tenant,
            'meta'    => [
                'service_name' => 'Tenant-Service',
                'api_version'  => 'v1',
            ],
        ], 200);
    }
}

/**
 * @OA\Schema(
 *     schema="Tenant",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Budi Santoso"),
 *     @OA\Property(property="email", type="string", example="budi@email.com"),
 *     @OA\Property(property="phone", type="string", example="08123456789"),
 *     @OA\Property(property="id_number", type="string", example="3374010101990001"),
 *     @OA\Property(property="address", type="string", example="Jl. Merdeka No.1, Bandung"),
 *     @OA\Property(property="occupation", type="string", example="Software Engineer"),
 *     @OA\Property(property="emergency_contact", type="string", example="08129999999"),
 *     @OA\Property(property="document_original_name", type="string", example="ktp_budi.jpg"),
 *     @OA\Property(property="status", type="string", enum={"pending","verified","rejected"}, example="pending"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="verified_at", type="string", format="datetime", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime"),
 * )
 *
 * @OA\Schema(
 *     schema="TenantDetail",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Tenant"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="contracts",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Contract")
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="Contract",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="tenant_id", type="integer", example=1),
 *     @OA\Property(property="contract_number", type="string", example="DRAFT-ABCD1234-1"),
 *     @OA\Property(property="status", type="string", enum={"draft","approved","active","terminated"}, example="draft"),
 *     @OA\Property(property="start_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="end_date", type="string", format="date", nullable=true),
 *     @OA\Property(property="monthly_rent", type="number", format="float", nullable=true),
 *     @OA\Property(property="terms", type="string", nullable=true),
 *     @OA\Property(property="approved_at", type="string", format="datetime", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="datetime"),
 *     @OA\Property(property="updated_at", type="string", format="datetime")
 * )
 */
class TenantSchemas {}
