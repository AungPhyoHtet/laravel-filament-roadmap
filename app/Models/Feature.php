<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FeatureStatus;
use App\Enums\FeatureType;
use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'status',
    'type',
    'description',
    'effort_in_days',
    'priority',
    'cost',
    'target_delivery_date',
    'delivered_at',
])]
class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    /**
     * Get the comments left on this feature.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the votes cast for this feature.
     *
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get the milestones for this feature.
     *
     * @return HasMany<Milestone, $this>
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => FeatureStatus::class,
            'type' => FeatureType::class,
            'effort_in_days' => 'integer',
            'priority' => 'integer',
            'cost' => 'decimal:2',
            'target_delivery_date' => 'date',
            'delivered_at' => 'datetime',
        ];
    }
}
