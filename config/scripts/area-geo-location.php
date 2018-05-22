<?php //-->
ini_set('memory_limit', '-1');
header('Content-Type: charset=utf-8');
include('../../bootstrap.php');

use Cradle\Sql\SqlFactory;
use Cradle\Curl\CurlHandler as Curl;

$config = include('../services.php');
if (!isset($config['sql-main'])) {
    echo '------------------------' . PHP_EOL;
    echo '---- CONFIG NOT SET ----' . PHP_EOL;
    echo '------------------------' . PHP_EOL;
    exit;
}

$host = 'mysql:host=localhost;dbname=' . $config['sql-main']['name'];
$db = SqlFactory::load(new PDO(
    $host,
    $config['sql-main']['user'],
    $config['sql-main']['pass'])
);

cradle()
    ->error(function($request, $response, $error) {
        echo $error->getMessage();
    })
    ->preprocess(function($request, $response) use ($db) {
        // Gets the total 
        $total = $db
            ->search('area')
            ->setColumns('area_id', 'area_name')
            ->addSort('area_created', 'ASC')
            ->getTotal();

        // Sets the defaults
        $range = 100;
        $pages = ceil($total / $range);
        $geoLocations = array();

        // Batch fetching
        for ($start = 1; $start <= $pages; $start++) { 
            // Gets the areas
            $areas = $db
                ->search('area')
                ->setColumns('area_id', 'area_name')
                ->setRange($range)
                ->setPage($start)
                ->addSort('area_created', 'ASC')
                ->getRows();

            // Loops through the areas
            foreach ($areas as $key => $area) {
                // Trims the post
                $area['area_name'] = ucwords(trim($area['area_name']));

                // Checks if the geo location for the post_location already exists
                if (isset($geoLocations[$area['area_name']])) {
                    $locations = $geoLocations[$area['area_name']];
                } else {
                    // We don't have this location geo mapped yet
                    $locations = geomap($area['area_name']);

                    // Save for future reference
                    // $geoLocations[$area['area_name']] = $locations;

                    echo '------------------------' . PHP_EOL;
                    echo '----New GEO Location----' . PHP_EOL;
                    echo '------------------------' . PHP_EOL;
                }

                // Checks if there is no lat and lng returned
                if (empty($locations)) {
                    // Skip this area
                    echo '------------------------' . PHP_EOL;
                    echo '----Skipping Area #' . $area['area_id'] . PHP_EOL;
                    echo '------------------------' . PHP_EOL;
                    continue;
                }

                $area['area_location'] = $locations['lat'] . ' ' . $locations['lon'];
                $query = "UPDATE `area` SET `area_location`=ST_GeomFromText('POINT("
                    .$area['area_location'].")') WHERE `area_id` = ".$area['area_id'];

                $db->query($query);

                echo '------------------------' . PHP_EOL;
                echo '----Updated Area #' . $area['area_id'] . PHP_EOL;
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