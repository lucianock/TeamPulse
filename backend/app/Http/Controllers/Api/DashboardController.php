<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard overview.
     */
    public function index(Request $request)
    {
        $stats = [
            'total_surveys' => Survey::count(),
            'active_surveys' => Survey::where('status', 'active')->count(),
            'total_responses' => SurveyResponse::count(),
            'recent_activity' => $this->getRecentActivity(),
        ];

        return response()->json([
            'stats' => $stats
        ]);
    }

    /**
     * Get survey statistics.
     */
    public function surveyStats(Request $request, Survey $survey)
    {
        $statistics = $survey->getStatistics();

        return response()->json([
            'statistics' => $statistics
        ]);
    }

    /**
     * Get recent activity.
     */
    public function recentActivity(Request $request)
    {
        $activity = $this->getRecentActivity();

        return response()->json([
            'activity' => $activity
        ]);
    }

    /**
     * Get recent activity based on user role.
     */
    private function getRecentActivity()
    {
        $activity = [];

        // Recent survey responses
        $recentResponses = SurveyResponse::with(['survey', 'question'])
            ->orderBy('submitted_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($recentResponses as $response) {
            $activity[] = [
                'type' => 'survey_response',
                'message' => 'New response submitted to "' . $response->survey->title . '"',
                'timestamp' => $response->submitted_at,
                'data' => $response
            ];
        }

        // Recent survey creations
        $recentSurveys = Survey::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        foreach ($recentSurveys as $survey) {
            $activity[] = [
                'type' => 'survey_created',
                'message' => 'Survey "' . $survey->title . '" was created',
                'timestamp' => $survey->created_at,
                'data' => $survey
            ];
        }

        // Sort by timestamp and return top 15
        usort($activity, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        return array_slice($activity, 0, 15);
    }
}