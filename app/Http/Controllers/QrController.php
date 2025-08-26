<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Firestore\FieldValue;

class QrController extends Controller
{
    public function __construct(private FirebaseService $firebase)
    {
        // Only curators/admins should access this in routes via middleware.
    }

    private function fs()
    {
        return $this->firebase->firestore();
    }

    /**
     * GET /curators/qr
     * Lists existing QR mappings and shows a form to create new ones.
     */
    public function index(Request $request)
    {
        // Fetch all QR mappings
        $qrDocs = $this->fs()->collection('qr_codes')->orderBy('code')->documents();
        $qrs = [];
        foreach ($qrDocs as $doc) {
            if (!$doc->exists()) continue;
            $d = $doc->data();
            $qrs[] = [
                'id'           => $doc->id(),
                'code'         => $d['code'] ?? '',
                'landmark_id'  => $d['landmark_id'] ?? '',
                'created_at'   => $d['created_at'] ?? null,
                'download_url' => route('curators.qr.download', $doc->id()),
                'resolve_url'  => route('qr.resolve', ['id' => $d['code'] ?? '']),
            ];
        }

        // Fetch landmarks (id + name) for selection
        $lmSnap = $this->fs()->collection('landmarks')->orderBy('name')->documents();
        $landmarks = [];
        foreach ($lmSnap as $lm) {
            if (!$lm->exists()) continue;
            $landmarks[] = [
                'id'   => $lm->id(),
                'name' => $lm['name'] ?? 'Untitled',
            ];
        }

        return view('curators.qr.index', compact('qrs', 'landmarks'));
    }

    /**
     * POST /curators/qr
     * Create a mapping: code -> landmark_id. Optionally generate and store a QR image.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|max:120',
            'landmark_id' => 'required|string',
            'format'      => 'nullable|in:png,svg', // optional: desired generated file format
        ]);

        $code = trim($data['code']);
        $landmarkId = $data['landmark_id'];
        $format = $data['format'] ?? 'png';

        // Ensure code is unique (Firestore query)
        $existing = $this->fs()->collection('qr_codes')->where('code', '==', $code)->limit(1)->documents();
        foreach ($existing as $ex) {
            if ($ex->exists()) {
                return back()->withErrors(['error' => 'QR code already exists. Choose another value.'])->withInput();
            }
        }

        // Ensure landmark exists
        $lm = $this->fs()->collection('landmarks')->document($landmarkId)->snapshot();
        if (!$lm->exists()) {
            return back()->withErrors(['error' => 'Selected landmark does not exist.'])->withInput();
        }

        // Create Firestore doc
        $qrRef = $this->fs()->collection('qr_codes')->add([
            'code'        => $code,
            'landmark_id' => $landmarkId,
            'created_at'  => FieldValue::serverTimestamp(),
        ]);

        // Try to generate and store a QR image (optional)
        $saved = $this->generateQrImage($code, $format);

        return redirect()->route('curators.qr')
            ->with('success', 'QR mapping created' . ($saved ? ' and image generated.' : '.'));
    }

    /**
     * DELETE /curators/qr/{id}
     * Remove the mapping and any stored image file.
     */
    public function destroy(string $id)
    {
        $docRef = $this->fs()->collection('qr_codes')->document($id);
        $doc = $docRef->snapshot();
        if ($doc->exists()) {
            $code = (string) ($doc['code'] ?? '');
            // Delete Firestore mapping
            $docRef->delete();

            // Delete generated files if present
            foreach (['png', 'svg'] as $ext) {
                $path = "qrcodes/{$code}.{$ext}";
                try { Storage::disk('public')->delete($path); } catch (\Throwable $e) {}
            }
        }

        return back()->with('success', 'QR mapping deleted.');
    }

    /**
     * GET /curators/qr/{id}/download
     * Streams a QR code image (generate on-the-fly if needed).
     */
    public function download(string $id)
    {
        $doc = $this->fs()->collection('qr_codes')->document($id)->snapshot();
        if (!$doc->exists()) abort(404);

        $code = (string) ($doc['code'] ?? '');
        if ($code === '') abort(404);

        // Prefer existing PNG file; otherwise generate SVG on the fly.
        $pngPath = "qrcodes/{$code}.png";
        if (Storage::disk('public')->exists($pngPath)) {
            return response()->download(Storage::disk('public')->path($pngPath), "{$code}.png");
        }

        // Generate a fresh SVG in-memory
        $url = route('qr.resolve', ['id' => $code]);
        $svg = $this->makeQrSvg($url);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="'.$code.'.svg"',
        ]);
    }

    /**
     * Attempt to generate and save a QR image to storage/app/public/qrcodes/{code}.{ext}
     * Returns true on success, false otherwise.
     */
    private function generateQrImage(string $code, string $format = 'png'): bool
    {
        $url = route('qr.resolve', ['id' => $code]);
        $dir = 'qrcodes';
        $ext = in_array($format, ['png','svg']) ? $format : 'png';
        $path = "{$dir}/{$code}.{$ext}";

        try {
            // Ensure symlink exists: php artisan storage:link
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }

            // Try Simple QrCode first (if installed)
            if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
                $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::format($ext)
                    ->size(600)->margin(1)
                    ->generate($url);

                Storage::disk('public')->put($path, $qr);
                return true;
            }

            // Fallback: inline SVG (works even without the package)
            if ($ext === 'svg') {
                $svg = $this->makeQrSvg($url);
                Storage::disk('public')->put($path, $svg);
                return true;
            }

            // If PNG requested but no package, skip silently
            return false;

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Minimal SVG QR (fallback). For high quality, install simple-qrcode package.
     * NOTE: This is a placeholder; for production-quality PNG/SVG, use the package.
     */
    private function makeQrSvg(string $text): string
    {
        // Basic placeholder: render the URL as text inside a framed box.
        // Replace with a real encoder if needed.
        $safe = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="600">
  <rect width="100%" height="100%" fill="#ffffff"/>
  <rect x="10" y="10" width="580" height="580" fill="none" stroke="#000" stroke-width="6"/>
  <text x="50%" y="50%" font-family="monospace" font-size="18" text-anchor="middle">
    {$safe}
  </text>
  <text x="50%" y="570" font-family="monospace" font-size="14" text-anchor="middle" fill="#666">
    (Install simple-qrcode for scannable codes)
  </text>
</svg>
SVG;
    }
}
