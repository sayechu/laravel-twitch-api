<?php

namespace App\Services;

class ApiClient
{
    private const CLIENT_ID = '970almy6xw98ruyojcwqpop0p0o5a2';
    private const CLIENT_SECRET = 'yl0nqzjjnadd8wl7zilpr9pzuh979j';
    private const GRANT_TYPE = 'client_credentials';

    public function getToken(): array
    {
        $url = 'https://id.twitch.tv/oauth2/token';
        $data = [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'grant_type' => self::GRANT_TYPE
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
        ]);

        $tokenResponse = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [
            'response' => $tokenResponse,
            'http_code' => $http_code
        ];
    }

    public function makeCurlCall(string $apiUrl, array $apiHeaders): array
    {
        $apiHeaders[] = 'Client-Id: ' . self::CLIENT_ID;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $apiHeaders
        ]);

        $apiResponse = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return [
            'response' => $apiResponse,
            'http_code' => $httpCode
        ];
    }

    public function getTopThreeGames(array $api_headers): array
    {
        $api_url = "https://api.twitch.tv/helix/games/top?first=3";

        $topGamesData = $this->makeCurlCall($api_url, $api_headers);

        return json_decode($topGamesData['response'], true)['data'];
    }

    public function getTopFourtyVideos($gameId, $api_headers): array
    {
        $api_url = "https://api.twitch.tv/helix/videos?game_id={$gameId}&sort=views&first=40";

        $topVideosData = $this->makeCurlCall($api_url, $api_headers);

        return json_decode($topVideosData['response'], true)['data'];
    }
}
