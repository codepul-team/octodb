<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GenerateToken extends Controller
{

    public function generateToken()
    {
        $response = Http::asForm()
            ->withHeaders([
                'User-Agent' => '<your Nexar Client>',
            ])
            ->post('https://identity.nexar.com/connect/token', [
                'grant_type' => 'client_credentials',
                'client_id' => '9f658c81-09d3-4400-816f-16d46388c151',
                'client_secret' => 'ZryWmH8W_qBisR7mApg3rXK2_A_t3JgVMiaZ',
                'scope' => 'supply.domain',
            ]);

        if ($response->successful()) {
            return $response->json()['access_token']; // Return only the access_token
        }

        return response()->json([
            'error' => 'Failed to generate token',
            'status' => $response->status(),
            'message' => $response->body(),
        ], $response->status());
    }

    public function sendGraphQLRequest()
    {
        // Step 1: Generate Token
        $token = $this->generateToken();

        if (is_array($token)) {
            // Handle token generation error
            return $token;
        }

        // Step 2: GraphQL Query
        $query = <<<GRAPHQL
    query partSearch {
      supSearch(
        q: "3 ohm resistor",
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

        // Step 3: Send the GraphQL request
        $response = Http::withHeaders([
            'Authorization' => "Bearer $token", // Token in the header
            'Content-Type' => 'application/json',
        ])
            ->post('https://api.nexar.com/graphql', [
                'query' => $query,
            ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json([
            'error' => 'Failed to fetch data from GraphQL API',
            'status' => $response->status(),
            'message' => $response->body(),
        ], $response->status());
    }

}
