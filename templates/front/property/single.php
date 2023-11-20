<style>
    .es-class-tab-nav ul {
        width: 100%;
        margin-bottom: 0;
        padding-left: 0;
        list-style: none;
        display: flex;
    }
    .es-class-tab-nav ul li.es-property-header-nav {
        padding: 5px 10px;
        margin: 5px
    }

    .es-class-tab-nav ul li.es-property-header-nav:hover {
        background: rgba(128, 128, 128, 0.58);
    }
    .es-property-header-nav a {
        color: black;
        font-size: 15px;
        font-weight: 700;
    }
    @media only screen and (max-width: 600px) {
        .es-class-tab-nav ul li.es-property-header-nav a{
            display: none;
        }
    }
</style>

<?php do_action('es_before_single_wrapper', get_the_ID()); ?>
    <div class='js-es-single es-single es-single--<?php echo ests('single_layout'); ?>'
         data-layout="<?php echo ests('single_layout'); ?>">
        <?php do_action('es_property_breadcrumbs', get_the_ID()); ?>
        <div class="js-es-single-property-layout">
            <?php do_action('es_single_property_layout', get_the_ID()); ?>
        </div>

        <?php $instance = es_get_sections_builder_instance();
        if ($sections = $instance::get_items('property')) :

            ?>
            <div class="es-property-section es-class-tab-nav">
                <ul>
                <?php
                $icons = array (
                    'basic-facts' => '<i class="fa fa-list-alt" aria-hidden="true"></i>',
                    'description' => '<i class="fa fa-file-text-o" aria-hidden="true"></i>',
                    'features' => '<i class="fa fa-list-alt" aria-hidden="true"></i>',
                    'location' => '<i class="fa fa-map-marker" aria-hidden="true"></i>',
                    'rental-rates' => '<i class="fa fa-money" aria-hidden="true"></i>',
                );
                foreach ($sections as $section) :
                    ob_start();
                    es_the_entity_section($section, get_the_ID());
                    $content = ob_get_clean();
                    if (!empty($content)):
                        ?>
                        <li class="es-property-header-nav">
                            <?php echo isset($icons[$section['machine_name']])?$icons[$section['machine_name']] : '';?>
                            <a
                                    href="#<?php echo $section['machine_name'] ?>">

                                <?php echo $section['label'] ?>
                            </a></li>
                    <?php
                    endif;
                endforeach;
                ?>
                <li class="es-property-header-nav"><i class="fa fa-calendar-check-o" aria-hidden="true"></i><a href="#ipro-calendar">IPRO Booking Calendar</a></li>
                </ul>
            </div>
            <?php
            foreach ($sections as $section) :
                do_action('es_single_property_section', $section, get_the_ID());
            endforeach;
        endif; ?>

        <div class="es-property-section es-property_section--booking-form" id="ipro-calendar">
            <h3 class="es-property-section__title">IPRO Booking Calendar</h3>
            <div class="es-property-section__content" style="padding: 0px">
                <?php
                $propertyID = 12912;
                ?>
                <iframe src="https://www.iprodemo.co.uk/<?PHP echo $propertyID; ?>/?alttemplate=iframecalendar"
                        width="100%" height="700" frameborder="0" loading="lazy"></iframe>
            </div>
        </div>

        <?php do_action('es_after_single_property_content'); ?>
        <?php do_action('es_after_single_content'); ?>
    </div>
    </div>

<?php do_action('es_after_single_wrapper', get_the_ID());


