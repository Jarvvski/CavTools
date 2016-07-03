<?php

class CavTools_ControllerPublic_GithubIssue extends XenForo_ControllerPublic_Abstract {
    public function actionIndex()
    {
        //Get values from options
        $enable = XenForo_Application::get('options')->enableGithubIssues;

        if(!$enable) {
            throw $this->getNoPermissionResponseException();
        }

        if (!XenForo_Visitor::getInstance()->hasPermission('CavToolsGroupId', 'GithubIssueView'))
        {
            throw $this->getNoPermissionResponseException();
        }

        //Set Time Zone to UTC
        date_default_timezone_set("UTC");

        //Set variables
        $username = $this->getUsername();
        $rank     = $this->getRank();

        //View Parameters
        $viewParams = array(
            'rank' => $rank,
            'username' => $username
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_SubmitIssue', 'CavTools_githubIssues', $viewParams);
    }

    public function getUsername()
    {
        $visitor = XenForo_Visitor::getInstance()->toArray();
        return $visitor['username'];
    }

    public function getRank()
    {
        $visitor = XenForo_Visitor::getInstance()->toArray();
        $userID = $visitor['user_id'];

        //Get DB
        $db = XenForo_Application::get('db');

        $rankTitle = $db->fetchRow("
        SELECT title
        FROM xf_pe_roster_rank
        INNER JOIN xf_pe_roster_user_relation
        ON xf_pe_roster_rank.rank_id=xf_pe_roster_user_relation.rank_ID
        WHERE user_id=".$userID."
        ");

        return $rankTitle['title'];
    }

    public function actionPostNote()
    {
        //Action can only be called via post
        $this->_assertPostOnly();
        // done

        $title    = $this->_input->filterSingle('title', XenForo_Input::STRING);
        $problem  = $this->_input->filterSingle('problem', XenForo_Input::STRING);
        $reason   = $this->_input->filterSingle('reason', XenForo_Input::STRING);
        
        $content = array(
                        'title' => $title,
                        'problem' => $problem,
                        'reason' => $reason
                        );

        $visitor = XenForo_Visitor::getInstance()->toArray();
        $this->callAPI($content);
        $this->tweet($visitor);

        // redirect back to the normal scratchpad index page
        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildPublicLink('forums'),
            new XenForo_Phrase('Request sent')
        );
    }

    public function callAPI($content) {

        // curl -H 'Authorization: token $token' -d '{"title": "My title", "body":"My content"}' https://api.github.com/repos/$repoOwner/$repo/issues

        //Get values from options
        $githubToken  = XenForo_Application::get('options')->githubToken;
        $githubRepo      = XenForo_Application::get('options')->githubRepo;
        $githubRepoOwner = XenForo_Application::get('options')->githubRepoOwner;
        $githubIssueLabel = XenForo_Application::get('options')->githubIssueLabel;

        //Set variables
        $token = $githubToken;
        $repo = $githubRepo;
        $repoOwner = $githubRepoOwner;
        $label = $githubIssueLabel;
        $username = $this->getUsername();
        $rank     = $this->getRank();
        
        //Set variables
        $headerValue = " Bearer " . $token;
        $header = array("Authorization:" . $headerValue);
        $title = $content['title'];
        $body  = sprintf("<h2>Problem</h2><br />%s<br /><hr><h2>Reason</h2><br />%s<br><br>-%s %s", $content['problem'], $content['reason'], $rank, $username);
        $data = array("title" => $title, "body" => $body, "labels" => array($label));
        $data_string = json_encode($data);
        $url   = sprintf("https://api.github.com/repos/%s/%s/issues", $repoOwner, $repo);

        //Send curl message
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_USERAGENT,'7Cav');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_exec($ch);
    }

    public function tweet($visitor)
    {
        $twitterModel = $this->_getTwitterBot();
        $text = $visitor['username'] . " sent my creator another feature request. Hopefully it's a good one!";
        $hashtag = "#Github #7Cav #IMO";
        $twitterModel->postStatus($text, $hashtag);
    }

    protected function _getTwitterBot()
    {
        return $this->getModelFromCache( 'CavTools_Model_IMOBot' );
    }
}