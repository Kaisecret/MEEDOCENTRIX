<?php

namespace App\Http\Controllers\Fishport;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FishportPhoneUploadController extends Controller
{
    private const CACHE_PREFIX = 'fishport_phone_upload:';

    private const TOKEN_TTL_MINUTES = 20;

    /**
     * @var array<int, string>
     */
    private const ALLOWED_TARGET_INPUT_IDS = [
        'newCertificateOfOwnershipFile',
        'newPreviousRegistrationFile',
        'newBoatPermitLicenseFile',
        'newEngineReceiptProofFile',
        'newValidIdFile',
        'newInspectionCertificateFile',
        'editCertificateOfOwnershipFile',
        'editPreviousRegistrationFile',
        'editBoatPermitLicenseFile',
        'editEngineReceiptProofFile',
        'editValidIdFile',
        'editInspectionCertificateFile',
    ];

    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_input_id' => ['required', 'string'],
            'doc_label' => ['required', 'string', 'max:120'],
        ]);

        $targetInputId = (string) $validated['target_input_id'];
        if (! in_array($targetInputId, self::ALLOWED_TARGET_INPUT_IDS, true)) {
            return response()->json([
                'ok' => false,
                'message' => 'Unsupported document target.',
            ], 422);
        }

        $token = Str::random(48);
        $uploadUrl = route('fishport.phone_upload.show', ['token' => $token]);
        $statusUrl = route('fishport.phone_upload.status', ['token' => $token]);
        $expiresAt = now()->addMinutes(self::TOKEN_TTL_MINUTES);

        Cache::put($this->cacheKey($token), [
            'owner_user_id' => (int) ($request->user()?->id ?? 0),
            'target_input_id' => $targetInputId,
            'doc_label' => trim((string) $validated['doc_label']),
            'status' => 'waiting',
            'uploaded_path' => null,
            'uploaded_name' => null,
            'uploaded_mime' => null,
            'uploaded_size' => null,
            'uploaded_at' => null,
        ], $expiresAt);

        return response()->json([
            'ok' => true,
            'token' => $token,
            'upload_url' => $uploadUrl,
            'status_url' => $statusUrl,
            'expires_at' => $expiresAt->toIso8601String(),
            'expires_in_seconds' => self::TOKEN_TTL_MINUTES * 60,
        ]);
    }

    public function show(string $token): View
    {
        $payload = Cache::get($this->cacheKey($token));
        if (! is_array($payload)) {
            return view('fishport.phone_upload', [
                'token' => $token,
                'docLabel' => 'Document',
                'expired' => true,
                'uploaded' => false,
                'message' => 'This QR session has expired. Please generate a new QR from the Vessel Registry.',
            ]);
        }

        return view('fishport.phone_upload', [
            'token' => $token,
            'docLabel' => (string) ($payload['doc_label'] ?? 'Document'),
            'expired' => false,
            'uploaded' => ($payload['status'] ?? '') === 'uploaded',
            'message' => null,
        ]);
    }

    public function upload(Request $request, string $token): RedirectResponse
    {
        $cacheKey = $this->cacheKey($token);
        $payload = Cache::get($cacheKey);

        if (! is_array($payload)) {
            return redirect()
                ->route('fishport.phone_upload.show', ['token' => $token])
                ->with('error', 'Upload session expired. Please scan a new QR code.');
        }

        $validated = $request->validate([
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $uploadedFile = $validated['document'];
        $existingPath = isset($payload['uploaded_path']) ? (string) $payload['uploaded_path'] : '';
        if ($existingPath !== '' && Storage::disk('local')->exists($existingPath)) {
            Storage::disk('local')->delete($existingPath);
        }

        $storedPath = $uploadedFile->store('fishport/phone_uploads', 'local');
        $payload['status'] = 'uploaded';
        $payload['uploaded_path'] = $storedPath;
        $payload['uploaded_name'] = (string) $uploadedFile->getClientOriginalName();
        $payload['uploaded_mime'] = (string) ($uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType() ?: 'application/octet-stream');
        $payload['uploaded_size'] = (int) $uploadedFile->getSize();
        $payload['uploaded_at'] = now()->toIso8601String();

        Cache::put($cacheKey, $payload, now()->addMinutes(self::TOKEN_TTL_MINUTES));

        return redirect()
            ->route('fishport.phone_upload.show', ['token' => $token])
            ->with('status', 'Uploaded successfully. Return to your computer form.');
    }

    public function status(Request $request, string $token): JsonResponse
    {
        $payload = Cache::get($this->cacheKey($token));
        if (! is_array($payload)) {
            return response()->json([
                'ok' => false,
                'status' => 'expired',
                'message' => 'Session expired.',
            ], 404);
        }

        if ((int) ($payload['owner_user_id'] ?? 0) !== (int) ($request->user()?->id ?? 0)) {
            return response()->json([
                'ok' => false,
                'status' => 'forbidden',
                'message' => 'You are not allowed to access this upload session.',
            ], 403);
        }

        $isUploaded = ($payload['status'] ?? '') === 'uploaded';
        $response = [
            'ok' => true,
            'status' => $isUploaded ? 'uploaded' : 'waiting',
            'doc_label' => (string) ($payload['doc_label'] ?? 'Document'),
            'uploaded_name' => (string) ($payload['uploaded_name'] ?? ''),
            'uploaded_mime' => (string) ($payload['uploaded_mime'] ?? ''),
            'uploaded_size' => (int) ($payload['uploaded_size'] ?? 0),
            'uploaded_at' => $payload['uploaded_at'] ?? null,
        ];

        if ($isUploaded) {
            $response['file_url'] = route('fishport.phone_upload.file', ['token' => $token]);
        }

        return response()->json($response);
    }

    public function file(Request $request, string $token)
    {
        $payload = Cache::get($this->cacheKey($token));
        if (! is_array($payload)) {
            abort(404);
        }

        if ((int) ($payload['owner_user_id'] ?? 0) !== (int) ($request->user()?->id ?? 0)) {
            abort(403);
        }

        $uploadedPath = (string) ($payload['uploaded_path'] ?? '');
        if ($uploadedPath === '' || ! Storage::disk('local')->exists($uploadedPath)) {
            abort(404);
        }

        $absolutePath = Storage::disk('local')->path($uploadedPath);
        $mime = (string) ($payload['uploaded_mime'] ?? 'application/octet-stream');
        $name = (string) ($payload['uploaded_name'] ?? 'phone-upload');

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($name) . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function cacheKey(string $token): string
    {
        return self::CACHE_PREFIX . $token;
    }
}

