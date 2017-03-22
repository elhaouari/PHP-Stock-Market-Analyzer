<?php

class Analysis
{
    private $pdo;

    function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function masterLoop($symbols) {
        foreach ($symbols as $symbol) {
            $nextDayIncrease = 0;
            $nextDayDecrease = 0;
            $nextDayNoChange = 0;
            $total = 0;

            $sumOfIncrease = 0;
            $sumOfDecrease = 0;

            $sql  = "SELECT date, percent_change FROM $symbol WHERE percent_change < '0' ORDER BY date ASC";
            $stmt = $this->pdo->query($sql);

            foreach ($stmt->fetchAll() as $row) {
                $date  = $row['date'];
                $percent_change = $row['percent_change'];

                $sql2 = "SELECT date, percent_change FROM $symbol WHERE date > '$date' ORDER BY date ASC LIMIT 1";
                $stmt2 = $this->pdo->query($sql2);
                $count = $stmt2->rowCount();

                // if tomorrow exists
                if ($count == 1) {
                    $tomorrow = $stmt2->fetch();

                    $tomorrow_date  = $tomorrow['date'];
                    $tomorrow_percent_change = $tomorrow['percent_change'];

                    if ($tomorrow_percent_change > 0) {
                        $nextDayIncrease++;
                        $sumOfIncrease += $tomorrow_percent_change;
                        $total++;
                    }
                    else if ($tomorrow_percent_change < 0) {
                        $nextDayDecrease++;
                        $sumOfDecrease += $tomorrow_percent_change;
                        $total++;
                    }
                    else {
                        $nextDayNoChange++;
                        $total++;
                    }
                }
                else if ($count ==0 ) {

                }
            }

            $nextDayIncreasePercent = ($nextDayIncrease/$total)*100;
            $nextDayDecreasePercent = ($nextDayDecrease/$total)*100;
            $avgIncreasePercent     = $sumOfIncrease/$nextDayIncrease;
            $avgDecreasePercent     = $sumOfDecrease/$nextDayDecrease;

            $this->insertResult($symbol, $nextDayIncrease, $nextDayIncreasePercent, $avgIncreasePercent,
                $nextDayDecrease, $nextDayDecreasePercent, $avgDecreasePercent);
        }
    }

    public function insertResult($symbol, $nextDayIncrease,$nextDayIncreasePercent,$avgIncreasePercent,$nextDayDecrease,$nextDayDecreasePercent,$avgDecreasePercent) {
        $buyValue  = $nextDayIncreasePercent*$avgIncreasePercent;
        $sellValue = $nextDayDecreasePercent*$avgDecreasePercent;

        $sql = "SELECT * FROM analysis WHERE symbol LIKE '$symbol'";
        $stmt = $this->pdo->query($sql);
        if ($stmt->rowCount() == 0 ) {
            $sql = "INSERT INTO analysis (symbol, dayInc, prcOfDayInc, avgDayInc, dayDec, prcOfDayDec, avgDayDec, buyValue, sellValue) VALUE  ".
                   "('$symbol', '$nextDayIncrease', '$nextDayIncreasePercent', '$avgIncreasePercent', '$nextDayDecrease', '$nextDayDecreasePercent', '$avgDecreasePercent', '$buyValue', '$sellValue')";
            $this->pdo->exec($sql);
        }
        else {
            $sql = "UPDATE analysis SET " .
            "dayInc='$nextDayIncrease', prcOfDayInc='$nextDayIncreasePercent', avgDayInc='$avgIncreasePercent', dayDec='$nextDayDecrease', prcOfDayDec='$nextDayDecreasePercent', avgDayDec='$avgDecreasePercent', buyValue='$buyValue', sellValue='$sellValue'".
            " WHERE symbol='$symbol'";
            $this->pdo->exec($sql);
        }
    }
}