<?php /** @noinspection SqlResolve */
include_once "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/db-connection.inc.php";

try {
    // Inserimento dati in tabella di database
//    $insert = getPdo()->prepare(
//        'INSERT INTO form (nome, cognome, email, messaggio)
//    VALUES (:nome, :cognome, :email, :messaggio)'
//    );
//    $insert->bindValue(':nome', "Ciccio");
//    $insert->bindValue(':cognome', "Cappuccio");
//    $insert->bindValue(':email', "ciccio@cappuccio.com");
//    $insert->bindValue(':messaggio', "Hello!");
//    $insert->execute();

    // Aggiornamento dati
//    $update = getPdo()->prepare(
//        'UPDATE form
//        SET
//            nome = :nome,
//            messaggio = :messaggio
//        WHERE id = :id'
//    );
//    $update->bindValue(':nome', "Ciccio 1");
//    $update->bindValue(':messaggio', "Hello world!");
//    $update->bindValue(':id', 2);
//    $update->execute();

    // Cancellazione dati
//    $delete = getPdo()->prepare('DELETE FROM form WHERE id = :id');
//    $delete->bindValue(':id', 2);
//    $delete->execute();

    // Lettura dati
    $stmt = getPdo()->query('SELECT * FROM form');

    echo '<pre>';

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $key => $row) {
        echo $key . '<br>';
        print_r($row);
    }

} catch (PDOException $e) {
    echo '<pre>';
    print_r($e);
}
