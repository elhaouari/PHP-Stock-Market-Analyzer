<?php

global $pdo;

class StockDatabase
{
    private $pdo;
    private $startDay;
    private $startMonth;
    private $startYear;
    private $endDay;
    private $endMonth;
    private $endYear;
    private $startDate;
    private $endDate;

    function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * day:month:year
     * @param $date
     */
    public function setStartTime($date) {
        $this->startDate = $date;
    }

    /**
     * day:month:year
     * @param $date
     */
    public function setEndTime($date) {
        $this->endDate = $date;
    }

    private function getURL($symbol) {
        // s = symbol, d = end month, e = end day, f = end year, g = , a = begging month, b = begging day, c = begging year
        return "https://ichart.finance.yahoo.com/table.csv?s=$symbol&d=$this->endMonth&e=$this->endDay&f=$this->endYear&g=d&a=$this->startMonth&b=$this->startDay&c=$this->startYear&ignore=.csv";
    }

    private function getCsvFile($outputFile, $symbol) {
        $url = $this->getURL($symbol);
        $content = file_get_contents($url);
        $content = trim(str_replace('Date,Open,High,Low,Close,Volume,Adj Close', '', $content));
        file_put_contents($outputFile, $content);
    }

    private function fileToDatabase($txtFile, $tableName) {
        $file = fopen($txtFile, 'r');
        while(!feof($file)) {
            $line = fgets($file);
            $pieces = explode(',', $line);
            //Date,Open,High,Low,Close,Volume,Adj Close
            $date   = $pieces[0];
            $open   = $pieces[1];
            $high   = $pieces[2];
            $low    = $pieces[3];
            $close  = $pieces[4];
            $volume = $pieces[5];
            $amount_change  = $close - $open;
            $percent_change = ($amount_change/$open)*100;

            if (PDOConnection::isTableExists($tableName) === FALSE) {
                try {
                    $this->pdo->exec("CREATE TABLE $tableName (date DATE, PRIMARY KEY (date),open FLOAT, high FLOAT, low FLOAT, close FLOAT, volume INT, amount_change FLOAT, percent_change FLOAT)");
                }
                catch (Exception $e){
                    die("DB ERROR: ". $e->getMessage());
                }
            }
            $sql = "INSERT INTO $tableName (date, open, high, low, close, volume, amount_change, percent_change)".
                   "VALUE (:date, :open, :high, :low, :close, :volume, :amount_change, :percent_change)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':open', $open);
            $stmt->bindParam(':high', $high);
            $stmt->bindParam(':low', $low);
            $stmt->bindParam(':close', $close);
            $stmt->bindParam(':volume', $volume);
            $stmt->bindParam(':amount_change', $amount_change);
            $stmt->bindParam(':percent_change', $percent_change);
            $stmt->execute();
        }

        fclose($file);
    }

    /**
     * preparation the stock database
     */
    private function preRun() {
        if (!isset($this->startDate)) {
            $this->startDay   = date('j');
            $this->startMonth = date('n') - 1;
            $this->startYear  = date('Y');
        }
        else {
            $startDate = explode(':', $this->startDate);
            $this->startDay   = $startDate[0];
            $this->startMonth = $startDate[1]-1;
            $this->startYear  = $startDate[2];
        }
        if(!isset($this->endDate)){
            $this->endDay   = date('j');
            $this->endMonth = date('n')-1;
            $this->endYear  = date('Y');
        }
        else {
            $endDate = explode(':', $this->endDate);
            $this->endDay   = $endDate[0];
            $this->endMonth = $endDate[1]-1;
            $this->endYear  = $endDate[2];
        }
    }

    public function run($symbol) {
        $this->preRun();
        $symbolFile = "txtFiles/$symbol.txt";
        $this->getCsvFile($symbolFile, $symbol);
        $this->fileToDatabase($symbolFile, $symbol);
    }

}