<?php

namespace App\Clients;

use Illuminate\Support\Facades\Storage;
use App\Http\Resources\TVShowResource;
use App\Models\TVShow;
use App\Cache\FileCache;

class TVMazeClient
{
    const API_URL = 'https://api.tvmaze.com';

    public function searchShowsByName(?string $name = null): array
    {
        $results = FileCache::get($name);

        if (!$results) 
        {
            $data = $this->get(self::API_URL. '/search/shows?q=' . rawurlencode($name));
            FileCache::save($name, $data);

            $results['data'] = $data;
        }

        return $this->filter($results['data'], $name);
    }

    private function filter(&$results, &$name): array 
    {
        $shows = [];

        if (is_array($results)) 
        {   
            foreach ($results as $series) 
            {
                if (strpos ($series['show']['name'], strtolower ($name))) {
                    $shows[] = new TVShowResource(new TVShow($series['show']));
                    continue;
                }

                if (strpos ($series['show']['name'], ucfirst (strtolower ($name)))) {
                    $shows[] = new TVShowResource(new TVShow($series['show']));
                }
            }
        }

        return $shows;
    }

    private function get($url)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$result = curl_exec($ch);

		curl_close($ch);

		$response = json_decode($result, TRUE);

		if (is_array($response) && count($response) > 0 && (!isset($response['status']) || $response['status'] != '404')) 
        {
			return $response;
		}
		
		return false;
	}
}
