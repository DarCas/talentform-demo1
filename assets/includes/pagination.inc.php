<?php
/**
 * @var int $paginaCorrente
 * @var int $offset
 * @var int $quantePagine
 */
?>
<nav aria-label="Page navigation">
    <ul class="pagination">
        <?php
        // Se sono in prima pagina non ha senso visualizzare i comandi
        // "Prima pagina" e "Pagina precedente"
        if ($paginaCorrente !== 1) {
            // Se sono in seconda pagina non ha senso visualizza il comando
            // "Prima pagina".
            if ($paginaCorrente !== 2) {
                ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $_SERVER['PHP_SELF']; ?>"
                       aria-label="Previous" title="Prima pagina">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <?php
            }
            ?>
            <li class="page-item">
                <a class="page-link" href="<?= $_SERVER['PHP_SELF']; ?>?p=<?= $paginaCorrente - 1 ?>"
                   aria-label="Previous" title="Pagina precedente">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php
        }

        // Stampo tutte le pagine
        for ($pagina = 1; $pagina <= $quantePagine; ++$pagina) {
            ?>
            <li class="page-item <?php
            if ($pagina === $paginaCorrente) {
                echo 'active';
            }
            ?>">
                <a class="page-link" href="<?= $_SERVER['PHP_SELF']; ?>?p=<?= $pagina ?>"><?= $pagina ?></a>
            </li>
            <?php
        }

        // Se la pagina corrente è minore al numero totale di pagine
        // visualizzo "Pagina successiva"
        if ($paginaCorrente < $quantePagine) {
            ?>
            <li class="page-item">
                <a class="page-link" href="<?= $_SERVER['PHP_SELF']; ?>?p=<?= $paginaCorrente + 1 ?>"
                   aria-label="Next" title="Pagina successiva">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
            <?php
        }

        // Se la pagina corrente + 1 (ovvero sono a 2 pagine dalla fine)
        // visualizzo "Ultima pagina"
        if (($paginaCorrente + 1) < $quantePagine) {
            ?>
            <li class="page-item">
                <a class="page-link" href="<?= $_SERVER['PHP_SELF']; ?>?p=<?= $quantePagine ?>"
                   aria-label="Next" title="Ultima pagina">
                    <span aria-hidden="true">&raquo;&raquo;</span>
                </a>
            </li>
            <?php
        }
        ?>
    </ul>
</nav>
