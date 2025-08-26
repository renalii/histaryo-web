<?php

namespace App\Http\Controllers\Curator;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $firestore;

    public function __construct(FirebaseService $firebase)
    {
        $this->firestore = $firebase->firestore();
    }

    public function index()
    {
        $db = $this->firestore;

        $landmarksCount = $this->countDocuments($db->collection('landmarks')->documents());

        $triviaSnap   = $db->collectionGroup('trivia')->documents();
        $triviaCount  = $this->countDocuments($triviaSnap);

        $recentLogs = [];
        $logsSnap = $db->collection('logs')
            ->orderBy('timestamp', 'DESC')
            ->limit(10)
            ->documents();

        foreach ($logsSnap as $doc) {
            $d = $doc->data();
            $recentLogs[] = [
                'action'    => $d['action'] ?? 'Action',
                'email'     => $d['email'] ?? 'user@example.com',
                'timestamp' => $this->formatRelativeTime($d['timestamp'] ?? null),
            ];
        }

        $recentLandmarks = [];
        $landmarksRecentSnap = $db->collection('landmarks')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->documents();

        foreach ($landmarksRecentSnap as $doc) {
            $d = $doc->data();
            $recentLandmarks[] = [
                'id'         => $doc->id(),
                'name'       => $d['name'] ?? 'Untitled',
                'location'   => $d['location'] ?? null,
                'latitude'   => $d['latitude'] ?? null,
                'longitude'  => $d['longitude'] ?? null,
                'created_at' => $this->formatRelativeTime($d['created_at'] ?? null),
            ];
        }

        $pending = 0;

        return view('curators.dashboard', [
            'stats' => [
                'landmarks' => $landmarksCount,
                'trivia'    => $triviaCount,          
                'pending'   => $pending,
                'logs'      => count($recentLogs),
            ],
            'recentLandmarks' => $recentLandmarks,
            'recentLogs'      => $recentLogs,
        ]);
    }

    private function countDocuments($snapshot): int
    {
        
        if (is_object($snapshot) && method_exists($snapshot, 'size')) {
            return (int) $snapshot->size();
        }

        if (is_object($snapshot) && method_exists($snapshot, 'rows')) {
            $rows = $snapshot->rows();
            return is_array($rows) ? count($rows) : (int) iterator_count($rows);
        }

        $count = 0;
        foreach ($snapshot as $_) { $count++; }
        return $count;
    }

    private function formatRelativeTime($value): string
    {
        if (!$value) return '—';

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->diffForHumans();
        }

        if (is_object($value) && method_exists($value, 'get')) {
            try {
                $dt = $value->get(); 
                if ($dt instanceof \DateTimeInterface) {
                    return Carbon::instance($dt)->diffForHumans();
                }
            } catch (\Throwable $e) {
                // fall through to generic parse
            }
        }

        try {
            return Carbon::parse((string) $value)->diffForHumans();
        } catch (\Throwable $e) {
            return '—';
        }
    }
}
