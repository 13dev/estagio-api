<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Transformers\DestinyTransformer;
use App\Transformers\EventTransformer;
use App\Models\User;
use App\Models\Destiny;

class DestinyController extends BaseController
{
    private $destiny;

    public function __construct(Destiny $destiny)
    {
        $this->destiny = $destiny;
    }

    public function index()
    {
        $destinies = $this->destiny->paginate();

        return $this->response->paginator($destinies, new DestinyTransformer());
    }

    public function events($id)
    {
    	try {
    		$destiny = Destiny::findOrFail($id);
    		$events = $destiny->events;
    	} catch(Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    		$this->response->error('404 Not Found.', 404);
    	}

        return $this->response->collection($events, new EventTransformer());
    }
}
