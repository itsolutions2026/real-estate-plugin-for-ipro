<?php

/**
 * Class Es_Cron_Job.
 */
class Es_Cron_Job
{

    private $endpoint;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $cronEnabled;

    private $endpointRouters = array(
        'lastUpdated' => array(
            'endpoint' => 'apis/properties/lastupdated?lastUpdated=time',
        ),
        'property' => array(
            'endpoint' => 'apis/property/propertyId',
        ),
        'properties' => array (
            'endpoint' => 'apis/properties'
        )
    );

    /**
     * Main constructor
     *
     * @since 1.0
     */
    public function __construct()
    {
        //Get the Settings
        $settings = new Es_Settings_Container();
        $settings->load_options();
        $this->endpoint = $settings->__get('ipro_api_endpoint');
        $this->clientId = $settings->__get('ipro_client_id');
        $this->clientSecret = $settings->__get('ipro_client_secret');
        $this->cronEnabled = $settings->__get('is_rest_cron_enabled');

        $this->accessToken = get_transient('ipro_access_token');
        if (empty($this->accessToken)) $this->accessToken = $this->get_accessToken();

    }

    /**
     * Init divi functionality.
     *
     * @return void
     */
    public function init()
    {
        add_action('init', array($this, 'schedule_ipro'));
        add_filter('cron_schedules', array($this, 'add_wp_cron_schedule'));
        add_action('ipro_cron_scheduler', array($this, 'ipro_cron_scheduler'));
        add_action( 'wp_ajax_es_ipro_load_properties', array( $this, 'ajax_load_properties' ) );
    }

    /**
     * Register Ipro WP cron Job
     */
    public function schedule_ipro()
    {
        if (!wp_next_scheduled('ipro_cron_scheduler')) {
            wp_schedule_event(time(), 'ipro_timeInterval', 'ipro_cron_scheduler');
        }
    }

    /**
     * Define Ipro Cron Job Time interval
     * @param $schedules
     * @return mixed
     */
    public function add_wp_cron_schedule($schedules)
    {

        $schedules['ipro_timeInterval'] = array(
            'interval' => 600, // in seconds
            'display' => __('Ipro Time Interval', 'ipro'),
        );
        return $schedules;
    }

    /**
     *  WP-Cron Job Hook controller
     */

    public function ipro_cron_scheduler()
    {
        //Get the Settings
        if(!$this->cronEnabled) return false;

        $this->accessToken = get_transient('ipro_access_token');
        if (empty($this->accessToken)) $this->accessToken = $this->get_accessToken();

        if (empty($this->accessToken)) return false;

        // Get the recent updated property list

        $recentUpdatedProperties = $this->fetchRecentUpdates();

        error_log(print_r($recentUpdatedProperties[1], 1));
    }

    /**
     * API Wrapper
     */
    private function apiWrapper( $route, $params, $method = 'GET'){

        if(empty($route)) return false;

        $full_endpoint = $this->endpoint . $this->endpointRouters[$route]['endpoint'];

        if(is_array($params)){
            foreach($params as $key => $param){
                $full_endpoint = str_replace($key, $param, $full_endpoint);
            }
        }

        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->accessToken,
            ),
            'timeout' => 25
        );

        if($method === 'GET'){
            $response = wp_remote_get(
                $full_endpoint,
                $args
            );
        }
        else{
            $response = wp_remote_post(
                $full_endpoint,
                $args
            );
        }


        // Response body.
        $body = wp_remote_retrieve_body($response);

        return (!is_wp_error($response)) ? json_decode($body, true) : null;
    }

    private function get_accessToken()
    {

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Pragma' => ' no-cache',
                'Cache-Control' => ' no-store,no-cache',
                'Content-Type' => ' application/x-www-form-urlencoded; charset=utf-8',
            ),
            'body' => array(
                'grant_type' => 'client_credentials'
            )
        );

        $response = wp_remote_post(
            $this->endpoint . 'oauth/2.0/token',
            $args
        );

        // Response body.
        $body = wp_remote_retrieve_body($response);

        $parseData = (!is_wp_error($response)) ? json_decode($body, true) : null;

        // Set the access token
        if (!empty($parseData['access_token'])) {
            set_transient('ipro_access_token', $parseData['access_token'], $parseData['expires_in']);
        }

    }

    private function fetchRecentUpdates()
    {
        $currentTime = time();
        $lastUpdatedTime = get_option('ipro_last_updated_time');

        if(!empty($lastUpdatedTime)){
            return $this->apiWrapper('lastUpdated', array(
                'time' => round(($currentTime - $lastUpdatedTime) / 60) + 1
            ));
        }
    }

    private function fetchAllProperties(){
        $properties = $this->apiWrapper('properties', array());

        if(isset($properties['Success']) && isset($properties['message'])) {
            $response = array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( $properties['Message'], 'es' ),
                    'error'
                ),
            );
        }
        else if (is_array($properties) && count($properties) > 0) {
            $propertyIDs = array_column($properties, 'Id');

            $response = array(
                'status' => 'success',
                'message' => $propertyIDs,
            );
        }
        else{
            $response = array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( "Did you configure Ipro API Endpoint exactly? please check it again and try later.", 'es' ),
                    'error'
                ),
            );
        }

        return $response;
    }

    public function setupPropertyDetail($propertyIDs = array ()){
        try{
            if(!is_array($propertyIDs)) throw new Exception('Invalid IDs!');
            error_log(print_r($propertyIDs, 1));

            foreach ($propertyIDs as $ID){
                $propertyDetail = $this->apiWrapper('property', array(
                    'propertyId' => $ID
                ));

                error_log(print_r($propertyDetail['Id'], 1));
            }

            return array(
                'status' => 'success',
                'message' => 5,
            );
        }
        catch (Exception $exception){
            return array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( $exception->getMessage(), 'es' ),
                    'error'
                ),
            );
        }
    }


    public function ajax_load_properties(){
        if ( check_ajax_referer( 'es_save_settings', '_wpnonce', false ) ) {

            if ( current_user_can( 'manage_options' ) ) {

                switch ($_POST['step']) {
                    case 'progress':
                        $response = $this->setupPropertyDetail($_POST['IDs']);
                        break;
                    case 'init':
                        $currentTime = time();
                        update_option('ipro_last_updated_time', $currentTime);
                        $response  = $this->fetchAllProperties();
                        break;
                    default:
                        $response = array(
                            'status' => 'error',
                            'message' => es_get_notification_markup(
                                __( "Action is not defined!", 'es' ),
                                'error'
                            ),
                        );
                }

            } else {
                $response = array(
                    'status' => 'error',
                    'message' => es_get_notification_markup(
                        __( "You don't have permissions for this action.", 'es' ),
                        'error'
                    ),
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( "Authentication failed! Invalid request.", 'es' ),
                    'error'
                ),
            );
        }

        wp_die( json_encode( $response ) );
    }



}

$cronJob = new Es_Cron_Job();
$cronJob->init();
