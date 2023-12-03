<?php
 
namespace App\Http\Controllers;

use App\Clients\TVMazeClient;
use App\Http\Requests\SearchShowRequest;
use App\Http\Resources\TVShowCollection;

class ShowServer extends Controller
{
    private $tvMazeClient;

    public function __construct (TVMazeClient $TVMazeClient) 
    {
        $this->tvMazeClient = $TVMazeClient;
    }

    public function __invoke(SearchShowRequest $request)
    {
        $collection = $this->tvMazeClient->searchShowsByName($request->q);

        return response()->json([
            'success' => true, 
            'shows' => new TVShowCollection($collection)
        ]);
    }
}