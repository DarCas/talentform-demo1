<?php
declare(strict_types=1);

// Se la super globale $GLOBALS non ha "pdo" vuol dire che non è stata ancora creata
// una connessione database. Quindi, al creo.
if (!isset($GLOBALS['pdo'])) {
    /**
     * Host del database
     */
    $dbhost = 'localhost';

    /**
     * Nome del database
     */
    $dbname = 'demo1';

    /**
     * Username del database
     */
    $dbuser = 'demo1';

    /**
     * Password del database
     */
    $dbpass = 'otZppv*aK3tob3R[';

    /**
     * Database Source Name
     */
    $dsn = "mysql:host={$dbhost};dbname={$dbname}";

    try {
        $GLOBALS['pdo'] = new PDO($dsn, $dbuser, $dbpass);
        $GLOBALS['pdo']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo '<pre>';
        print_r($e);
    }
}

if (function_exists('getPdo') === false) {
    /**
     * Questo è un helper singleton per connettermi al database.
     *
     * @return PDO
     */
    function getPdo(): ?PDO
    {
        return $GLOBALS['pdo'];
    }
}
