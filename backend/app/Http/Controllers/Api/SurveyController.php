<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * Display a listing of surveys.
     */
    public function index(Request $request)
    {
        $surveys = Survey::with(['questions'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'surveys' => $surveys
        ]);
    }

    /**
     * Display the specified survey.
     */
    public function show(Request $request, Survey $survey)
    {
        $survey->load(['questions']);

        return response()->json([
            'survey' => $survey
        ]);
    }

    /**
     * Store a newly created survey.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'creator_name' => 'nullable|string|max:255',
            'creator_email' => 'nullable|email|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_anonymous' => 'boolean',
            'allow_multiple_responses' => 'boolean',
            'max_responses' => 'nullable|integer|min:1',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:500',
            'questions.*.type' => 'required|in:text,textarea,rating_1_5,rating_1_10,multiple_choice,single_choice,yes_no',
            'questions.*.options' => 'nullable|array',
            'questions.*.is_required' => 'boolean',
            'questions.*.order' => 'integer',
            'questions.*.help_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        }
        $survey = Survey::create([
            'title' => $request->title,
            'description' => $request->description,
            'creator_name' => $request->creator_name,
            'creator_email' => $request->creator_email,
            'access_code' => Survey::generateAccessCode(),
            'status' => 'draft',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_anonymous' => $request->is_anonymous ?? true,
            'allow_multiple_responses' => $request->allow_multiple_responses ?? false,
            'max_responses' => $request->max_responses,
        ]);

        // Create questions
        foreach ($request->questions as $questionData) {
            Question::create([
                'survey_id' => $survey->id,
                'question_text' => $questionData['question_text'],
                'type' => $questionData['type'],
                'options' => $questionData['options'] ?? null,
                'is_required' => $questionData['is_required'] ?? false,
                'order' => $questionData['order'] ?? 0,
                'help_text' => $questionData['help_text'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Survey created successfully',
            'survey' => $survey->load(['questions'])
        ], 201);
    }

    /**
     * Update the specified survey.
     */
    public function update(Request $request, Survey $survey)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'creator_name' => 'nullable|string|max:255',
            'creator_email' => 'nullable|email|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'is_anonymous' => 'boolean',
            'allow_multiple_responses' => 'boolean',
            'max_responses' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $survey->update($request->only([
            'title', 'description', 'creator_name', 'creator_email', 
            'start_date', 'end_date', 'is_anonymous', 'allow_multiple_responses', 'max_responses'
        ]));

        return response()->json([
            'message' => 'Survey updated successfully',
            'survey' => $survey->load(['questions'])
    }

    /**
     * Remove the specified survey.
     */
    public function destroy(Survey $survey)
    {
        $survey->delete();

        return response()->json([
            'message' => 'Survey deleted successfully'
        ]);
    }

    /**
     * Add a question to the survey.
     */
    public function addQuestion(Request $request, Survey $survey)
    {
        $validator = Validator::make($request->all(), [
            'question_text' => 'required|string|max:500',
            'type' => 'required|in:text,textarea,rating_1_5,rating_1_10,multiple_choice,single_choice,yes_no',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order' => 'integer',
            'help_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $question = Question::create([
            'survey_id' => $survey->id,
            'question_text' => $request->question_text,
            'type' => $request->type,
            'options' => $request->options,
            'is_required' => $request->is_required ?? false,
            'order' => $request->order ?? 0,
            'help_text' => $request->help_text,
        ]);

        return response()->json([
            'message' => 'Question added successfully',
            'question' => $question
        ], 201);
    }

    /**
     * Update a question.
     */
    public function updateQuestion(Request $request, Survey $survey, Question $question)
    {
        // Check if question belongs to this survey
        if ($question->survey_id !== $survey->id) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'question_text' => 'sometimes|required|string|max:500',
            'type' => 'sometimes|required|in:text,textarea,rating_1_5,rating_1_10,multiple_choice,single_choice,yes_no',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order' => 'integer',
            'help_text' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $question->update($request->only([
            'question_text', 'type', 'options', 'is_required', 'order', 'help_text'
        ]));

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $question
        ]);
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion(Request $request, Survey $survey, Question $question)
    {
        // Check if question belongs to this survey
        if ($question->survey_id !== $survey->id) {
            return response()->json(['message' => 'Question not found'], 404);
        }

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully'
        ]);
    }

    /**
     * Activate a survey.
     */
    public function activate(Survey $survey)
    {
        $survey->update(['status' => 'active']);

        return response()->json([
            'message' => 'Survey activated successfully',
            'survey' => $survey
        ]);
    }

    /**
     * Pause a survey.
     */
    public function pause(Survey $survey)
    {
        $survey->update(['status' => 'paused']);

        return response()->json([
            'message' => 'Survey paused successfully',
            'survey' => $survey
        ]);
    }

    /**
     * Close a survey.
     */
    public function close(Survey $survey)
    {
        $survey->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Survey closed successfully',
            'survey' => $survey
        ]);
    }

    /**
     * Get survey statistics.
     */
    public function statistics(Survey $survey)
    {
        $statistics = $survey->getStatistics();

        return response()->json([
            'statistics' => $statistics
        ]);
    }

    /**
     * Show public survey (for anonymous access).
     */
    public function publicShow($accessCode)
    {
        $survey = Survey::where('access_code', $accessCode)
            ->where('status', 'active')
            ->with(['questions' => function ($query) {
                $query->orderBy('order');
            }])
            ->first();

        if (!$survey) {
            return response()->json(['message' => 'Survey not found or not active'], 404);
        }

        // Check if survey is active
        if (!$survey->isActive()) {
            return response()->json(['message' => 'Survey is not currently active'], 400);
        }

        return response()->json([
            'survey' => $survey
        ]);
    }
}
