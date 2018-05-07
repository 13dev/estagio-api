<?php

namespace App\Transformers;

use App\Models\Destiny;
use League\Fractal\TransformerAbstract;

class DestinyTransformer extends TransformerAbstract
{
    protected $authorization;

    protected $availableIncludes = ['events'];

    public function transform(Destiny $destiny)
    {
        return [
            'name'                  => $destiny->name,
            'country'               => $destiny->country,
            'lat'                   => $destiny->lat,
            'long'                  => (bool)$destiny->long,
            'createdAt'             => (string) $destiny->created_at,
            'updatedAt'             => (string) $destiny->updated_at
        ];
    }

    public function includeEvents(Destiny $destiny)
    {
        if (! $destiny->events) {
            return $this->null();
        }

        return $this->item($destiny->events, new EventsTransformer());
    }

}
