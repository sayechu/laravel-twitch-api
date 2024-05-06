<?php

namespace App\Services;

class TopsOfTheTopsManager
{
    private ApiClient $apiClient;
    private DBClient $dbClient;

    public function __construct(ApiClient $apiClient, DBClient $dbClient)
    {
        $this->apiClient = $apiClient;
        $this->dbClient = $dbClient;
    }

    public function getTopsOfTheTops(int $since): array
    {
        $results = [];
        $responseGetToken = $this->apiClient->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $api_headers = array('Authorization: Bearer ' . $twitchToken);

        $topThreeGames = $this->apiClient->getTopThreeGames($api_headers);

        if (!$this->dbClient->isLoadedDB()) {
            $this->dbClient->addTopThreeGamesToDB($topThreeGames);
            foreach ($topThreeGames as $topGame) {
                $topFourtyVideos = $this->apiClient->getTopFourtyVideos($topGame['id']);
                $this->dbClient->addVideosToDB($topFourtyVideos, $topGame['id']);
                $results = $this->getTopsOfTheTopsAttributes($topGame, $results);
            }
            return $results;
        }
        if ($this->shouldReviewEachTopGame($since)) {
            $this->reviewEachTopGame($topThreeGames, $since);
        }

        return $this->fetchTopGamesData($topThreeGames);
    }

    private function shouldReviewEachTopGame($since): bool
    {
        $lastUpdateTime = strtotime($this->dbClient->getOldestUpdateDatetime()['fecha']);
        $currentTime = time();
        $maxTimeDifference = $currentTime - $lastUpdateTime;

        return $maxTimeDifference > $since;
    }

    private function reviewEachTopGame(array $topThreeGames, int $since): void
    {
        $gamesArray = [];

        $topThreeGamesInDB = $this->dbClient->getTopThreeGames();

        $gamesArray[] = $topThreeGamesInDB[0]['gameName'];
        $gamesArray[] = $topThreeGamesInDB[1]['gameName'];
        $gamesArray[] = $topThreeGamesInDB[2]['gameName'];

        foreach ($topThreeGames as $index => $twitchGame) {
            $date = $this->searchGameDate($topThreeGamesInDB, $twitchGame['id']);
            if ((in_array($twitchGame['name'], $gamesArray)) && (time() - strtotime($date) > $since)) {
                $this->dbClient->deleteVideosOfAGivenGame($twitchGame['id']);
                $this->dbClient->addVideosToDB(
                    $this->apiClient->getTopFourtyVideos($twitchGame['id']),
                    $twitchGame['id']
                );
                $this->dbClient->updateDatetime($twitchGame['id']);
            } elseif (!(in_array($twitchGame['name'], $gamesArray)) || !(time() - strtotime($date) > $since)) {
                $gameId = $this->dbClient->getGameIdAtPosition($index + 1);
                $this->dbClient->deleteVideosOfAGivenGame($gameId);
                $this->dbClient->updateTopGame($index + 1, $twitchGame['id'], $twitchGame['name']);
                $this->dbClient->addVideosToDB(
                    $this->apiClient->getTopFourtyVideos($twitchGame['id']),
                    $twitchGame['id']
                );
            }
        }
    }

    private function fetchTopGamesData(array $topThreeGames): array
    {
        $results = [];

        foreach ($topThreeGames as $topGame) {
            $results = $this->getTopsOfTheTopsAttributes($topGame, $results);
        }

        return $results;
    }

    private function getTopsOfTheTopsAttributes(mixed $topGame, array $results): array
    {
        $topsOfTheTopsAttr = $this->dbClient->getTopsOfTheTopsAttributes($topGame['id']);
        $resultTopsOfTheTops = [
            'game_id' => strval($topGame['id']),
            'game_name' => $topGame['name'],
            'user_name' => $topsOfTheTopsAttr['user_name'],
            'total_videos' => strval($topsOfTheTopsAttr['total_videos']),
            'total_views' => strval($topsOfTheTopsAttr['total_views']),
            'most_viewed_title' => $topsOfTheTopsAttr['most_viewed_title'],
            'most_viewed_views' => strval($topsOfTheTopsAttr['most_viewed_views']),
            'most_viewed_duration' => $topsOfTheTopsAttr['most_viewed_duration'],
            'most_viewed_created_at' => $topsOfTheTopsAttr['most_viewed_created_at']
        ];

        $results[] = $resultTopsOfTheTops;
        return $results;
    }

    private function searchGameDate(array $topThreeGamesInDB, string $gameId): string
    {
        foreach ($topThreeGamesInDB as $game) {
            if ($game['gameId'] == $gameId) {
                return $game['fecha'];
            }
        }
        return '';
    }
}