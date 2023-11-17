<h2><?php _e('Shortcode List'); ?></h2>

<div class="es-settings-fields es-settings-fields--slugs ">
    <h2>Please use the shortcode below to display listings.</h2>
    <h4>[es_my_listing]</h4>
    <div>
        <p>This shortcode allows the following attributes which will output different listings results.</p>
        <p>layout=grid-3 (or grid-2, or list, or half_map). Use half-map layout for page builder page with full
            width template only.</p>
        <p>type=es_type="term id" (es_category="term id", etc. ) - ID can be found in Data Manager.</p>
        <p>posts_per_page=Num properties to show per one page.</p>
        <p>disable_navbar=1 (or =0). Visible by default (0).</p>
        <p>show_sort=1 (or =0). Show or hide sort field in navbar.</p>
        <p>show_total=Show or hide num of listings in navbar.</p>
        <p>show_page_title=1 (or =0). Show or Hide shortcode page title. Useful attribute for page builder
            pages.</p>
        <p>show_layouts=1 (or =0). Show or hide layout switcher in navbar.</p>
        <p>sort=newest (or lowest_price, or highest_price, or largest_sq_ft, or bedrooms, or bathrooms, or
            oldest, or label-name).</p>
        <p>limit - Num of properties to show. Also disable pagination if the argument is set.</p>
        <p>page_num - Default current page number.</p>
        <p>ignore_search-1 (or =0). Default is 0. Set to 1 if you want to ignore search form results for this
            shortcode.</p>
        <p>enable_search=1 (or =0). Default is 0. Set to 1 if you want to display the search form above the
            listings navbar.</p>
        <p>search_type-see [es_search_form] - search_type</p>
        <p>enable_ajax-0 (or =1). Enable ajax search.</p>
        <p>view_all_page_id - Page ID for view_all_link_name button.</p>
        <p>view_all_link_name - 'View all' button label.</p>
        <p>hfm_full_width - calculates half-map layout width using javascript. Ignore all markup around hfm.
            Uses for page builder pages.</p>
        <p>fields_delimiter - Delimiter symbol for multiple values of property field. Comma (,) by default. If
            fields_delimiter=’*’ then custom_field=”value1*value2*value3”</p>
        <p>[es_my_listing min_price="50000" max_price="200000"] - to show listings within specific price
            range</p>
    </div>
    <h2>Please use the shortcode below to displays search form.</h2>
    <h4>[es_search_form]</h4>
    <div>
        <p>layout=vertical (or =horizontal) (e.g. [es_search_form layout="vertical"] )</p>
        <p>address_placeholder - Placeholder for address field in search form.</p>
        <p>search_type=main (or simple, or advanced)</p>
        <p>title - Search form title (Only for advanced or main search_type).</p>
        <p>padding - Search form CSS padding. Add spaces in the search form container. Used for search_type=”main” only. Example: padding=”50px” or padding=”10px 20px 10px 50px”
            search_page_id - ID of page with [es_my_listing] shortcode (in Estatik 3 it was [es_search] shortcode. For now it is the same shortcode).</p>
        <p>background - CSS background color or image for search form. Allowed for search_type=main. background=”#000”, where #000 - background color.</p>
        <p>is_address_search_enabled=0 (or =1). Allowed for search_type=main (or =simple).</p>
        <p>is_main_filter_enabled=0 (or =1). Hides | Shows main search fields. Allowed for search_type=main (or simple).</p>
        <p>is_collapsed_filter_enabled=0 (or =1). Hides | Shows collapsed search fields. Allowed for search_type=main (or simple).</p>
        <p>main_fields - comma separated fields to show. Allowed for search_type=main (or simple).</p>
        <p>collapsed_fields - comma separated fields to show. Allowed for search_type=main (or simple).</p>
        <p>enable_saved_search=0 - to disable Save search button.</p>
        <p>fields - comma separated fields to show. Used for search_type=advanced.</p>
    </div>
    <h2>Please use the shortcode below to displays properties slider widget.</h2>
    <h4>[es_properties_slider]</h4>
    <div>
        <p>You can use ignore_search=1 to ignore the search form.</p>
        <p>layout=vertical ( or horizontal) ( e.g. es_properties_slider layout="vertical" )</p>
        <p>slides_per_serving - Number of slides to show</p>
        <p>is_arrows_enabled=0 (or =1). Default arrows are visible (1)</p>
        <p>space_between_slides - space between slides in px. space_between_slides=”24” by default</p>
        <p>autoplay=0 (or =1). Autoplay is enabled by default (1)</p>
        <p>autopay_timeout - Time in ms between slides play. Default is 5000</p>
        <p>limit - num of listings in the carousel. Default is 20</p>
        <p>infinite - 0 (or =1). Infinite carousel is enabled by default (1)</p>
    </div>
    <h2>Please use the shortcode below to displays form for user login , register or reset password.</h2>
    <h4>[es_authentication]</h4>
    <div>
        <p>auth_item=login-buttons or reset-form,or buyer-register-buttons, or buyer-register-form, or login-form.</p>

        <p>enable_facebook=0 (or =1). Show Facebook auth button. Facebook API credentials are required.</p>

        <p>enable_google=0 (or =1). Show google auth button. Google API credentials are required.</p>

        <p>enable_login_form=0 (or =1). Set 0 for disable login form.</p>

        <p>login_title - Login form title</p>

        <p>login_subtitle - Login form subtitle</p>

        <p>enable_buyers_register - enables buyers register form</p>

        <p>buyer_register_title - Buyer register form title</p>

        <p>buyer_register_subtitle - Buyer register form subtitle</p>
    </div>
    <h2>Please use the shortcode below to displays a profile page of the authenticated user.</h2>
    <h4>[es_profile]</h4>
</div>