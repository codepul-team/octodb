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
            supSearch(
                q: "$request->mpn",
                limit: 5
            ) {
                hits
                results {
                part {
                    id
                    name
                    mpn
                    medianPrice1000 {
                    quantity
                    currency
                    }
                    category {
                    id
                    name
                    }
                    manufacturer {
                    name
                    homepageUrl
                    }
                }
                }
            }
            }
            GRAPHQL;
            $response = $this->access_token_service->postHttp($query,'graphql');

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
}
