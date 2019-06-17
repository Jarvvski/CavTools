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
                'award_date' => DateTime::createFromFormat('m-d-Y', '03-19-2003')->getTimestamp(),
                'filename' => ''
            ],
            1 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '09-01-2004')->getTimestamp(),
                'filename' => ''
            ],
            2 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '08-10-2009')->getTimestamp(),
                'filename' => ''
            ],
            3 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '09-18-2010')->getTimestamp(),
                'filename' => ''
            ],
            4 => [
                'relation_id' => $relation['relation_id'],
                'award_id' => 48,
                'from_user_id' => 263,
                'award_date' => DateTime::createFromFormat('m-d-Y', '06-02-2011')->getTimestamp(),
                'filename' => ''
            ]
        ];

        $awardModel = XenForo_Model::create('PixelExit_Roster_Model_RosterAward');

        foreach ($awards as $award) {
            $recordDw = XenForo_DataWriter::create('PixelExit_Roster_DataWriter_AwardRelation');
            $recordDw->bulkSet($award);
            $recordDw->save();

            $record = $recordDw->getMergedData();

            $awardModel->applyAwardRecordCitation($record['record_id'], $award['filename']);
        }

        return;
    }
}
