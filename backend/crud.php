<?php /** @noinspection SqlResolve */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/db-connection.inc.php";

/**
 * @var string $usernm
 * @var string $passwd
 */

// Verifico che il form mi stia arrivando tramite POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {
        case 'update':
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

            if (!empty($errors)) {
                $_SESSION['data'] = $data;
                $_SESSION['errors'] = $errors;

                header("Location: /backend/?edit={$_POST['id']}");
                exit;
            }

            $update = getPdo()->prepare('UPDATE form
            SET
                nome = :nome,
                cognome = :cognome,
                email = :email,
                messaggio = :messaggio
            WHERE id = :id');
            $update->bindValue(':nome', $data['nome']);
            $update->bindValue(':cognome', $data['cognome']);
            $update->bindValue(':email', $data['email']);
            $update->bindValue(':messaggio', $data['messaggio']);
            $update->bindValue(':id', $_POST['id']);
            $update->execute();

            header('Location: /backend/?successo=true');
            break;

        case 'delete':
            $delete = getPdo()->prepare('DELETE FROM form WHERE id = :id');
            $delete->bindValue(':id', $_POST['id']);
            $delete->execute();

            header('Location: /backend/?successo=true');
            break;

        case 'login':
            $select = getPdo()->prepare('SELECT * 
            FROM `users` 
            WHERE `usernm` = :usernm 
              AND `passwd` = :passwd
            LIMIT 1');
            $select->bindValue(':usernm', $_POST['usernm']);
            $select->bindValue(':passwd', sha1($_POST['passwd']));
            $select->execute();

            $user = $select->fetch(PDO::FETCH_ASSOC);

            if (!empty($user)) {
                setcookie('logged', "{$user['id']}:" . sha1(http_build_query($user)), 0);
            }

            header('Location: /backend/');
            break;

        default:
            header('Location: /backend/');
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'logout') {
    setcookie('logged', '', -1);
    header('Location: /backend/');
} else {
    header('Location: /ko.php?' . http_build_query([
            'error' => 'Non capisco la tua lingua'
        ]));
}
