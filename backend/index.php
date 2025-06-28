<?php
declare(strict_types=1);

/**
 * Avvio la sessione di PHP. Questo domando dev'essere il primo comando visibile nella pagina.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Carico in $data i valori già inseriti del form
 */
$data = $_SESSION['data'] ?? null;

/**
 * Carico in $errors gli eventuali errori generati dal form
 */
$errors = $_SESSION['errors'] ?? null;

/**
 * Includo le funzionalità del database
 */

include "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/db-connection.inc.php";

/**
 * @var string $usernm
 * @var string $passwd
 */

if (!empty($_GET['successo']) &&
    ($_GET['successo'] === 'true')
) {
    /**
     * Se le operazioni di salvataggio e invio del form vanno a buon fine
     * invio in $_GET il parametro "successo" valorizzato a "true" e imposto
     * un refresh della pagina dopo 5 secondi ricaricando la stessa pagina
     * ma senza valori di $_GET.
     */
    header('Refresh: 5; url=/backend/');
}

/**
 * Variabile di stato di autenticazione
 */
$logged = false;

// Se il cookie "logged" esiste, verifico che il contenuto sia corretto e che
// l'utente sia coerente al database.
if (isset($_COOKIE['logged'])) {
    // Il valore del cookie è nella forma [id]:[hash]
    // Esplodo per ":" e assegno a $id e $hash le due parti
    [$id, $hash] = explode(':', $_COOKIE['logged']);

    // Seleziono l'utente dalla tabella degli utenti in base al valore di $id
    $select = getPdo()->prepare('SELECT * FROM `users` WHERE `id` = :id');
    $select->bindValue(':id', $id, PDO::PARAM_INT);
    $select->execute();

    $user = $select->fetch(PDO::FETCH_ASSOC);

    // Se l'ID dell'utente esiste e l'hash del record è uguale a $hash
    // allora l'utente è autorizzato ad accedere
    if (!empty($user) && sha1(http_build_query($user)) === $hash) {
        // Imposto la variabile di stato a "true"
        $logged = true;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Guestbook - Backend</title>
    <?php include '../assets/includes/std-meta.inc.php'; ?>
</head>

<body>

<?php include '../assets/includes/header.inc.php'; ?>

<main class="container">
    <?php
    // Se mi sono correttamente autenticato
    if ($logged) {
        if (!empty($_GET['successo']) &&
            ($_GET['successo'] === 'true')
        ) {
            /**
             * Se le operazioni di salvataggio e invio del form vanno a buon fine
             * invio in $_GET il parametro "successo" valorizzato a "true" e stampo
             * un messaggio di conferma all'utente.
             */
            ?>
            <div class="alert alert-info mx-auto" style="width: 50%" role="alert">
                Operazione effettuata con successo!
            </div>
            <?php
        }
        ?>
        <h1 class="mb-4">Benvenuto, <?= $user['fullname'] ?? '' ?>!</h1>

        <?php
        // Conto quanti messaggi ci sono nella tabella
        $count = getPdo()->query('SELECT COUNT(*) FROM `form`');

        // Definisco una costante per indicare quanti messaggi voglio
        // visualizzare per pagina
        define("ITEM_PER_PAGE", 10);

        // Pagina di risultati visualizzata al momento
        $paginaCorrente = isset($_GET['p']) ? (int)$_GET['p'] : 1;

        // Da dove inizio a leggere i dati della tabella del database
        $offset = ($paginaCorrente - 1) * ITEM_PER_PAGE;

        // Quante pagine devo stampare nel paginatore
        $quantePagine = ceil($count->fetchColumn() / ITEM_PER_PAGE);

        // Seleziono tutti i record presenti nella tabella form
        $select = getPdo()->prepare('SELECT * 
        FROM `form` 
        ORDER BY `id` 
        LIMIT :limit 
        OFFSET :offset');
        $select->bindValue(':limit', ITEM_PER_PAGE, PDO::PARAM_INT);
        $select->bindValue(':offset', $offset, PDO::PARAM_INT);
        $select->execute();
        ?>

        <table class="table table-bordered shadow table-striped align-middle">
            <thead class="table-dark">
            <tr>
                <th class="text-end" scope="col">#</th>
                <th scope="col">Nome completo</th>
                <th scope="col">E-mail</th>
                <th scope="col">Messaggio</th>
                <th class="text-end" scope="col">Data inserimento</th>
                <th scope="col" style="width: 110px">&nbsp;</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="6">
                    <?php include "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/pagination.inc.php"; ?>
                </td>
            </tr>
            </tfoot>

            <tbody class="table-group-divider">
            <?php
            foreach ($select->fetchAll(PDO::FETCH_ASSOC) as $row) {
                ?>
                <tr>
                    <th class="text-end" scope="row"><?= $row['id'] ?></th>
                    <td><?= "{$row['cognome']}, {$row['nome']}" ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['messaggio'] ?></td>
                    <td class="text-end"><?php
                        // Elaboriamo la data recuperata dal database facendola diventare un oggetto DateTime
                        $oDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_ricezione']);
                        // Correggiamo il timezone (fuso orario) della data con quello italiano
                        $oDateTime = $oDateTime->setTimezone(new DateTimeZone('Europe/Rome'));

                        // Stampiamo la data nel formato desiderato
                        echo $oDateTime->format('d/m/Y H:i:s');
                        ?></td>
                    <td class="text-center">
                        <a class="btn btn-sm btn-info" href="/backend/?edit=<?= $row['id'] ?>">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <a class="btn btn-sm btn-danger" href="/backend/?delete=<?= $row['id'] ?>">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <div class="text-end">
            <a class="btn btn-warning shadow" href="/backend/crud.php?action=logout">
                <i class="bi bi-box-arrow-right"></i>
                Log Out
            </a>
        </div>

        <?php
        // Apro il form di modifica se passo un valore numerico in $_GET['edit']
        if (isset($_GET['edit']) &&
            is_numeric($_GET['edit'])
        ) {

            if (empty($data)) {
                // Seleziono il record dalla tabella "form"
                $select = getPdo()->prepare('SELECT * FROM `form` WHERE `id` = :id');
                $select->bindValue(':id', $_GET['edit'], PDO::PARAM_INT);
                $select->execute();

                $data = $select->fetch(PDO::FETCH_ASSOC);
            }

            // Verifico che effettivamente il record esista, se esiste faccio vedere
            // il form di modifica
            if (!empty($data)) {
                ?>
                <div class="card mt-4 shadow" style="width: 50%; margin: 0 auto;">
                    <div class="card-body">
                        <h5 class="card-title">Modifica messaggio</h5>
                        <?php
                        // Se si sono verificati errori di compilazione li visualizzo
                        if (!empty($errors)) {
                            ?>
                            <h2>Si sono verificati errori:</h2>
                            <ul>
                                <?php
                                /**
                                 * Stampo tutti gli eventuali errori generati dal form.
                                 */
                                foreach ($errors as $key => $value) {
                                    ?>
                                    <li><?= $key; ?>: <?= $value; ?></li>
                                    <?php
                                }
                                ?>
                            </ul>
                            <?php
                        }
                        ?>

                        <form action="/backend/crud.php" method="post" class="card-text">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $_GET['edit'] ?>">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="inputNome" class="form-label">Nome</label>
                                    <input type="text" class="form-control" id="inputNome"
                                           value="<?= $data['nome'] ?? '' ?>" required
                                           name="nome" placeholder="Il tuo nome">
                                </div>

                                <div class="col-6 mb-3">
                                    <label for="inputCognome" class="form-label">Cognome</label>
                                    <input type="text" class="form-control" id="inputCognome"
                                           value="<?= $data['cognome'] ?? '' ?>" required
                                           name="cognome" placeholder="Il tuo cognome">
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="inputEmail" class="form-label">E-mail</label>
                                    <input type="text" class="form-control" id="inputEmail"
                                           value="<?= $data['email'] ?? '' ?>" required
                                           name="email" placeholder="La tua e-mail">
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="inputMessage" class="form-label">Messaggio</label>
                                    <textarea class="form-control" id="inputMessage" rows="5"
                                              name="messaggio" placeholder="Il tuo messaggio"
                                              style="min-height: 150px"><?php
                                        echo $data['messaggio'] ?? ''
                                        ?></textarea>
                                </div>

                                <div class="col-4">
                                    <a href="/backend/" class="btn w-100 btn-secondary">Chiudi</a>
                                </div>
                                <div class="col-8">
                                    <button type="submit" class="btn w-100 block btn-success">Invia</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-warning" role="alert">
                    Il messaggio non esiste.
                </div>
                <?php
            }

            /**
             * Una volta visualizzati gli eventuali dati dei form e gli eventuali errori,
             * elimino dalla $_SESSION i dati, così che l'utente se aggiorna la pagina non
             * li rivede e deve ricompilare il form.
             */
            unset($_SESSION['data']);
            unset($_SESSION['errors']);
        }
        ?>

        <?php
        // Apro il form di conferma cancellazione se passo un valore numerico in
        // $_GET['delete']
        if (isset($_GET['delete']) &&
            is_numeric($_GET['delete'])
        ) {
            $select = getPdo()->prepare('SELECT * FROM `form` WHERE `id` = :id');
            $select->bindValue(':id', $_GET['delete'], PDO::PARAM_INT);
            $select->execute();

            $data = $select->fetch(PDO::FETCH_ASSOC);

            // Verifico che effettivamente il record esista, se esiste faccio vedere
            // il form di conferma cancellazione
            if (!empty($data)) {
                ?>
                <div class="card mt-4 shadow" style="width: 50%; margin: 0 auto;">
                    <div class="card-body">
                        <h5 class="card-title">Cancella messaggio</h5>
                        <form action="/backend/crud.php" method="post" class="card-text">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $_GET['delete'] ?>">
                            <div class="alert alert-danger" role="alert">
                                Sei sicuro di voler cancellare il messaggio
                                di <?= "{$data['cognome']}, {$data['nome']}" ?>?
                            </div>
                            <button type="submit" class="btn w-100 block btn-danger">Sì</button>
                        </form>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-warning" role="alert">
                    Il messaggio non esiste.
                </div>
                <?php
            }
        }
    } else {
        ?>
        <div class="card shadow mx-auto" style="width: 50%;">
            <div class="card-body">
                <h5 class="card-title">Login</h5>
                <form action="./crud.php" method="post" class="card-text">
                    <input type="hidden" name="action" value="login">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="inputUsernm" class="form-label">Username</label>
                            <input type="text" class="form-control" id="inputUsernm"
                                   required name="usernm">
                        </div>

                        <div class="col-6 mb-3">
                            <label for="inputPasswd" class="form-label">Password</label>
                            <input type="password" class="form-control" id="inputPasswd"
                                   required name="passwd">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn w-100 block btn-success">Accedi</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
    ?>
</main>

<?php include '../assets/includes/footer.inc.php'; ?>

<?php include '../assets/includes/footer-scripts.inc.php'; ?>
</body>
</html>
