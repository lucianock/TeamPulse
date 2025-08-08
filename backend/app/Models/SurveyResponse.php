<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'question_id',
        'response_text',
        'rating_value',
        'selected_options',
        'session_id',
        'ip_address',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'selected_options' => 'array',
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the survey that the response belongs to.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the question that the response belongs to.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Check if the response is anonymous.
     */
    public function isAnonymous(): bool
    {
        return true; // All responses are now anonymous
    }

    /**
     * Get the response value based on question type.
     */
    public function getResponseValue()
    {
        return match($this->question->type) {
            'text', 'textarea' => $this->response_text,
            'rating_1_5', 'rating_1_10' => $this->rating_value,
            'multiple_choice', 'single_choice', 'yes_no' => $this->selected_options,
            default => null,
        };
    }

    /**
     * Get the response value as a formatted string.
     */
    public function getFormattedResponse(): string
    {
        if ($this->question->isText()) {
            return $this->response_text ?? '';
        }

        if ($this->question->isRating()) {
            return $this->rating_value ? "{$this->rating_value}/{$this->question->getMaxRating()}" : '';
        }

        if ($this->question->isChoice()) {
            if (is_array($this->selected_options)) {
                return implode(', ', $this->selected_options);
            }
            return '';
        }

        return '';
    }

    /**
     * Scope to filter responses by session.
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to filter responses by survey.
     */
    public function scopeBySurvey($query, $surveyId)
    {
        return $query->where('survey_id', $surveyId);
    }

    /**
     * Scope to filter responses by question.
     */
    public function scopeByQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Scope to filter responses by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('submitted_at', [$startDate, $endDate]);
    }
}
