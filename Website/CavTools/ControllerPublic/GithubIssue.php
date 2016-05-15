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
    $username = getUsername();
    $rank     = getRank();

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

    return $rankTitle;
  }

  public function actionSubmit()
  {
    //Get values from options
    $githubToken  = XenForo_Application::get('options')->githubToken;
    $githubRepo      = XenForo_Application::get('options')->githubRepo;
    $githubRepoOwner = XenForo_Application::get('options')->githubRepoOwner;

    //Set variables
    $token = $githubToken;
    $repo = $githubRepo;
    $repoOwner = $githubRepoOwner;
    $username = getUsername();
    $rank     = getRank();
    $title    = $this->_input->filterSingle('title', XenForo_Input::STRING);
    $problem  = $this->_input->filterSingle('problem', XenForo_Input::STRING);
    $reason   = $this->_input->filterSingle('reason', XenForo_Input::STRING);
    $solution = $this->_input->filterSingle('solution', XenForo_Input::STRING);

    callAPI($repoOwner, $repo, $token, $title, $problem, $reason, $solution, $username, $rank);
  }

  public function callAPI($repoOwner, $repo, $token, $formTitle, $problem, $reason, $solution, $username, $rank) {

    // curl -i -H 'Authorization: token d6132407cfe91d8b19b14c3a4ae38c1af7f150f2' -d '{"title": "New logo", "body":"Testing tester"}' https://api.github.com/repos/Jarvvski/Test/issues

    //Set variables
    $ch  = curl_init();
    $header = "Authorization: token %s", $token;
    $title = $formTitle;
    $body  = "<h2>Problem</h2><br />%s<br /><hr><h2>Reason</h2><br />%s<br /><hr><h2>Solution</h2><br />%s<br><br>-%s %s", $problem, $reason, $solution, $rank, $username;
    $data = array("title" => $title, "body" => $body);
    $data_string = json_encode($data);
    $url   = "https://api.github.com/repos/%s/%s/issues", $repoOwner, $repo;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CIRLOPT_HEADER, $header);
    curl_exec($ch);
  }
}
