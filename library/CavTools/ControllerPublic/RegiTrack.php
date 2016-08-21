<?php

class CavTools_ControllerPublic_RegiTrack extends XenForo_ControllerPublic_Abstract
{

    public function actionIndex()
    {
        $regiPosGrps = XenForo_Application::get('options')->regiPosGrps;
        $regiPosGrps = explode(',',$regiPosGrps);
        $bat1PosGrps = XenForo_Application::get('options')->bat1PosGrps;
        $bat1PosGrps = explode(',',$bat1PosGrps);
        $bat2PosGrps = XenForo_Application::get('options')->bat2PosGrps;
        $bat2PosGrps = explode(',',$bat2PosGrps);


        $model = $this->_getRegiModel();
        $regiTotal = $model->getSectionStats();
        $bat1Total = 0;
        $bat2Total = 0;
        $regiStats = array();
        $bat1Stats = array();
        $bat2Stats = array();

        foreach ($regiPosGrps as $posGrp) {
            $num = $model->getSectionStats($posGrp);
            $query = $model->getPosGrpInfo($posGrp);
            $store = array(
                'title' => $query['title'],
                'number' => $num
            );

            array_push($regiStats, $store);
        }

        foreach ($bat1PosGrps as $posGrp) {
            $num = $model->getSectionStats($posGrp);
            $query = $model->getPosGrpInfo($posGrp);
            $store = array(
                'title' => $query['title'],
                'number' => $num
            );
            $bat1Total += $num;

            array_push($bat1Stats, $store);
        }

        foreach ($bat2PosGrps as $posGrp) {
            $num = $model->getSectionStats($posGrp);
            $query = $model->getPosGrpInfo($posGrp);
            $store = array(
                'title' => $query['title'],
                'number' => $num
            );
            $bat2Total += $num;

            array_push($bat2Stats, $store);
        }

        $bat1 = array(
            'title' => '1st Battalion',
            'number' => $bat1Total
        );
        $bat2 = array(
            'title' => '2nd Battalion',
            'number' => $bat2Total
        );

        $batStats = array($bat1, $bat2);
        foreach ($batStats as $batStat) {
            $store = array(
                'title' => $batStat['title'],
                'number' => $batStat['number']
            );
            array_push($regiStats, $store);
        }

        //View Parameters
        $viewParams = array(
            'RegiData' => $regiStats,
            'bat1Data' => $bat1Stats,
            'bat2Data' => $bat2Stats
        );

        //Send to template to display
        return $this->responseView('CavTools_ViewPublic_RegiTrack', 'CavTools_RegiTrack', $viewParams);
    }

    protected function _getRegiModel()
    {
        return $this->getModelFromCache( 'CavTools_Model_RegiTrack' );
    }
}
