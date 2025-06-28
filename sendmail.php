<?php /** @noinspection SqlResolve */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/db-connection.inc.php";

// Verifico che il form mi stia arrivando tramite POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collezione di dati
    $data = [
        'nome' => trim($_POST['nome'] ?? ''),
        'cognome' => trim($_POST['cognome'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'messaggio' => trim(strip_tags($_POST['messaggio']) ?? ''),
    ];

    // Collezione di errori
    $errors = [];

    // Verifico che nome non sia vuoto e che sia maggiore di 2 caratteri
    if (empty($data['nome'])) {
        $errors['nome'] = "Il nome è obbligatorio";
    } else if (mb_strlen($data['nome']) < 3) {
        unset($data['nome']);

        $errors['nome'] = 'Il nome deve essere lungo almeno 3 caratteri';
    }

    // Verifico che cognome non sia vuoto e che sia maggiore di 2 caratteri
    if (empty($data['cognome'])) {
        $errors['cognome'] = "Il cognome è obbligatorio";
    } else if (mb_strlen($data['cognome']) < 2) {
        unset($data['cognome']);

        $errors['cognome'] = "Il cognome deve essere lungo almeno 2 caratteri";
    }

    // Verifico che e-mail non sia vuoto e che sia formalmente valida
    if (empty($data['email'])) {
        $errors['email'] = "Il campo e-mail è obbligatorio";
    } else if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
        unset($data['email']);

        $errors['email'] = "L'indirizzo e-mail non è valido";
    }

    // Verifico che il messaggio non sia vuoto è che sia almeno di 50 caratteri
    if (empty($data['messaggio'])) {
        $errors['messaggio'] = "Il campo messaggio è obbligatorio";
    } else if (mb_strlen($data['messaggio']) < 2) {
        unset($data['messaggio']);

        $errors['messaggio'] = 'Il messaggio deve essere lungo almeno 50 caratteri';
    }

    // Se non ci sono errori continuo
    if (empty($errors)) {
        // Salvo il messaggio nel database
        $insert = getPdo()->prepare(
            'INSERT INTO form (nome, cognome, email, messaggio)
                VALUES (:nome, :cognome, :email, :messaggio)'
        );
        $insert->bindParam(':nome', $data['nome']);
        $insert->bindParam(':cognome', $data['cognome']);
        $insert->bindParam(':email', $data['email']);
        $insert->bindParam(':messaggio', $data['messaggio']);
        $insert->execute();

        // Inizio le elaborazioni per invio e-mail del messaggio

        $messaggio = nl2br($data['messaggio']);

        // Heredoc
        $message = <<<TEXT
<ul>
    <li>Nome: {$data['nome']}</li>
    <li>Cognome: {$data['cognome']}</li>
    <li>E-mail: {$data['email']}</li>
    <li>Messaggio:<br>
{$messaggio}</li>
</ul>
TEXT;

        $errorMail = @mail(
            'dario.casertano@gmail.com',
            'Messaggio da guestbook dal sito demo',
            $message
        );

        if ($errorMail) {
            header('Location: /ko.php?' . http_build_query([
                    'error' => 'Errore lato server'
                ]));
        } else {
            header('Location: /?successo=true');
        }
    } else {
        $_SESSION['data'] = $data;
        $_SESSION['errors'] = $errors;

        header('Location: /');
    }

} else {
    header('Location: /ko.php?' . http_build_query([
            'error' => 'Non capisco la tua lingua'
        ]));
}
