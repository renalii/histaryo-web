<?php

namespace App\Http\Controllers\Curator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Pagination\LengthAwarePaginator;

class LandmarkController extends Controller
{
    protected $firestore;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firestore = $firebaseService->firestore();
    }

    public function map(Request $request, $id = null)
    {
        $landmarksRef = $this->firestore->collection('landmarks');
        $documents = $landmarksRef->documents();

        $landmarks = [];
        foreach ($documents as $doc) {
            $data = $doc->data();

            // Normalize coords: prefer latitude/longitude, fallback to lati/longti
            $lat = $data['latitude'] ?? $data['lati'] ?? null;
            $lng = $data['longitude'] ?? $data['longti'] ?? null;

            if (is_numeric($lat) && is_numeric($lng)) {
                $data['latitude']  = (float) $lat;
                $data['longitude'] = (float) $lng;
                $landmarks[] = array_merge($data, ['id' => $doc->id()]);
            }
        }

        $mapboxToken = config('services.mapbox.token');
        
        return view('curators.landmarks.map', compact('landmarks', 'mapboxToken'));
    }

    public function index(Request $request)
    {
        $snapshot = $this->firestore->collection('landmarks')->documents();
        $items = collect($snapshot->rows());

        $perPage = 3;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $paginated = new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path'  => url()->current(),
                'query' => $request->query(),
            ]
        );

        return view('curators.landmarks.index', ['landmarks' => $paginated]);
    }

    public function create()
    {
        return view('curators.landmarks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'lati' => 'nullable|numeric',
            'longti' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'video_url' => 'nullable|url',
            'image' => 'nullable|image|max:2048',
        ]);

        $lat = $request->latitude ?? $request->lati;
        $lng = $request->longitude ?? $request->longti;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('landmarks', 'public');
        }

        $this->firestore->collection('landmarks')->add([
            'name' => $request->name,
            'description' => $request->description,
            'lati' => is_numeric($lat) ? (float) $lat : null,
            'longti' => is_numeric($lng) ? (float) $lng : null,
            'latitude' => is_numeric($lat) ? (float) $lat : null,
            'longitude' => is_numeric($lng) ? (float) $lng : null,
            'video_url' => $request->video_url,
            'image_path' => $imagePath,
            'created_at' => now(),
        ]);

        $this->firestore->collection('logs')->add([
            'email' => Session::get('email'),
            'action' => 'Added a Landmark',
            'timestamp' => now()->toISOString(),
        ]);

        return redirect()->route('landmarks.index')->with('success', 'Landmark added!');
    }

    public function edit($id)
    {
        $doc = $this->firestore->collection('landmarks')->document($id)->snapshot();
        if (!$doc->exists()) abort(404);
        return view('curators.landmarks.edit', ['id' => $id, 'landmark' => $doc->data()]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'lati' => 'nullable|numeric',
            'longti' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'video_url' => 'nullable|url',
            'image' => 'nullable|image|max:2048',
        ]);

        $docRef = $this->firestore->collection('landmarks')->document($id);
        $doc = $docRef->snapshot();
        if (!$doc->exists()) abort(404);

        $data = $doc->data();

        if ($request->hasFile('image')) {
            if (!empty($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }
            $data['image_path'] = $request->file('image')->store('landmarks', 'public');
        }

        $lat = $request->latitude ?? $request->lati ?? $data['latitude'] ?? $data['lati'] ?? null;
        $lng = $request->longitude ?? $request->longti ?? $data['longitude'] ?? $data['longti'] ?? null;

        $docRef->set([
            'name' => $request->name,
            'description' => $request->description,
            'lati' => is_numeric($lat) ? (float) $lat : null,
            'longti' => is_numeric($lng) ? (float) $lng : null,
            'latitude' => is_numeric($lat) ? (float) $lat : null,
            'longitude' => is_numeric($lng) ? (float) $lng : null,
            'video_url' => $request->video_url,
            'image_path' => $data['image_path'] ?? null,
        ], ['merge' => true]);

        $this->firestore->collection('logs')->add([
            'email' => Session::get('email'),
            'action' => 'Updated a Landmark',
            'timestamp' => now()->toISOString(),
        ]);

        return redirect()->route('landmarks.index')->with('success', 'Updated successfully');
    }

    public function destroy($id)
    {
        $doc = $this->firestore->collection('landmarks')->document($id)->snapshot();
        if ($doc->exists()) {
            $data = $doc->data();
            if (!empty($data['image_path'])) {
                Storage::disk('public')->delete($data['image_path']);
            }
            $this->firestore->collection('landmarks')->document($id)->delete();

            $this->firestore->collection('logs')->add([
                'email' => Session::get('email'),
                'action' => 'Deleted a Landmark',
                'timestamp' => now()->toISOString(),
            ]);
        }

        return redirect()->route('landmarks.index')->with('success', 'Deleted successfully');
    }

    public function show($id)
    {
        $doc = $this->firestore->collection('landmarks')->document($id)->snapshot();
        if (!$doc->exists()) abort(404);
        return view('curators.landmarks.show', [
            'landmark' => $doc->data(),
            'id' => $doc->id(),
        ]);
    }
}
