<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Transformers\EventTransformer;
use App\Models\User;

class EventController extends BaseController
{
    private $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function index()
    {
        $events = $this->event->paginate();

        // Foreach event
        $events->map(function ($event) {
            $user =  User::find($event->user_id);

            //Add user
            if ($user) {
              $event['user'] = $user;
            }
            return $event;
        });

        return $this->response->paginator($events, new EventTransformer());
    }

    public function show($id)
    {
        $event = $this->event->where('id', $id)->first();
        return $this->response->item($event, new EventTransformer());
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'destiny_id' => 'required|exists:destiny,id',
            'title' => 'required|string',
            'desc' => 'required|string',
            'rating' => 'required|in:0,0.5,1,1.5,2,2.5,3,3.5,4,4.5,5',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $attributes = $request->only('title', 'desc', 'rating');
        $attributes['destiny_id'] = Destiny::find($request->get('destiny_id'))->id;

        $event = $this->event->create($attributes);

        // Return 201 plus data
        return $this->response
            ->item($event, new EventTransformer())
            ->setStatusCode(201);
    }
}
