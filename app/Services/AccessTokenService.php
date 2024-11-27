<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AccessTokenService
{
      public function accessToken()
      {
            $response = Http::asForm()
                  ->withHeaders([
                        'User-Agent' => '<your Nexar Client>',
                  ])
                  ->post('https://identity.nexar.com/connect/token', [
                        'grant_type' => 'client_credentials',
                        'client_id' => env('CLIENT_ID'),
                        'client_secret' => env('CLIENT_SECRET'),
                        'scope' =>  env('SCOPE'),
                  ]);

            if ($response->successful()) {
                  return $response['access_token']; // Return only the access_token
            }
      }

      // HTTP POST
      public function postHttp($query, $endpoint)
      {
            // Step 1: Generate Token
            $token = $this->accessToken();
            if (is_array($token)) {
                  // Handle token generation error
                  return $token;
            }


            // Step 3: Send the GraphQL request
            $response = Http::withHeaders([
                  'Authorization' => "Bearer $token", // Token in the header
                  'Content-Type' => 'application/json',
            ])
                  ->post(env('API_URL').$endpoint, [
                        'query' => $query,
                  ]);

            if ($response) {
                  return $response->json();
            }

            return false;
      }
}
