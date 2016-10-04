<?php
require_once('Tweet/TwitterAPIExchange.php');

class CavTools_Model_IMOBot extends XenForo_Model {



    public function postStatus($text, $hashtag)
    {
        $enable = XenForo_Application::get('options')->enableTwitterBot;

        if ($enable) {

            // https://github.com/J7mbo/twitter-api-php

            $settings = array(
                'oauth_access_token' => XenForo_Application::get('options')->twitterOauthToken,
                'oauth_access_token_secret' => XenForo_Application::get('options')->twitterOauthTokenSecret,
                'consumer_key' => XenForo_Application::get('options')->twitterConsumerKey,
                'consumer_secret' => XenForo_Application::get('options')->twitterConsumerSecret,
            );

            $url = 'https://api.twitter.com/1.1/statuses/update.json';
            $requestMethod = 'POST';

            $postfields = array(
                'status' => $text . " " . $hashtag,
                'possibly_sensitive' => false,
                'lat' => '37.235',
                'long' => '-115.811111',
                'display_coordinates' => true
            );

            $twitter = new TwitterAPIExchange($settings);
            $twitter->buildOauth($url, $requestMethod)
                ->setPostfields($postfields)
                ->performRequest();
        }
    }
}
