<?php

class CavTools_ControllerPublic_PublicEnlistmentController extends XenForo_ControllerPublic_Abstract {


    public function actionIndex()
    {

        // store a nice and easy obj of our user
        $user = XenForo_Visitor::getInstance();

        // overide default forum/server times
        date_default_timezone_set("UTC");

        // check if enlistments are disabled
        $this->handleEnlistmentDisabled();

        // @TODO: check if logged in, as will need to sign up for forums first

        // check if user has ability to see the enlistment form
        if (!$user->hasPermission('CavToolsGroupId', 'EnlistmentForm')) {
            throw $this->getNoPermissionResponseException();
        }

        $supportedGames = explode(',', XenForo_Application::get('options')->games);

        $model = $this->_getEnlistmentModel();

        // check if the user already has an elistment open
        // $openEnlistment = $model->openEnlistment($user->user_id);
        // if ($openEnlistment) {
        //     $this->handleOpenEnlistment($openEnlistment);
        // }

        // time to send them the enlistment form
        $viewParams = [
            'username' => $user->username,
            // 'milpac' => $milpac, // not sure about using this yet
            'threads' => $this->_getInformationThreads(),
            'links' => $this->_getInformationLinks(),
            'supported_games' => $supportedGames,
        ];

        return $this->responseView('CavTools_ViewPublic_Enlist', 'CavTools_Enlistment', $viewParams);
    }

    public function actionPost()
    {

    }

    public function handleEnlistmentDisabled()
    {
        $enabled = XenForo_Application::get('options')->enableEnlistmentForm;

        // if Enlistments are disabled, return the 'disabled' view
        if (!$enabled) {

            $viewParams = [
                'disabled_message' => XenForo_Application::get('options')->enlistmentsDisabledMessage
            ];

            return $this->responseView('CavTools_ViewPublic_EnlistmentsDisabled', 'CavTools_EnlistmentsDisabled', $viewParams);
        }
    }

    /**
     * @TODO
     * @param  [type] $enlistment [description]
     * @return [type]             [description]
     */
    public function handleOpenEnlistment($enlistment)
    {
        // should either present them a page showing their information
        // or we just send them to the thread itself?
    }

    protected function _getInformationLinks()
    {
        return [
            'general_orders' => XenForo_Application::get('options')->genOrders,
            'code_of_conduct' => XenForo_Application::get('options')->codeOfConduct,
        ];
    }

    protected function _getInformationThreads()
    {
        return [
            'steamID' => XenForo_Application::get('options')->SteamIDthread ?? '',
            'requirements' => XenForo_Application::get('options')->minRequireThread ?? '',
        ];
    }

    protected function _getEnlistmentModel()
    {
        return $this->getModelFromCache('CavTools_Model_Enlistment');
    }
}
