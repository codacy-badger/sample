<?php //-->
ini_set('memory_limit', '-1');
header('Content-Type: charset=utf-8');
include('../../bootstrap.php');

$settings = include('../services.php');
use Cradle\Sql\SqlFactory;
use Cradle\Module\Commerce\Store\Service as StoreService;
use Cradle\Module\Utility\File;
use Cradle\Curl\CurlHandler as Curl;
use Cradle\Module\Utility\Validator as UtilityValidator;

$db = SqlFactory::load(new PDO('mysql:host=localhost;dbname=jobayan_stage', 'root', 'root'));

cradle()
    ->error(function($request, $response, $error) {
        echo $error->getMessage();
    })
    ->preprocess(function($request, $response) use ($db) {

        $config = include('../services.php');

        $posts =  $db
            ->search('post')
            ->setColumns('post_name', 'post_position')
            ->addSort('post_created', 'DESC')
            ->getRows();

        foreach ($posts as $key => $value) {
            sleep(1);
            echo $value['post_position']. PHP_EOL;
            $desc = crawl_page("https://www.payscale.com/research/PH/Job=". $value['post_position']."/Salary", 2);

            if ($desc) {
                cradle()->inspect($desc);exit;
            }
        }
    })
    //prepare will call the preprocssors
    ->prepare();


function crawl_page($url, $depth = 5)
{
    static $seen = array();
    if (isset($seen[$url]) || $depth === 0) {
        return;
    }

    $seen[$url] = true;

    $dom = new DOMDocument('1.0');
    @$dom->loadHTMLFile($url);

    if(isset($dom->getElementById('abstractMoreDiv')->parentNode)) {
        $desc = $dom->getElementById('abstractMoreDiv')->parentNode;

        return $desc;
    } else {
        return;
    }
    
}
