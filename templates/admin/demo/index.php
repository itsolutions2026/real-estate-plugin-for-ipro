<?php

/**
 * @var $args array
 */

$step = es_clean( filter_input( INPUT_GET, 'step' ) );
if(empty($step)) $step = 1;

?>

<div class="es-wrap es-demo">
    <?php
        es_load_template( 'admin/demo/steps.php' );
    ?>
</div>
