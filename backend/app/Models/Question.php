<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'question_text',
        'type',
        'options',
        'is_required',
        'order',
        'help_text',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Get the survey that the question belongs to.
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the responses for the question.
     */
    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Check if the question is a rating question.
     */
    public function isRating(): bool
    {
        return in_array($this->type, ['rating_1_5', 'rating_1_10']);
    }

    /**
     * Check if the question is a choice question.
     */
    public function isChoice(): bool
    {
        return in_array($this->type, ['multiple_choice', 'single_choice']);
    }

    /**
     * Check if the question is a text question.
     */
    public function isText(): bool
    {
        return in_array($this->type, ['text', 'textarea']);
    }

    /**
     * Get the maximum rating value for rating questions.
     */
    public function getMaxRating(): int
    {
        return match($this->type) {
            'rating_1_5' => 5,
            'rating_1_10' => 10,
            default => 0,
        };
    }

    /**
     * Get the question options as an array.
     */
    public function getOptionsArray(): array
    {
        return $this->options ?? [];
    }

    /**
     * Get response statistics for the question.
     */
    public function getResponseStats(): array
    {
        $responses = $this->responses;
        $totalResponses = $responses->count();

        $stats = [
            'question_id' => $this->id,
            'question_text' => $this->question_text,
            'type' => $this->type,
            'total_responses' => $totalResponses,
        ];

        if ($this->isRating()) {
            $ratings = $responses->pluck('rating_value')->filter();
            $stats['average_rating'] = $ratings->count() > 0 ? round($ratings->avg(), 2) : 0;
            $stats['rating_distribution'] = $ratings->countBy()->toArray();
            $stats['max_rating'] = $this->getMaxRating();
        }

        if ($this->isChoice()) {
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

        if ($this->isText()) {
            $textResponses = $responses->pluck('response_text')->filter();
            $stats['text_responses_count'] = $textResponses->count();
            $stats['average_text_length'] = $textResponses->count() > 0 ? round($textResponses->map(fn($text) => strlen($text))->avg(), 2) : 0;
        }

        return $stats;
    }
}
