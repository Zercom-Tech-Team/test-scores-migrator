<?php

namespace Core;

use FormaLms\db\DbConn;

class UserTestScore
{
    public $data = [];

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $this->data[0] = $this->getUserIdst($this->data[0]);
        $this->data[2] = $this->getDatetime($this->data[2]);
        $this->data[3] = $this->getDatetime($this->data[3]);

        $refId = $this->getReferenceId($this->data[1]);

        if ($this->data[0] && $refId) {
            $this->createTestTrack($this->data, $refId);
        }
    }

    private function getAbsoluteId($userId)
    {
        return "/" . $userId;
    }

    private function getDatetime($timestamp)
    {
        return date("Y-m-d H:i:s", $timestamp);
    }

    private function getReferenceId($testId)
    {
        $created_at = $this->getTimeCreated($testId);

        $query_ref = "SELECT param_value FROM learning_lo_param WHERE param_name='idReference' AND created_at='$created_at'";
        $res = sql_query($query_ref);
        if (!sql_num_rows($res)) {
            return 0;
        }
        list($param_value) = sql_fetch_row($res);

        return $param_value;
    }

    private function getTimeCreated($testId)
    {
        $query_created = "SELECT created_at FROM learning_test WHERE idTest = $testId";
        $res = sql_query($query_created);

        if (!sql_num_rows($res)) {
            return 0;
        }
        list($created_at) = sql_fetch_row($res);

        return $created_at;
    }

    private function getUserIdst($userId)
    {
        $userId = $this->getAbsoluteId($userId);
        $query = "SELECT idst FROM core_user WHERE userid='$userId'";
        $res = sql_query($query);
        if (!sql_num_rows($res)) {
            $file = _base_ . "/testCompletion/failedTestsCompletion.csv";
            $fp = fopen($file, "a");
            fputcsv($fp, [$userId, $this->data[1]]);
            fclose($fp);
            return 0;
        }

        list($idst) = sql_fetch_row($res);
        $file = _base_ . "/testCompletion/passedTestsCompletion.csv";
        $fp = fopen($file, "a");
        fputcsv($fp, [$userId, $this->data[1], $idst]);
        fclose($fp);

        return $idst;
    }

    private function createTestTrack($data, $idReference)
    {
        $exist = "SELECT COUNT(idTrack) FROM learning_testtrack WHERE idReference = $idReference AND idUser = $data[0] AND idTest = $data[1]";
        if (sql_query($exist) > 0) {
            return 0;
        }
        $query = "INSERT INTO learning_testtrack
            (idReference, idUser, idTest, date_attempt, date_end_attempt, last_page_seen, score_status, score)
            VALUES($idReference, $data[0], $data[1], '$data[2]', '$data[3]', 1, '$data[4]', $data[5])";

        if (!sql_query($query)) {
            return false;
        }

        list($idTrack) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

        $this->createCommonTrack($data, $idReference, (int) $idTrack);

        // $actions = [
        //     "testTrack" => $query,
        //     "commonTrack" => $commonTrack,
        // ];

        // return $actions;
    }

    private function createCommonTrack($data, $idReference, $idTrack)
    {
        $query = "INSERT INTO learning_commontrack
            (idReference, idUser, idTrack, objectType, status, dateAttempt, firstAttempt, first_complete, last_complete)
            VALUES($idReference, $data[0], $idTrack, 'test', '$data[4]', '$data[2]', '$data[3]','$data[3]','$data[3]')";
        if (!sql_query($query)) {
            return false;
        }

        // return $query;
    }
}
