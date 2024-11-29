<?php

namespace App\Http\Controllers;

use App\Services\AccessTokenService;
use App\Traits\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BomController extends Controller
{
    use JsonResponse;
    protected $access_token_service;
    public function __construct(
        AccessTokenService $access_token_service
    ) {
        $this->access_token_service = $access_token_service;
    }

    public function index()
    {
        return view('bom.index');
    }

    public function searchMpn(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'mpn' => 'required|min:3'
            ],
            $this->validationMessage()
        );

        if ($validation->fails()) {
            $validation_error = "";
            foreach ($validation->errors()->all() as $message) {
                $validation_error .= $message;
            }
            return $this->validationResponse(
                $validation_error
            );
        }

        try {
            $query = <<<GRAPHQL
            query partSearch {
            supSearch(q: "$request->mpn", limit: 5) {
                hits
                results {
                part {
                    id
                    mpn
                    name
                    shortDescription
                    estimatedFactoryLeadDays
                    sellers {
                    company {
                        id
                        name
                        homepageUrl
                        isOctocartSupported
                        isVerified
                        isDistributorApi
                    }
                    offers {
                        id
                        inventoryLevel
                        moq
                        multipackQuantity
                        onOrderQuantity
                        orderMultiple
                        packaging
                        sku
                        updated
                        prices {
                        conversionRate
                        convertedCurrency
                        convertedPrice
                        currency
                        price
                        quantity
                        }
                        factoryPackQuantity
                        factoryLeadDays
                    }
                    isAuthorized
                    isBroker
                    isRfq
                    }
                    specs {
                    displayValue
                    siValue
                    units
                    unitsName
                    unitsSymbol
                    value
                    valueType
                    attribute {
                        group
                        id
                        name
                        shortname
                        unitsName
                        unitsSymbol
                        valueType
                    }
                    }
                    totalAvail
                }
                }
            }
            }
            GRAPHQL;
            $response = $this->access_token_service->postHttp($query, 'graphql');
            if ($response) {
                return  $this->success(
                    'Success',
                    $response,
                    false
                );
            }
        } catch (Exception $e) {
            // dd($e->getMessage());
            return $this->error('Failed to fetch data from Search API');
        }
    }

    public function getData(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'mpn' => 'required'
            ],
            $this->validationMessage()
        );

        if ($validation->fails()) {
            $validation_error = "";
            foreach ($validation->errors()->all() as $message) {
                $validation_error .= $message;
            }
            return $this->validationResponse(
                $validation_error
            );
        }
        $variables = [
            'mpn' => 'LM358N', // Replace with your desired part number
        ];
        // try {

        $query = <<<GRAPHQL
            query partDetail {
            supSearchMpn(q: "913932398", limit: 5) {
                hits
                results {
                part {
                    _cacheId
                    akaMpns
                    avgAvail
                    cadRequestUrl
                    counts
                    estimatedFactoryLeadDays
                    freeSampleUrl
                    genericMpn
                    id
                    manufacturerUrl
                    mpn
                    name
                    octopartUrl
                    shortDescription
                    slug
                    totalAvail
                    v3uid
                    extras {
                    confidence
                    datasheetUrls
                    description
                    isRohsCompliant
                    lifeCycle
                    partId
                    alternatives {
                        confidence
                        datasheetUrls
                        description
                        isRohsCompliant
                        lifeCycle
                        partId
                        alternatives {
                        parameters {
                            name
                            unit
                            value
                        }
                        }
                    }
                    }
                    manufacturer {
                    aliases
                    displayFlag
                    homepageUrl
                    id
                    isDistributorApi
                    isOctocartSupported
                    isVerified
                    name
                    slug
                    }
                    sellers {
                    _cacheId
                    country
                    isAuthorized
                    isBroker
                    isRfq
                    }
                }
                }
            }
            }
            GRAPHQL;
        $data = [];
        $part_search = $this->access_token_service->postHttp($query, 'graphql');
        dd($part_search);
        $parts = $part_search['data']['supSearch']['results'];
        foreach ($parts as $item) {
            $data[] = [
                "part_id" => $item['part']['id'] ?? '',
                "part_name" => $item['part']['name'] ?? '',
                "part_npm" => $item['part']['npm'] ?? '',
                "category_id" => $item['part']['category']['id'] ?? '',
                "category_name" => $item['part']['category']['name'] ?? '',
                "manufacturer_name" => $item['part']['manufacturer']['name'] ?? '',
                "manufacturer_url" => $item['part']['manufacturer']['homepageUrl'] ?? '',
                "medianPrice1000_qty" => $item['part']['medianPrice1000']['quantity'] ?? 0,
                "medianPrice1000_currency" => $item['part']['medianPrice1000']['currency'] ?? '',
            ];
        }


        if ($data) {
            return  $this->success(
                'Success',
                $data,
                false
            );
        }
        // } catch (Exception $e) {
        //    // dd($e->getMessage());
        //     return $this->error('Failed to fetch data from Search API');
        // }
    }
}
