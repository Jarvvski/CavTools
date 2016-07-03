<?php
require_once('TwitterAPIExchange.php');

class CavTools_Model_IMOBot extends XenForo_Model {

    public function postStatus($text, $hashtag)
    {
        // https://github.com/J7mbo/twitter-api-php

        $settings = array(
            'oauth_access_token' => "749728771662569472-fasR8QyyEG1xEebJl2zz2wnsEA7CbDk",
            'oauth_access_token_secret' => "xWDnkePFjDsTytTvVJTkqTCJvIuIRQFq7fCXdsgHtJ33T",
            'consumer_key' => "al1Hl25XPPNwhhlONL4rWHSQZ",
            'consumer_secret' => "70fnTEeTxoHt2nBN8buztXgHvPZzWOl554jxydPmzMaYjraK7a"
        );

        $url = 'https://api.twitter.com/1.1/statuses/update.json';
        $requestMethod = 'POST';

        $postfields = array(
            'status' => $text . $hashtag,
            'possibly_sensitive' => false,
            'lat' => 37.235,
            'long' => -115.811111,
            'display_coordinates' => true
        );

        $twitter = new TwitterAPIExchange($settings);
        echo $twitter->buildOauth($url, $requestMethod)
            ->setPostfields($postfields)
            ->performRequest();
    }
}