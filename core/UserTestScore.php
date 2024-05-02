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
        $this->data["name"] = $this->data[0];
        $this->data[0] = $this->getUserIdst($this->data[0]);
        $this->data[2] = $this->getDatetime($this->data[2]);
        $this->data[3] = $this->getDatetime($this->data[3]);

        if (
            $this->data[0] &&
            ($refId = $this->getReferenceId($this->data[1]))
        ) {
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
        $query_ref = "SELECT idOrg FROM learning_organization WHERE objectType = 'test' AND idResource='$testId'";
        $res = sql_query($query_ref);
        if (!sql_num_rows($res)) {
            $file = _base_ . "/testCompletion/failedTestsCompletion.csv";
            $fp = fopen($file, "a");
            fputcsv($fp, [
                $this->data["name"],
                $this->data[1],
                "test reference not found",
            ]);
            fclose($fp);
            echo "<p style='background: #c21919; color: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-family: \"Courier New\", Courier, monospace;'>Error processing request. Referenced test does not exist in the database</p>";
            return 0;
        }
        list($idOrg) = sql_fetch_row($res);

        return $idOrg;
    }

    private function getUserIdst($userId)
    {
        $userId = $this->getAbsoluteId($userId);
        $query = "SELECT idst FROM core_user WHERE userid='$userId'";
        $res = sql_query($query);
        if (!sql_num_rows($res)) {
            $file = _base_ . "/testCompletion/failedTestsCompletion.csv";
            $fp = fopen($file, "a");
            fputcsv($fp, [$userId, $this->data[1], "user not found"]);
            fclose($fp);
            echo "<p style='background: #c21919; color: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-family: \"Courier New\", Courier, monospace;'>Error processing request. User does not exist in the database</p>";
            return 0;
        }

        list($idst) = sql_fetch_row($res);

        return $idst;
    }

    private function createTestTrack($data, $idReference)
    {
        $exist = "SELECT *  FROM learning_testtrack WHERE idReference = $idReference AND idUser = $data[0] AND idTest = $data[1] AND score_status = '$data[4]' AND score = $data[5]";
        $re = sql_query($exist);
        if (sql_num_rows($re) > 0) {
            echo "<p style='background: #c21919; color: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-family: \"Courier New\", Courier, monospace;'>Error processing request. Record already exist </p>";
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

        $file = _base_ . "/testCompletion/passedTestsCompletion.csv";
        $fp = fopen($file, "a");
        fputcsv($fp, [$data["name"], $data[0], $data[1]]);
        fclose($fp);

        echo "<p style='background: green; color: white; padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-family: \"Courier New\", Courier, monospace;'>Success. Record created successfully</p>";

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
