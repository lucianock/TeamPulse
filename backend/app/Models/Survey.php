<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Survey extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'access_code',
        'status',
        'start_date',
        'end_date',
        'is_anonymous',
        'allow_multiple_responses',
        'max_responses',
        'creator_name',
        'creator_email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_anonymous' => 'boolean',
        'allow_multiple_responses' => 'boolean',
    ];

    /**
     * Get the questions for the survey.
     */
    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get the responses for the survey.
     */
    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Get the responses grouped by question.
     */
    public function responsesByQuestion()
    {
        return $this->responses()->with('question')->get()->groupBy('question_id');
    }

    /**
     * Check if the survey is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if ($this->max_responses && $this->responses()->count() >= $this->max_responses) {
            return false;
        }

        return true;
    }

    /**
     * Check if the survey is expired.
     */
    public function isExpired(): bool
    {
        return $this->end_date && now()->gt($this->end_date);
    }

    /**
     * Check if the survey has reached maximum responses.
     */
    public function hasReachedMaxResponses(): bool
    {
        return $this->max_responses && $this->responses()->count() >= $this->max_responses;
    }

    /**
     * Get the response count for the survey.
     */
    public function getResponseCount(): int
    {
        return $this->responses()->count();
    }

    /**
     * Generate a unique access code for the survey.
     */
    public static function generateAccessCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('access_code', $code)->exists());

        return $code;
    }

    /**
     * Get survey statistics.
     */
    public function getStatistics(): array
    {
        $totalResponses = $this->responses()->count();
        $uniqueSessions = $this->responses()->distinct('session_id')->count();
        
        $questionStats = [];
        foreach ($this->questions as $question) {
            $responses = $this->responses()->where('question_id', $question->id)->get();
            
            $stats = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'total_responses' => $responses->count(),
            ];

            if (in_array($question->type, ['rating_1_5', 'rating_1_10'])) {
                $ratings = $responses->pluck('rating_value')->filter();
                $stats['average_rating'] = $ratings->count() > 0 ? round($ratings->avg(), 2) : 0;
                $stats['rating_distribution'] = $ratings->countBy()->toArray();
            }

            if (in_array($question->type, ['multiple_choice', 'single_choice'])) {
                $selectedOptions = $responses->pluck('selected_options')->filter();
                $stats['option_distribution'] = [];
                foreach ($selectedOptions as $options) {
                    if (is_array($options)) {
                        foreach ($options as $option) {
                            $stats['option_distribution'][$option] = ($stats['option_distribution'][$option] ?? 0) + 1;
                        }
                    }
                }
            }

            $questionStats[] = $stats;
        }

        return [
            'total_responses' => $totalResponses,
            'unique_sessions' => $uniqueSessions,
            'questions' => $questionStats,
        ];
    }
}
