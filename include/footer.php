<?php

include_once("../include/messages.php");

?>
        <div class="alerts-container">
            <?= format_errors_clear() ?>
            <?= format_success_clear() ?>
        </div>

<?php if(!isset($no_navbar) || !$no_navbar): ?>
    </div>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3" crossorigin="anonymous"></script>
</body>
</html>