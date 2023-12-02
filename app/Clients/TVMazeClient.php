<?php

namespace App\Clients;

use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TVShowResource;
use App\Models\TVShow;

class TVMazeClient
{
    const API_URL = 'https://api.tvmaze.com';

    public function searchShowsByName (string $name): array
    {
        $shows = [];

        $fileName = 'request-' . md5($name) .'.json';
        $cacheFile = Storage::get($fileName);

        $results = json_decode($cacheFile, TRUE);

        if (!$results) 
        {
            $results = $this->get(self::API_URL. '/search/shows?q=' . rawurlencode($name));
            Storage::put($fileName, json_encode($results));    
        }
        

        if (is_array($results)) 
        {   
            foreach ($results as $series) 
            {
                if (strpos ($series['show']['name'], strtolower ($name))) {
                    $shows[] = new TVShowResource(new TVShow($series['show']));
                }

                if (strpos ($series['show']['name'], ucfirst (strtolower ($name)))) {
                    $shows[] = new TVShowResource(new TVShow($series['show']));
                }
            }
        }

        return $shows;
    }

    private function get ($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$result = curl_exec($ch);

		curl_close($ch);

		$response = json_decode($result, TRUE);

		if (is_array($response) && count($response) > 0 && (!isset($response['status']) || $response['status'] != '404')) {
			return $response;
		}
		
		return false;
	}
}
