<?php

class StockSymbol
{

    public function getSymbols($txtFile) {
        $file = fopen($txtFile, 'r');
        $symbols = '';
        while (!feof($file)) {
            $symbol = trim(fgets($file));
            $symbols[] = $symbol;
        }
        fclose($file);

        return $symbols;
    }

    public function main(StockDatabase $stock, $txtSymbols) {
        $symbols = $this->getSymbols($txtSymbols);
        foreach ($symbols as $symbol) {
            $stock->run($symbol);
        }
    }
}