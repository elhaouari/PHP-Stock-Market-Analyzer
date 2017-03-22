<?php
require_once 'PDOConnection.php';
require_once 'StockDatabase.php';
require_once 'StockSymbol.php';
require_once 'Analysis.php';


$stock = new StockDatabase(PDOConnection::getPDO());
$stock->setStartTime('15:1:2017');
$stock->setEndTime('9:3:2017');

$stockSymbol = new StockSymbol();
$stockSymbol->main($stock, 'tickerMarket.txt');


$a = new Analysis(PDOConnection::getPDO());
$a->masterLoop($stockSymbol->getSymbols('tickerMarket.txt'));
?>



