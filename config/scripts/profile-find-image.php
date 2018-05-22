<?php
/**
 * Reads and Parse Companies to profile
 */

include(__DIR__.'/repo/bootstrap.php');

use PDO as Resource;
use Cradle\Sql\SqlFactory;
use Cradle\Module\Profile\Validator as ProfileValidator;
use Cradle\Module\Profile\Validator as PostValidator;
use Cradle\Module\Utility\File;

//set file to read
$file = __DIR__ . '/egentics-emails.csv';
//set config
$config = include(__DIR__ . '/repo/config/services.php');
//mysql connection
$connection = new Resource('mysql:host=127.0.0.1;dbname=jobayan', 'root', '');
// $connection = new Resource('mysql:host=192.168.140.157;dbname=jobayan', 'root', 'all I need is 1 mic!.');
$resource = SqlFactory::load($connection);

// check file if exists
if (!file_exists($file)) {
    echo $file . ' files does not exists!' . PHP_EOL;
    die;
}

//open file
$readStream = fopen($file, 'r');

$row = 0;
//read until end of file.
while (!feof($readStream)) {
    $data = fgetcsv($readStream);
    if((++$row) === 1) {
        continue;
    }

    //invalid profile email
    if (!isset($data[3]) || empty($data[3])) {
        echo '_____________________' . PHP_EOL;
        echo 'Invalid Profile. Email' . PHP_EOL;
        echo 'skipping ROW: ' . $row . PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    //invalid last name
    if (!isset($data[2]) || empty($data[2])) {
        echo '_____________________' . PHP_EOL;
        echo 'Invalid Last name' . PHP_EOL;
        echo 'skipping ROW: ' . $row . PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    //invalid first name
    if (!isset($data[1]) || empty($data[1])) {
        echo '_____________________' . PHP_EOL;
        echo 'Invalid First name' . PHP_EOL;
        echo 'skipping ROW: ' . $row . PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    //invalid place
    if (!isset($data[6]) || empty($data[6])) {
        echo '_____________________' . PHP_EOL;
        echo 'Invalid Place' . PHP_EOL;
        echo 'skipping ROW: ' . $row . PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    $name =  utf8_encode(trim($data[1] . ' ' .$data[2]));
    $email = utf8_encode(strtolower($data[3]));
    $city = utf8_encode(strtolower($data[6]));

    $search = $resource->search('profile');
    $search->filterByProfileEmail($email);
    $results = $search->getRow();

    //check company email if match. csv vs DB
    if (strtolower($results['profile_email']) === strtolower($email)) {
        echo '_____________________' . PHP_EOL;
        echo 'profile_id: ' . $results['profile_id'] . ' email exists.' . PHP_EOL;
        echo 'skipping ROW: ' . $row . ' ' . $email  .  PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    //insert csv row into profile
    $profile = [
        'profile_name' => $name ,
        'profile_type' => 'guest' ,
        'profile_email' => $email,
        'profile_flag' => 9,
    ];

    //set gender
    if (isset($data[5]) && !empty($data[5])) {
        $profile['profile_gender'] = utf8_encode(strtolower($data[5]));
    }

    //check errors
    $errors = ProfileValidator::getCreateErrors($profile);

    //if there are errors
    if (!empty($errors)) {
        echo '_____________________' . PHP_EOL;
        echo 'Invalid Parameters ROW: ' . $row . PHP_EOL;
        print_r($errors);
        echo 'skipping ROW: ' . $row  . PHP_EOL;
        print_r($profile) . PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    //get image from google search
    $image = getImage($profile['profile_name']);
    if (!isset($image[0])) {
        //try again
        $image = getImage($profile['profile_name']);

        if (!isset($image[0])) {
            echo '__________________' . $row . '__________________' . PHP_EOL;
            print_r('Image not found! ' . $profile['profile_name'] . ' logo');
            echo '__________________' . $row . '__________________' . PHP_EOL;
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

    $profile = $resource->model($profile)
        ->setProfileCreated(date('Y-m-d H:i:s'))
        ->setProfileUpdated(date('Y-m-d H:i:s'))
        ->save('profile')
        ->get();

    //success inserting the email
    echo '_____________________' . PHP_EOL;
    echo 'Inserted with ROW: ' . $row . ' ' . $name .  PHP_EOL;
    echo '_____________________' . PHP_EOL;
    print_r($profile);

    //list of generic jobs
    $jobs = [
      'Encoder', 'Customer Representative', 'Fresh Graduate', 'Sales Manager'
    ];

    //shuffle jobs
    $rand_keys = array_rand($jobs, 1);

    $post = [
        'profile_name' => $name,
        'post_name' => $name,
        'post_email' => $email,
        'post_position' => $jobs[$rand_keys],
        'post_notify' => '["matches", "likes"]',
        'post_location' => ucwords($city),
        'post_type' => 'seeker',
        'post_expires' => '2017-06-15 08:49:35',
        'post_flag' => 9
    ];

    //validate
    $posterrors = PostValidator::getCreateErrors($post);

    //if there are errors
    if (!empty($posterrors)) {
        echo '_________POST ERROR____________' . PHP_EOL;
        echo 'Invalid Parameters ROW: ' . $row . PHP_EOL;
        print_r($posterrors);
        echo 'skipping ROW: ' . $row  . PHP_EOL;
        print_r($post) . PHP_EOL;
        echo '_____________________' . PHP_EOL;
        continue;
    }

    $post = $resource->model($post)
        ->setPostCreated(date('Y-m-d H:i:s'))
        ->setPostUpdated(date('Y-m-d H:i:s'))
        ->save('post')
        ->get();

    //success inserting the email
    echo '_____________________' . PHP_EOL;
    echo 'Inserted with ROW: ' . $row . ' ' . $name .  PHP_EOL;
    echo '_____________________' . PHP_EOL;
    print_r($post);

    //link profile - post
    if(isset($post['post_id'], $profile['profile_id'])) {
        $link = $resource
            ->model()
            ->setPostId($post['post_id'])
            ->setProfileId($profile['profile_id'])
            ->insert('post_profile')
            ->get();
        echo '____________LINKED____________' . PHP_EOL;
        echo '______' .  $post['post_id'] . ' + ' . $profile['profile_id'] . '______' . PHP_EOL;
        echo PHP_EOL;
        echo PHP_EOL;
    }
}

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

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://api.ababeen.com/api/images.php?q='. $keyword .'&count=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $result = curl_exec($ch);

    curl_close($ch);
    $result = json_decode($result, true);

    if (isset($result['error'])) {
        echo "<pre>"; print_r($result); die(' ' . uniqid());
    }

    return $result;
}