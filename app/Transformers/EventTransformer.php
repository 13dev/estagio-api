<?php

namespace App\Transformers;

use App\Models\Event;
use League\Fractal\ParamBag;
use League\Fractal\TransformerAbstract;

class EventTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['user', 'destiny'];

    public function transform(Event $event)
    {
        return $event->attributesToArray();
    }

    public function includeUser(Event $event)
    {
        if (! $event->user) {
            return $this->null();
        }

        return $this->item($event->user, new UserTransformer());
    }

    public function includeDestiny(Event $event)
    {
        if (! $event->destiny) {
            return $this->null();
        }

        return $this->item($event->destiny, new DestinyTransformer());
    }


}
