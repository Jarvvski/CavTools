<?php

class CavTools_EventListener_MilpacCreate
{
    public static function addPUCS($relation)
    {
        $recordDw = XenForo_DataWriter::create('PixelExit_Roster_DataWriter_AwardRelation');

        $awards = [
            0 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '03-18-2003')->getTimestamp(),
                'filename' => "/srv/www/7cav.us/public_html/data/pixelexit/rosters/award_citations/9/9242.jpg"
            ],
            1 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '09-01-2004')->getTimestamp(),
                'filename' => "/srv/www/7cav.us/public_html/data/pixelexit/rosters/award_citations/9/9243.jpg"
            ],
            2 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '08-10-2009')->getTimestamp(),
                'filename' => "/srv/www/7cav.us/public_html/data/pixelexit/rosters/award_citations/9/9244.jpg"
            ],
            3 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '09-18-2010')->getTimestamp(),
                'filename' => "/srv/www/7cav.us/public_html/data/pixelexit/rosters/award_citations/9/9245.jpg"
            ],
            4 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '06-02-2011')->getTimestamp(),
                'filename' => "/srv/www/7cav.us/public_html/data/pixelexit/rosters/award_citations/9/9246.jpg"
            ]
        ];

        $awardModel = XenForo_Model::create('PixelExit_Roster_Model_RosterAward');

        foreach ($awards as $award) {
            $recordDw = XenForo_DataWriter::create('PixelExit_Roster_DataWriter_AwardRelation');
            $recordDw->set('relation_id', $award['relation_id']);
            $recordDw->set('award_id', $award['award_id']);
            $recordDw->set('from_user_id', $award['from_user_id']);
            $recordDw->set('award_date', $award['award_date']);

            $recordDw->save();

            $record = $recordDw->getMergedData();

            $awardModel->applyAwardRecordCitation($record['record_id'], $award['filename']);
        }

        return;
    }
}
