<?php

class PDOConnection{
    private static $pdo = null;

    private function __construct()
    {
        $db_dsn      = 'mysql:dbname=stock_market;host=127.0.0.1';
        $db_user     = 'root';
        $db_password = 'root';

        self::$pdo = new PDO($db_dsn, $db_user, $db_password);
        self::$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );//Error Handling

        try {
            self::$pdo = new PDO($db_dsn, $db_user, $db_password);
        } catch (PDOException $e) {
            die( 'Connection failed: ' . $e->getMessage());
        }
    }

    public static function getPDO() {
        if (self::$pdo != null && self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        new PDOConnection();
        return self::$pdo;
    }

    /**
     * Check if a table exists in the current database.
     *
     * @param PDO $pdo PDO instance connected to a database.
     * @param string $table Table to search for.
     * @return bool TRUE if table exists, FALSE if no table found.
     */
    public static function isTableExists($table) {

        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $result = self::$pdo->query("SELECT 1 FROM $table LIMIT 1");
        } catch (Exception $e) {
            // We got an exception == table not found
            return FALSE;
        }

        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== FALSE;
    }
}