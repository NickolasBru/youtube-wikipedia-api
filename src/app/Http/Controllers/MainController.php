<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Interfaces\CountryDataAggregatorInterface;
use App\Http\Requests\CountryDataRequest;
use Illuminate\Http\JsonResponse;

class MainController extends Controller
{
    private CountryDataAggregatorInterface $aggregator;

    public function __construct(CountryDataAggregatorInterface $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function index(CountryDataRequest $request): JsonResponse
    {
        //Retrieve validated data safely
        $data = $request->safe();

        //Extract the fields
        $country      = $data->country ?? null;
        $page         = $data->page ?? 1;
        $limit        = $data->offset ?? 5;         // or keep calling it `offset`
        $forceRefresh = $data->force_refresh ?? false;

        //Build the list of countries
        $defaultCountries = ['gb','nl','de','fr','es','it','gr'];
        $countriesToFetch = $country ? [strtolower($country)] : $defaultCountries;
Log::info($countriesToFetch);
        //Use your aggregator service
        $results = $this->aggregator->getCountryData(
            $countriesToFetch,
            $page,
            $limit,
            $forceRefresh
        );

        //Return JSON
        return response()->json($results);
    }
}
