<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use VK\Client\VKApiClient;
use App\ObsceneWord;
use App\InsultWord;

class HandleController extends Controller
{
    public function handle(Request $request)
    {
        $vk = new VKApiClient();
        $accessToken = '3f1a5ea13f1a5ea13f1a5ea1bc3f76e0ba33f1a3f1a5ea16269045809c1fe94690af51c';

// *** GET POSTS ***

        $posts = $vk->wall()->get($accessToken, array(
            'owner_id' => -186269527
        ));

// *** POSTS IDS ***

        $result = [];

        foreach ($posts['items'] as $post) {
            $comments = $vk->wall()->getComments($accessToken, array(
                'owner_id' => -186269527,
                'post_id' => $post['id'],
            ));

            if (isset($posts['items'])) {
                foreach ($comments['items'] as $comment) {
                    $originalText = $comment['text'];
                    $obsceneRank = 0;
                    $insultRank = 0;

                    // Cleaning
                    $text = str_replace([',', '!'], '', $comment['text']);

                    // Mystem
                    $params = http_build_query(['text' => $text]);
                    $response = file_get_contents('http://mystem:5000/mystem?'.$params);
                    $response = json_decode($response, true);
                    $text = str_replace("\n", '', $response['text']);

                    // Looping
                    foreach (explode(' ', $text) as $word) {
                        if (!$word) continue;

                        // Speller
                        $params = http_build_query(['text' => $word]);
                        $response = file_get_contents('https://speller.yandex.net/services/spellservice.json/checkText?'.$params);
                        $response = json_decode($response, true);
                        if (count($response)) {
                            $word = $response[0]['s'][0];
                        }

                        if (ObsceneWord::where('text', $word)->exists()) {
                            $obsceneRank += 1;
                        }

                        if (InsultWord::where('text', $word)->exists()) {
                            $insultRank += 1;
                        }
                    }

                    if (!isset($result[$comment['from_id']])) {
                        $arr = [
                            'text' => $originalText,
                            'obsceneRank' => $obsceneRank,
                            'insultRank' => $insultRank,
                            'totalRank' => $obsceneRank + $insultRank,
                        ];

                        $user = $vk->users()->get($accessToken, array(
                            'user_ids' => [$comment['from_id']]
                        ));

                        if (count($user)) {
                            $arr['user_name'] = $user[0]['first_name'].' '.$user[0]['last_name'];
                            $arr['user_link'] = 'https://vk.com/id'.$user[0]['id'];
                        }

                        $result[$comment['from_id']] = $arr;
                    }
                    else {
                        $result[$comment['from_id']]['obsceneRank'] += $obsceneRank;
                        $result[$comment['from_id']]['insultRank'] += $insultRank;
                        $result[$comment['from_id']]['totalRank'] += $obsceneRank + $insultRank;
                    }
                }
            }
        }

        $html = '';

        foreach ($result as $item) {
            $html .= view('item')
                ->with('item', $item)
                ->render();
        }

        return $html;
    }
}
