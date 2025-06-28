<?php
$year = date('Y');

if ($year !== '2025') {
    /**
     * Se l'anno è successivo al 2025, siccome questo script l'ho realizzato nel 2025,
     * allora imposto il copyright dal 2025 all'anno corrente, mettendoci un trattino (-) in mezzo.
     */
    $year = "2025&minus;{$year}";
}
?>
<div class="container mt-5">
    <hr>

    <footer class="row">
        <div class="col-4 d-flex align-items-center">
            <div class="mb-3 text-body-secondary">
                <a class="text-body-secondary" href="https://getbootstrap.com/"
                   target="_blank" title="Bootstrap" aria-label="Bootstrap"><i class="bi bi-bootstrap"></i></a>
                &middot;&middot;&middot;
                <strong>DarCas Software &copy; <?= $year ?></strong>
            </div>
        </div>

        <ul class="nav col-8 justify-content-end list-unstyled d-flex">
            <li class="ms-3">
                <a class="text-body-secondary" href="https://www.instagram.com/darcas"
                   target="_blank" aria-label="Instagram" title="Instagram">
                    <i class="bi bi-instagram"></i>
                </a>
            </li>
            <li class="ms-3">
                <a class="text-body-secondary" href="https://github.com/DarCas"
                   aria-label="GitHub" title="GitHub" target="_blank">
                    <i class="bi bi-github"></i>
            </li>
        </ul>
    </footer>
</div>
