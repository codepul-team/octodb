<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Services\AccessTokenService;
use App\Traits\JsonResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                '*.query' => 'required|string',
                '*.qty' => 'required|numeric',
                '*.is_match' => 'required|boolean',
                '*.part' => 'nullable|string',
                '*.part_description' => 'nullable|string',
                '*.description' => 'nullable|string',
                '*.schematic_reference' => 'nullable|string',
                '*.internal_part_no' => 'nullable|string',
                '*.lifecycle' => 'nullable|string',
                '*.lead_time' => 'nullable|string',
                '*.rohs' => 'nullable|string',
                '*.digi_key' => 'nullable|string',
                '*.mouser' => 'nullable|string',
                '*.newark' => 'nullable|string',
                '*.online_component' => 'nullable|string',
                '*.rs_component' => 'nullable|string',
                '*.distributor' => 'nullable|string',
                '*.unit_price' => 'nullable|numeric',
                '*.line_total' => 'nullable|numeric',
                '*.bacth_total' => 'nullable|numeric',
                '*.note' => 'nullable|string',
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
            DB::beginTransaction();

            foreach ($validation as $data) {
                Bom::create($data);
            }
            DB::commit();
        } catch (Exception $e) {

            DB::rollback();
            return $this->error('Data not save!');
        }
        return  $this->success(
            'Data Saved!',
            [],
            true
        );
    }
}
