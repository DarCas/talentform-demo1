<?php
declare(strict_types=1);

/**
 * Avvio la sessione di PHP. Questo domando dev'essere il primo comando visibile nella pagina.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Includo le funzionalità del database
 */

include "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/db-connection.inc.php";

/**
 * Verifico che una sessione sia attiva
 */
if (!empty($_SESSION)) {
    /**
     * Carico in $data i valori già inseriti del form
     */
    $data = $_SESSION['data'];

    /**
     * Carico in $errors gli eventuali errori generati dal form
     */
    $errors = $_SESSION['errors'];
}

if (!empty($_GET['successo']) &&
    ($_GET['successo'] === 'true')
) {
    /**
     * Se le operazioni di salvataggio e invio del form vanno a buon fine
     * invio in $_GET il parametro "successo" valorizzato a "true" e imposto
     * un refresh della pagina dopo 5 secondi ricaricando la stessa pagina
     * ma senza valori di $_GET.
     */
    header('Refresh: 5; url=/');
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <title>Guestbook</title>
    <?php include './assets/includes/std-meta.inc.php'; ?>del
</head>

<body>

<?php include './assets/includes/header.inc.php'; ?>

<main class="container">
    <div class="row">
        <?php
        if (!empty($_GET['successo']) &&
            ($_GET['successo'] === 'true')
        ) {
            /**
             * Se le operazioni di salvataggio e invio del form vanno a buon fine
             * invio in $_GET il parametro "successo" valorizzato a "true" e stampo
             * un messaggio di conferma all'utente.
             */
            ?>
            <div class="col-12 mb-3">
                <h1 class="text-center bg-dark rounded py-3">
                    Messaggio aggiunto con successo
                </h1>
            </div>
            <?php
        }
        ?>
        <div class="col-4">
            <div class="card shadow p-2">
                <form action="/sendmail.php" method="post" class="card-body">
                    <h2 class="card-title">Guestbook</h2>
                    <?php
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
                    <div class="card-text">
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
                                <button type="reset" class="btn w-100 btn-secondary">Reset</button>
                            </div>
                            <div class="col-8">
                                <button type="submit" class="btn w-100 block btn-success">Invia</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-8">
            <div class="card shadow p-2">
                <div class="card-body">
                    <?php
                    // Conto quanti messaggi ci sono nella tabella
                    $count = getPdo()->query('SELECT COUNT(*) FROM `form`');
                    // Il numero di messaggi presenti nella tabella
                    $countMessage = $count->fetchColumn();

                    // Definisco una costante per indicare quanti messaggi voglio
                    // visualizzare per pagina
                    define("ITEM_PER_PAGE", 3);

                    // Pagina di risultati visualizzata al momento
                    $paginaCorrente = isset($_GET['p']) ? (int)$_GET['p'] : 1;

                    // Da dove inizio a leggere i dati della tabella del database
                    $offset = ($paginaCorrente - 1) * ITEM_PER_PAGE;

                    // Quante pagine devo stampare nel paginatore
                    $quantePagine = ceil($countMessage / ITEM_PER_PAGE);

                    // Seleziono tutti i messaggi della tabella ordinati per data di ricezione decrescente
                    /**
                     * @var PDOStatement $select
                     */
                    $select = getPdo()->prepare('SELECT * 
                    FROM `form` 
                    ORDER BY `data_ricezione` DESC
                    LIMIT :limit
                    OFFSET :offset');
                    $select->bindValue(':limit', ITEM_PER_PAGE, PDO::PARAM_INT);
                    $select->bindValue(':offset', $offset, PDO::PARAM_INT);
                    $select->execute();
                    ?>
                    <h2 class="card-title">Messaggi <sup>(<?= $countMessage; ?>)</sup></h2>

                    <div class="card-text">
                        <div class="row">
                            <?php
                            // È il primo elemento
                            $first = true;

                            // Ciclo tutti i dati per avere la singola riga della tabella del database
                            foreach ($select->fetchAll(PDO::FETCH_ASSOC) as $row) {
                                // Se non è il primo elemento, stampo una linea divisore
                                if ($first === false) {
                                    ?>
                                    <hr class="mt-2 mb-4">
                                    <?php
                                }
                                ?>
                                <div class="col-12">
                                    <h4>
                                        <?= "{$row['cognome']}, {$row['nome']}" ?>
                                        <a href="mailto:<?= htmlentities($row['email']) ?>">
                                            <i class="bi bi-envelope-at fs-6"></i>
                                        </a>
                                    </h4>
                                    <blockquote class="border-start border-3 py-1 ps-3 ms-2">
                                        <?= nl2br($row['messaggio']) ?>
                                    </blockquote>
                                    <p>
                                        <i class="bi bi-calendar-event"></i>
                                        <small><?php
                                            // Elaboriamo la data recuperata dal database facendola diventare un oggetto DateTime
                                            $oDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $row['data_ricezione']);
                                            // Correggiamo il timezone (fuso orario) della data con quello italiano
                                            $oDateTime = $oDateTime->setTimezone(new DateTimeZone('Europe/Rome'));

                                            // Stampiamo la data nel formato desiderato
                                            echo $oDateTime->format('d/m/Y H:i:s');
                                            ?></small>
                                    </p>
                                </div>
                                <?php
                                /**
                                 * Se sono qui è perché ho stampato il primo elemento della tabella del database.
                                 * Quindi, il successivo sicuramente non sarà il primo. Valorizzo quindi la variabile
                                 * $first a "false".
                                 */
                                if ($first) {
                                    $first = false;
                                }
                            }
                            ?>
                            <div class="col-12">
                                <?php include "{$_SERVER['DOCUMENT_ROOT']}/assets/includes/pagination-advanced.inc.php"; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include './assets/includes/footer.inc.php'; ?>

<?php include './assets/includes/footer-scripts.inc.php'; ?>
</body>
</html>
<?php
/**
 * Una volta visualizzati gli eventuali dati dei form e gli eventuali errori,
 * elimino dalla $_SESSION i dati, così che l'utente se aggiorna la pagina non
 * li rivede e deve ricompilare il form.
 */
unset($_SESSION['data']);
unset($_SESSION['errors']);
