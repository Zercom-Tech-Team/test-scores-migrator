<?php

namespace Core;

class HandleCsv
{
    public $csvFile;
    public function __construct($csvFile)
    {
        $this->csvFile = $csvFile;
    }

    public function readCSVFile()
    {
        $data = [];

        $file = fopen($this->csvFile, "r");
        $headers = fgetcsv($file);

        while (($line = fgetcsv($file)) !== false) {
            $data[] = $line;
        }

        fclose($file);

        return $data;
    }
}
