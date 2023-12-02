<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Clients\TVMazeClient;
use App\Http\Resources\TVShowCollection;

class ShowServer extends Controller
{
    private $tvMazeClient;

    public function __construct (TVMazeClient $TVMazeClient) 
    {
        $this->tvMazeClient = $TVMazeClient;
    }

    public function __invoke(Request $request)
    {
        if (!$request->q) {
            return response()->json([
                'success' => false
            ]);
        }

        $collection = $this->tvMazeClient->searchShowsByName($request->q);

        return response()->json([
            'success' => true, 
            'shows' => new TVShowCollection($collection)
        ]);
    }
}