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

$resume = SqlFactory::load(new PDO('mysql:host=192.168.140.157;dbname=resume', 'root', 'all I need is 1 mic!.'));
$db = SqlFactory::load(new PDO('mysql:host=192.168.140.157;dbname=jobayan', 'root', 'all I need is 1 mic!.'));

cradle()
    ->error(function($request, $response, $error) {
        echo $error->getMessage();
    })
    ->preprocess(function($request, $response) use ($db, $resume) {

        $config = include('../services.php');

        $resumes =  $resume
            ->search('applicant')
            ->getRows();

        foreach ($resumes as $key => $value) {
            $min = strtotime('2017-03-01 00:00:00');
            $max = strtotime('2017-07-01 00:00:00');

            $val = rand($min, $max);

            $created = date('Y-m-d H:i:s', $val);

            $profile = [
                'profile_name' => $value['applicant_name'],
                'profile_email' => $value['applicant_email'],
                'profile_phone' => $value['applicant_contact'],
                'profile_created' => $created,
                'profile_updated' => $created,
            ];

            $profileExists = $db
                ->search('profile')
                ->setColumns('profile_id')
                ->filterByProfileEmail($value['applicant_email'])
                ->getRow();

            if($profileExists) {
                continue;
            }

            echo 'APPLICANT ID '.$value['applicant_id'].'___________________________________' . PHP_EOL;

            //get image from google search
            $image = getImage($profile['profile_name']);
            if (!isset($image[0])) {
                //try again
                $image = getImage($profile['profile_name']);

                if (!isset($image[0])) {
                    echo '___________________________________' . PHP_EOL;
                    print_r('Image not found! ' . $profile['profile_name'] . ' logo');
                    echo '___________________________________' . PHP_EOL;
                    continue;
                }
            }

            $image = $image[0]['url'];

            //profile image
            if (trim($image)) {
                //is it base 64 ?
                if (strpos($image, 'data:') === 0) {
                    $profile['profile_image'] = File::base64ToS3($image, $config['s3-main']);
                } else {
                    $profile['profile_image'] = File::linkToS3($image, $config['s3-main']);
                }
            }

            
            
            $profileId = $db->insertRow('profile', $profile)->getLastInsertedId();
            echo "Inserting Profile...". $profileId ."\n";


            $val = rand($min, $max);

            $expireDate = date('Y-m-d H:i:s', $val);
            $post = [
                'post_name' => $value['applicant_name'],
                'post_email' => $value['applicant_email'],
                'post_position' => ucwords($value['applicant_job']),
                'post_location' => 'Metro Manila',
                'post_phone' => $value['applicant_contact'],
                'post_expires' => $expireDate,
                'post_created' => $created,
                'post_updated' => $created,
            ];

            $postId = $db->insertRow('post', $post)->getLastInsertedId();
            echo "Inserting Post...". $postId ."\n";

            $postProfile = [
                'profile_id' => $profileId,
                'post_id' => $postId
            ];

            $db->insertRow('post_profile', $postProfile);
        }
    })
    //prepare will call the preprocssors
    ->prepare();


function getImage($q)
{
    
    //convert browser readle format
    $keyword = urlencode($q . ' linkedin philippines');

    $length = strlen($keyword);
    //the api has a 50 char limit on query
    if ($length >= 50) {
        $keyword = substr($keyword, 0, 49);
    }

    echo '_____________________' . PHP_EOL;
    echo 'Searching for keyword: ' . $keyword . PHP_EOL;
    echo '_____________________' . PHP_EOL;

     $result = Curl::i()
        ->setUrl('http://api.ababeen.com/api/images.php?q='. $keyword .'&count=1')
        ->setReturnTransfer(1)
        ->setBinaryTransfer(1)
        ->getJsonResponse();

    if (isset($result['error'])) {
        echo "<pre>"; print_r($result); die(' ' . uniqid());
    }

    sleep(1);
    return $result;
}