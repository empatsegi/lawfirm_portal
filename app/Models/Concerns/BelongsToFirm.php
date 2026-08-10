<?php

namespace App\Models\Concerns;

use App\Models\Firm;
use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applies firm isolation to a model. Every tenant-owned table should use this.
 *
 * @property int $firm_id
 *
 * @mixin Model
 */
trait BelongsToFirm
{
    protected static function bootBelongsToFirm(): void
    {
        if (static::appliesFirmGlobalScope()) {
            static::addGlobalScope('firm', function (Builder $builder): void {
                $tenancy = app(Tenancy::class);

                if ($tenancy->isBypassed()) {
                    return;
                }

                // Fail closed. An unresolved firm must match nothing — the one
                // thing it must never do is fall through and return every
                // firm's rows.
                $builder->where(
                    $builder->getModel()->qualifyColumn('firm_id'),
                    $tenancy->id() ?? 0,
                );
            });
        }

        static::creating(function (Model $model): void {
            if ($model->getAttribute('firm_id') === null) {
                $model->setAttribute('firm_id', app(Tenancy::class)->id());
            }
        });
    }

    /**
     * Whether firm isolation is enforced automatically on every query.
     *
     * Only override this where the firm cannot be known at query time — the
     * authenticatable model is the one genuine case, because the firm is
     * resolved from the user that is being looked up.
     */
    protected static function appliesFirmGlobalScope(): bool
    {
        return true;
    }

    /**
     * Constrain a query to one firm explicitly. Use on models that opt out of
     * the global scope.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeOfFirm(Builder $query, int|Firm $firm): Builder
    {
        return $query->where(
            $this->qualifyColumn('firm_id'),
            $firm instanceof Firm ? $firm->id : $firm,
        );
    }

    /**
     * @return BelongsTo<Firm, $this>
     */
    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }
}
