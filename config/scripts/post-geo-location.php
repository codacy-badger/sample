<?php //-->
ini_set('memory_limit', '-1');
header('Content-Type: charset=utf-8');
include('../../bootstrap.php');

use Cradle\Sql\SqlFactory;
use Cradle\Module\Commerce\Store\Service as StoreService;
use Cradle\Module\Utility\File;
use Cradle\Curl\CurlHandler as Curl;
use Cradle\Module\Utility\Validator as UtilityValidator;

$db = SqlFactory::load(new PDO('mysql:host=localhost;dbname=jobayan_s', 'phpmyadmin', 'root'));

cradle()
    ->error(function($request, $response, $error) {
        echo $error->getMessage();
    })
    ->preprocess(function($request, $response) use ($db) {
        // Getes the config
        $config = include('../services.php');

        // Gets the total 
        $total = $db
            ->search('post')
            ->setColumns('post_id', 'post_location')
            ->addSort('post_created', 'DESC')
            ->getTotal();

        // Sets the defaults
        $range = 1000;
        $pages = ceil($total / $range);
        $geoLocations = array();

        // Batch fetching
        for ($start = 1; $start <= $pages; $start++) { 
            // Gets the posts
            $posts = $db
                ->search('post')
                ->setColumns('post_id', 'post_location')
                ->setRange($range)
                ->setPage($start)
                ->addSort('post_created', 'DESC')
                ->getRows();

            // Loops through the posts
            foreach ($posts as $key => $post) {
                // Trims the post
                $post['post_location'] = ucwords(trim($post['post_location']));

                // Checks if the geo location for the post_location already exists
                if (isset($geoLocations[$post['post_location']])) {
                    $locations = $geoLocations[$post['post_location']];
                } else {
                    // We don't have this location geo mapped yet
                    $locations = geomap($post['post_location']);

                    // Save for future reference
                    $geoLocations[$post['post_location']] = $locations;

                    echo '------------------------' . PHP_EOL;
                    echo '----New GEO Location----' . PHP_EOL;
                    echo '------------------------' . PHP_EOL;
                }

                $post['post_geo_location'] = json_encode($locations, true);
                $post = $db->model($post)
                    ->update('post')
                    ->get();

                echo '------------------------' . PHP_EOL;
                echo '----Updated Post #' . $post['post_id'] . PHP_EOL;
                echo '------------------------' . PHP_EOL;
            }
        }

    })
    //prepare will call the preprocssors
    ->prepare();


function geomap($location)
{
    // Gets the services config
    $config = include('../services.php');

    // Checks for google config
    if (!isset($config['google'])) {
        return [];
    }

    // Gets the google config
    $config = $config['google'];

    // Gets the settings
    $settings = include('../settings.php');

    // Default country_code
    $ccode = 'PH';

    // Checks if there is a country_code set
    if (isset($settings['country_code'])) {
        $ccode = $settings['country_code'];
    }

    // Sets the param
    $param = array(
        'region'  => $ccode,
        'address' => $location,
        'key'     => $config['secret']
    );

    // Constructs the url
    foreach ($param as $index => $value) {
        $url[] = $index . '=' . rawurlencode($value);
    }

    $url = $config['endpoint'] . '/json?' . implode('&', $url);
    
    // Gets the results
    $result = Curl::i()
            ->setUrl($url)
            ->setReturnTransfer(1)
            ->getJsonResponse();

    cradle()->inspect($result);

    // Checks for results
    if (!empty($result['results'])) {
        // Checks if the location is set
        if ($result['results'][0]['geometry']['location']) {
            $location = $result['results'][0]['geometry']['location'];
            $location = array(
                'lat' => $location['lat'],
                'lon' => $location['lng']
            );

            // Returns the nearest location found based on name of city
            return $location;
        }
    }

    return [];
}