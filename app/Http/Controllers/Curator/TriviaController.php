<?php

namespace App\Http\Controllers\Curator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;

class TriviaController extends Controller
{
    protected $firestore;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firestore = $firebaseService->firestore();
    }

    // Show trivia only for a specific landmark
    public function index($landmarkId)
    {
        $landmark = $this->firestore->collection('landmarks')->document($landmarkId)->snapshot();

        if (!$landmark->exists()) {
            abort(404, 'Landmark not found.');
        }

        $triviaDocs = $this->firestore
            ->collection('landmarks')
            ->document($landmarkId)
            ->collection('trivia')
            ->documents();

        $allTrivia = [];

        foreach ($triviaDocs as $trivia) {
            $allTrivia[] = [
                'landmark_id'    => $landmarkId,
                'landmark_name'  => $landmark['name'] ?? 'Unnamed Landmark',
                'trivia_id'      => $trivia->id(),
                'question'       => $trivia['question'],
                'choices'        => $trivia['choices'],
                'correct_answer' => $trivia['correct_answer'],
            ];
        }

        $landmarkList = [[
            'id'   => $landmarkId,
            'name' => $landmark['name'] ?? 'Unnamed Landmark',
        ]];

        return view('curators.trivia.all', compact('allTrivia', 'landmarkList'));
    }

    // Show create form
    public function create($landmarkId)
    {
        return view('curators.trivia.create', compact('landmarkId'));
    }

    // Store new trivia
    public function store(Request $request, $landmarkId)
    {
        $request->validate([
            'question'        => 'required|string',
            'choices'         => 'required|array|min:2',
            'correct_answer'  => 'required|string|in:' . implode(',', $request->choices),
        ]);

        $this->firestore
            ->collection('landmarks')
            ->document($landmarkId)
            ->collection('trivia')
            ->add([
                'question'       => $request->question,
                'choices'        => $request->choices,
                'correct_answer' => $request->correct_answer,
                'created_at'     => now()
            ]);

        // 🔄 Redirect to all trivia instead of only landmark trivia
        return redirect()
            ->route('curators.trivia.all')
            ->with('success', 'Trivia added successfully.');
    }

    // Edit form
    public function edit($landmarkId, $triviaId)
    {
        $doc = $this->firestore
            ->collection('landmarks')
            ->document($landmarkId)
            ->collection('trivia')
            ->document($triviaId)
            ->snapshot();

        if (!$doc->exists()) {
            abort(404, 'Trivia not found.');
        }

        return view('curators.trivia.edit', [
            'landmarkId' => $landmarkId,
            'triviaId'   => $triviaId,
            'trivia'     => $doc->data()
        ]);
    }

    // Update trivia
    public function update(Request $request, $landmarkId, $triviaId)
    {
        $request->validate([
            'question'        => 'required|string',
            'choices'         => 'required|array|min:2',
            'correct_answer'  => 'required|string|in:' . implode(',', $request->choices),
        ]);

        $this->firestore
            ->collection('landmarks')
            ->document($landmarkId)
            ->collection('trivia')
            ->document($triviaId)
            ->set([
                'question'       => $request->question,
                'choices'        => $request->choices,
                'correct_answer' => $request->correct_answer,
                'updated_at'     => now()
            ], ['merge' => true]);

        // 🔄 Redirect to all trivia
        return redirect()
            ->route('curators.trivia.all')
            ->with('success', 'Trivia updated successfully.');
    }

    // Delete trivia
    public function destroy($landmarkId, $triviaId)
    {
        $this->firestore
            ->collection('landmarks')
            ->document($landmarkId)
            ->collection('trivia')
            ->document($triviaId)
            ->delete();

        return redirect()
            ->route('curators.trivia.all')
            ->with('success', 'Trivia deleted.');
    }

    // Show all trivia from all landmarks
    public function all()
    {
        $landmarks    = $this->firestore->collection('landmarks')->documents();
        $allTrivia    = [];
        $landmarkList = [];

        foreach ($landmarks as $landmark) {
            if (!$landmark->exists()) continue;

            $landmarkId   = $landmark->id();
            $landmarkName = $landmark['name'] ?? 'Unnamed Landmark';

            $landmarkList[] = [
                'id'   => $landmarkId,
                'name' => $landmarkName,
            ];

            $triviaDocs = $this->firestore
                ->collection('landmarks')
                ->document($landmarkId)
                ->collection('trivia')
                ->documents();

            foreach ($triviaDocs as $trivia) {
                if (!$trivia->exists()) continue;

                $allTrivia[] = [
                    'landmark_id'    => $landmarkId,
                    'landmark_name'  => $landmarkName,
                    'trivia_id'      => $trivia->id(),
                    'question'       => $trivia['question'],
                    'choices'        => $trivia['choices'],
                    'correct_answer' => $trivia['correct_answer'],
                ];
            }
        }

        return view('curators.trivia.all', compact('allTrivia', 'landmarkList'));
    }
}
