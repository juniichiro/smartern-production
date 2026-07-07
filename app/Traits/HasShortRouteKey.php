<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

trait HasShortRouteKey
{
    /**
     * Get the value of the model's route key
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Retrieve the model for a bound value
     *
     * @param mixed $value
     * @param string|null $field
     * @return Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $id = Hashids::decode($value)[0] ?? null;

        if (! $id) {
            abort(404);
        }

        return $this->where($field ?? $this->getKeyName(), $id)->firstOrFail();
    }
}
