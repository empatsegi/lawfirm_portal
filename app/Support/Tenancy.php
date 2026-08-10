<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Holds the firm the current request, job, or command is acting on.
 *
 * For web requests this falls back to the authenticated user's firm, so
 * nothing needs to set it. Outside HTTP (queues, console, seeders) there is no
 * authenticated user, so scoped queries return no rows until you wrap the work
 * in actAs() or bypass().
 */
class Tenancy
{
    protected ?int $firmId = null;

    protected bool $bypassed = false;

    public function set(?int $firmId): void
    {
        $this->firmId = $firmId;
    }

    /**
     * Resolved lazily rather than pushed in by middleware, so that it is
     * correct no matter how early a scoped query runs — route model binding
     * included, which happens before any appended web middleware.
     */
    public function id(): ?int
    {
        return $this->firmId ?? Auth::user()?->firm_id;
    }

    public function hasId(): bool
    {
        return $this->id() !== null;
    }

    public function isBypassed(): bool
    {
        return $this->bypassed;
    }

    /**
     * Run a callback scoped to a specific firm, restoring the previous one after.
     */
    public function actAs(?int $firmId, callable $callback): mixed
    {
        $previous = $this->firmId;
        $this->firmId = $firmId;

        try {
            return $callback();
        } finally {
            $this->firmId = $previous;
        }
    }

    /**
     * Run a callback with firm scoping disabled — cross-firm admin reporting,
     * seeders, migrations. Keep the callback as small as possible.
     */
    public function bypass(callable $callback): mixed
    {
        $previous = $this->bypassed;
        $this->bypassed = true;

        try {
            return $callback();
        } finally {
            $this->bypassed = $previous;
        }
    }
}
