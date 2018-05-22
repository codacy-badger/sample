<?php //-->
use Cradle\I18n\Timezone;

return function ($request, $response) {
    //get handlebars
    $handlebars = $this->package('global')->handlebars();

    //add cache folder
    //$handlebars->setCache(__DIR__.'/../compiled');

    $handlebars->registerHelper('strip', function ($html, $tags, $options) {
        if (!is_string($tags)) {
            $tags = '<p><b><em><i><strong><b><br><u><ul><li><ol>';
        }

        $html = preg_replace('/\<[\/]{0,1}div[^\>]*\>/i', '', $html);



        $html = strip_tags($html, $tags);

        return $html;

        //let's unleash this logic only when either we want to monetize or if it becomes a problem
        $replacement = ' <em style="color:#F99462;font-size:13px;font-weight:normal;">( Clicked interested below )</em> ';

        //replace all emails
        //Another way
        $html = preg_replace('#[\w\d\-\_\.]+@[\w\d\-\_\.]{6,20}#i', $replacement, $html);

        //replace all URLs
        $html = preg_replace('/(http|https|ftp):\/\/([A-Z0-9][A-Z0'.
        '-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?\/?/i', $replacement, $html);

        //replace all phone
        $html = preg_replace('#[\d\s\.\-\(\)]{9,30}#', $replacement, $html);

        return $html;
    });

    $handlebars->registerHelper('sort', function ($name, $options) {
        $value = null;
        if (isset($_GET['order'][$name])) {
            $value = $_GET['order'][$name];
        }

        return $options['fn'](['value' => $value]);
    });

    $handlebars->registerHelper('toquery', function ($key = null, $value = '') {
        $query = $_GET;

        if (is_scalar($key) && !is_null($key)) {
            $query[$key] = $value;
            $query = http_build_query($query);
            parse_str(urldecode($query), $query);
        }

        if (isset($query['start'])) {
            unset($query['start']);
        }

        return http_build_query($query);
    });

    $handlebars->registerHelper('random', function ($options) {
        $messages = func_get_args();
        $options = array_pop($messages);
        $version = floor(rand() % count($messages));
        return cradle('global')->translate($messages[$version]);
    });

    $handlebars->registerHelper('inspect', function ($mixed) {
        return var_export($mixed, true);
    });

    $handlebars->registerHelper('char_length', function ($value, $length) {
        return strlen($value, $length);
    });

    $handlebars->registerHelper('word_length', function ($value, $length) {
        if (str_word_count($value, 0) > $length) {
            $words = str_word_count($value, 2);
            $position = array_keys($words);
            $value = substr($value, 0, $position[$length]);
        }

        return $value;
    });

    $handlebars->registerHelper('word_count', function ($value) {
        return str_word_count(strip_tags($value));
    });

    $handlebars->registerHelper('toupper', function ($value) {
        return strtoupper($value);
    });

    $handlebars->registerHelper('tolower', function ($value) {
        return strtolower($value);
    });

    $handlebars->registerHelper('default_avatar', function ($value, $options) {
        if (strpos($value, 'default-avatar') !== false) {
            return $options['fn']();
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('utf8_encode', function ($value) {
        return utf8_encode($value);
    });

    $handlebars->registerHelper('is_expired', function ($expires, $options) {
        // check post expiration
        if ((strtotime($expires) - time()) < 0) {
            return $options['inverse']();
        }

        return $options['fn']();
    });

    $handlebars->registerHelper('highlight', function ($created, $updated, $expires, $options) {
        //if less than 24
        if ((time() - strtotime($updated)) < (24*60*60)) {
            $settings = cradle('global')->config('settings');
            $timezone = new Timezone($settings['server_timezone'], $updated);
            $offset = $timezone->getOffset();
            return $timezone->toRelative(time() - $offset);
        }

        //if less than 7 days
        if ((time() - strtotime($updated)) < (7*24*60*60)) {
            return 'New';
        }

        // if already expired
        if ((strtotime($expires) - time()) < 0) {
            return 'Expired';
        }

        // if expires within 7 days
        if ((strtotime($expires) - time()) < (7*24*60*60)) {
            return 'Expiring Soon';
        }

        return '';
    });

    $handlebars->registerHelper('date_modify', function ($modify) {
        $today = strtotime('now');

        // Checks the modifier
        if ($modify == 'now') {
            return date('Y-m-d', $today);
        } else {
            $return = strtotime($modify, $today);
            return date('Y-m-d', $return);
        }
    });

    $handlebars->registerHelper('date_format', function ($date, $format) {
        if ($date == 'now') {
            return date($format, strtotime($date));
        }

        $date = new DateTime($date);
        return $date->format($format);
    });

    $handlebars->registerHelper('time_format', function ($time, $format = 'g:i a') {
        return date($format, strtotime($time));
    });

    $handlebars->registerHelper('number_format', function ($value, $decimal = 0) {
        return number_format($value, $decimal);
    });

    $handlebars->registerHelper('escape_quotes', function ($value) {
        return str_replace('"', '\\"', strip_tags($value));
    });

    $handlebars->registerHelper('sitemappager', function ($total, $range, $options) {
        if ($range == 0) {
            return '';
        }

        $start = 0;

        if (isset($_GET['start']) && is_numeric($_GET['start'])) {
            $start = $_GET['start'];
        }

        $pages     = ceil($total / $range);
        $page     = floor($start / $range) + 1;


        //if no pages
        if ($pages <= 1) {
            return $options['inverse']();
        }

        $buffer = array();

        for ($i = 1; $i <= $pages; $i++) {
            $_GET['start'] = ($i -1) * $range;

            $buffer[] = $options['fn'](array(
                'href'    => http_build_query($_GET),
                'active'  => $i == $page,
                'page'    => $i
            ));
        }

        return implode('', $buffer);
    });

    $handlebars->registerHelper('querystring', function ($key = null, $value = '') {
        $query = $_GET;
        $order = [];
        $filter = [];

        if ((
                is_scalar($key)
                && !is_null($key)
                && isset($query[$key])
            )
            || (
                is_string($key)
                && strpos($key, 'order') !== false
            )
            || (
                is_string($key)
                && strpos($key, 'filter') !== false
            )
        ) {
            // if (is_string($key) && strpos($key, 'order') !== false) {
            //     $order = $query['order'];
            //     $query['order'] = [];
            // }
            //
            // if (is_string($key) && strpos($key, 'filter') !== false) {
            //     $filter = $query['filter'];
            //     $query['filter'] = [];
            // }

            $query[$key] = $value;

            $query = http_build_query($query);
            parse_str(urldecode($query), $query);
        }

        return http_build_query($query);
    });

    $handlebars->registerHelper('ucwords', function ($value) {
        return ucwords($value);
    });

    $handlebars->registerHelper('is_array_key', function ($key, $array, $options) {
        if (isset($array[$key]) !== false) {
            return $options['fn']();
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('recruiters_applicants', function ($recruiters, $applicants, $options) {
        if (!is_numeric($recruiters) || !is_numeric($applicants)) {
            return;
        }

        $total = $recruiters + $applicants;

        return number_format((float) $total, 0);
    });

    $handlebars->registerHelper('_', function ($key) {
        $args = func_get_args();
        $key = array_shift($args);
        $options = array_pop($args);

        $more = preg_split('/\s__\s/is', $options['fn']());
        foreach ($more as $arg) {
            $args[] = $arg;
        }

        foreach ($args as $i => $arg) {
            if (is_null($arg)) {
                $args[$i] = '';
            }
        }

        return cradle('global')->translate((string) $key, $args);
    });

    $handlebars->registerHelper('mod', function ($key, $mod, $value, $options) {
        if (($key % $mod) == $value) {
            return $options['fn']();
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('append_s', function ($word, $value) {
        if ($value > 1) {
            return $word .= 's';
        }

        return $word;
    });

    $handlebars->registerHelper('ellipsis', function ($word, $length) {
        // Checks if we need to trim this and add ellipsis
        if (strlen($word) > $length) {
            return substr($word, 0, $length).'...';
        } else {
            // Assume that we don't need to trim this
            return $word;
        }
    });

    $handlebars->registerHelper('a_an_article', function ($word) {
        $article = 'a';
        if (preg_match('/^[aeiou]/i', $word)) {
            $article = 'an';
        }
        return $article;
    });

    $handlebars->registerHelper('array_to_str', function ($value) {
        return implode(' ', $value);
    });

    $handlebars->registerHelper('desc', function ($string, $limit) {
        $string = strip_tags($string);

        if (strlen($string) > $limit) {
            // truncate string
            $stringCut = substr($string, 0, $limit);

            // make sure it ends in a word so assassinate doesn't become ass...
            $string = substr($stringCut, 0, strrpos($stringCut, ' ')).'... ';
        }

        return $string;
    });

    $handlebars->registerHelper('substr_desc', function (
        $text,
        $length = 100,
        $ending = '...',
        $exact = true,
        $considerHtml = false
    ) {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';

            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                // if it’s an “empty element” with or without xhtml-conform closing slash (f.e.)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag (f.e.)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag (f.e. )
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                           array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                // add html-tag to $truncate’d text
                    $truncate .= $line_matchings[1];
                }

           // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1]+1-$entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }

                    $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                    // maximum lenght is reached, so get off the loop
                     break;
                } else {
                      $truncate .= $line_matchings[2];
                      $total_length += $content_length;
                }

            // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }
        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                   // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }

        // add the defined ending to the text
        $truncate .= $ending;
        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '';
            }
        }

        return $truncate;
    });

    $handlebars->registerHelper('rank', function ($experience) {
        $getExperience = function ($l) {
            $xp = 0;
            for ($i = 0; $i < $l; $i++) {
                $xp += floor($i + 300 * pow(2, $i / 7));
            }
            return floor($xp / 4);
        };
        $level = 1;
        while ($getExperience($level) < $experience) {
            $level++;
        }
        switch (true) {
            case $level < 5:
                return cradle()->package('global')->translate('Just Starting');
            case $level < 10:
                return cradle()->package('global')->translate('New Recruiter');
            case $level < 20:
                return cradle()->package('global')->translate('Junior Recruiter');
            case $level < 30:
                return cradle()->package('global')->translate('Active Recruiter');
            case $level < 40:
                return cradle()->package('global')->translate('Ninja Recruiter');
            case $level < 50:
                return cradle()->package('global')->translate('Samurai Recruiter');
            case $level < 60:
                return cradle()->package('global')->translate('Rockstar Company');
            case $level < 70:
                return cradle()->package('global')->translate('VIP Company');
            case $level < 80:
                return cradle()->package('global')->translate('Grand Company');
            case $level < 90:
                return cradle()->package('global')->translate('Super Company');
            case $level < 100:
                return cradle()->package('global')->translate('Epic Company');
            case $level < 120:
                return cradle()->package('global')->translate('Champion Company');
            case $level < 140:
                return cradle()->package('global')->translate('Master Company');
            case $level < 160:
                return cradle()->package('global')->translate('Legendary Company');
            case $level < 180:
                return cradle()->package('global')->translate('Godly Company');
            default:
                return cradle()->package('global')->translate('Chuck Noris');
        }
    });
    $handlebars->registerHelper('next_rank', function ($experience) {
        $getExperience = function ($l) {
            $xp = 0;
            for ($i = 0; $i < $l; $i++) {
                $xp += floor($i + 300 * pow(2, $i / 7));
            }
            return floor($xp / 4);
        };
        $level = 1;
        while ($getExperience($level) < $experience) {
            $level++;
        }

        switch (true) {
            case $level < 5:
                return cradle()->package('global')->translate('New Recruiter');
            case $level < 10:
                return cradle()->package('global')->translate('Junior Recruiter');
            case $level < 20:
                return cradle()->package('global')->translate('Active Recruiter');
            case $level < 30:
                return cradle()->package('global')->translate('Ninja Recruiter');
            case $level < 40:
                return cradle()->package('global')->translate('Samurai Recruiter');
            case $level < 50:
                return cradle()->package('global')->translate('Rockstar Company');
            case $level < 60:
                return cradle()->package('global')->translate('VIP Company');
            case $level < 70:
                return cradle()->package('global')->translate('Grand Company');
            case $level < 80:
                return cradle()->package('global')->translate('Super Company');
            case $level < 90:
                return cradle()->package('global')->translate('Epic Company');
            case $level < 100:
                return cradle()->package('global')->translate('Champion Company');
            case $level < 120:
                return cradle()->package('global')->translate('Master Company');
            case $level < 140:
                return cradle()->package('global')->translate('Legendary Company');
            case $level < 160:
                return cradle()->package('global')->translate('Godly Company');
            case $level < 180:
                return cradle()->package('global')->translate('Chuck Noris');
            default:
                return cradle()->package('global')->translate('Chuck Noris II');
        }
    });

    $handlebars->registerHelper('level', function ($experience) {
        $getExperience = function ($l) {
            $xp = 0;
            for ($i = 0; $i < $l; $i++) {
                $xp += floor($i + 300 * pow(2, $i / 7));
            }
            return floor($xp / 4);
        };
        $level = 1;
        while ($getExperience($level) < $experience) {
            $level++;
        }
        return $level;
    });
    $handlebars->registerHelper('next_level', function ($experience) {
        $getExperience = function ($l) {
            $xp = 0;
            for ($i = 0; $i < $l; $i++) {
                $xp += floor($i + 300 * pow(2, $i / 7));
            }
            return floor($xp / 4);
        };
        $level = 1;
        while ($getExperience($level) < $experience) {
            $level++;
        }
        return $level + 1;
    });
    $handlebars->registerHelper('experience_percent', function ($experience) {
        $getExperience = function ($l) {
            $xp = 0;
            for ($i = 0; $i < $l; $i++) {
                $xp += floor($i + 300 * pow(2, $i / 7));
            }
            return floor($xp / 4);
        };
        $level = 1;
        while ($getExperience($level) < $experience) {
            $level++;
        }
        $startExperience = $getExperience($level-1);
        $endExperience = $getExperience($level);
        return floor(($experience  / $endExperience) * 100);
    });
    $handlebars->registerHelper('level_up', function ($before, $after, $options) {
        if (!$before || !$after) {
            return '';
        }
        $getLevel = function ($xp) {
            $getExperience = function ($l) {
                $xp = 0;
                for ($i = 0; $i < $l; $i++) {
                    $xp += floor($i + 300 * pow(2, $i / 7));
                }
                return floor($xp / 4);
            };
            $level = 1;
            while ($getExperience($level) < $xp) {
                $level++;
            }
        };
        $before = $getLevel($before);
        $after = $getLevel($after);
        if ($after > $before) {
            return $options['fn']();
        }
        return '';
    });
    $handlebars->registerHelper('achievement', function ($badge, $key, $options) {
        $achievement = cradle('global')->config('achievements', $badge);
        return isset($achievement[$key]) ? $achievement[$key] : null;
    });
    $handlebars->registerHelper('sanitize_string', function ($string, $options) {
        return filter_var(html_entity_decode($string), FILTER_SANITIZE_STRING);
    });
    $handlebars->registerHelper('single_line', function ($value) {
        return preg_replace('!\s+!', '', $value);
    });
    // Default avatars
    $handlebars->registerHelper('default_avatar', function ($profile_id) {
        $settings = cradle('global')->config('settings');
        $host = $settings['host'];

        return $host.'/images/avatar/avatar-' . ($profile_id % 5) . '.png';
    });
    // Profile avatars
    $handlebars->registerHelper('profile_avatar', function ($profile_id) {
        $settings = cradle('global')->config('settings');
        $host = $settings['host'];

        return '/images/avatar/avatar-' . ($profile_id % 5) . '.png';
    });
    // Local Avatar
    $handlebars->registerHelper('local_avatar', function ($profile_id) {
        $settings = cradle('global')->config('settings');
        $host = $settings['host'];

        $path = $host . '/images/avatar/avatar-' . ($profile_id % 5) . '.png';
        $image = file_get_contents($path);
        $tempPath = tempnam(sys_get_temp_dir(), 'prefix');
        file_put_contents($tempPath, $image);

        return $tempPath;
    });
    $handlebars->registerHelper('pager_sponsored', function ($total, $range, $startSponsored, $options) {
        if ($range == 0) {
            return '';
        }

        $show = 10;
        $start = 0;

        if (isset($_GET['start']) && is_numeric($_GET['start'])) {
            $start = $_GET['start'];
        }

        $pages     = ceil($total / $range);
        $page     = floor($start / $range) + 1;

        $min     = $page - $show;
        $max     = $page + $show;

        if ($min < 1) {
            $min = 1;
        }

        if ($max > $pages) {
            $max = $pages;
        }

        //if no pages
        if ($pages <= 1) {
            return $options['inverse']();
        }

        $buffer = array();

        for ($i = $min; $i <= $max; $i++) {
            $_GET['start'] = ($i -1) * $range;
            $_GET['start_sponsored'] = ($i -1) * $startSponsored;

            $buffer[] = $options['fn'](array(
                'href'    => http_build_query($_GET),
                'active'  => $i == $page,
                'page'    => $i
            ));
        }

        return implode('', $buffer);
    });
    $handlebars->registerHelper('end', function ($value) {
        return end($value);
    });

    $handlebars->registerHelper('toucfirst', function ($value) {
        return ucwords(str_replace('-', ' ', $value));
    });

    $handlebars->registerHelper('array_pop', function ($array, $options) {
        $pop = array_pop($array);

        if ($pop) {
            return $options['fn']((array) $pop);
        }

        return $options['inverse']();
    });
    $handlebars->registerHelper('date_information', function ($value) {
        $date =  date('F Y', strtotime($value));

        if (!$value) {
            $date = cradle('global')->translate('Present');
        }

        return $date;
    });
    $handlebars->registerHelper('long_dateFormat', function ($value) {
        $date =  date('F d, Y', strtotime($value));

        if (!$value) {
            $date = cradle('global')->translate('Present');
        }

        return $date;
    });
    $handlebars->registerHelper('phone_mask', function ($value) {
        // get the length of the value
        $length = strlen($value) - 1;

        // check if value starts with 0
        if (substr($value, 0, '-'.$length) === '0') {
            $length = strlen($value) - 4;
            return substr($value, 0, '-'.$length) . ' XXXXXXXX';
        }

        // check if value starts with 9
        if (substr($value, 0, '-'.$length) === '9') {
            $length = strlen($value) - 3;
            return substr($value, 0, '-'.$length) . ' XXXXXXXX';
        }

        // mask the last 4 digit of phone
        return substr($value, 0, 4) . ' XXXXXXXX';
    });
    $handlebars->registerHelper('trim', function ($value) {
        return trim($value);
    });

    $handlebars->registerHelper('lower_dasherize', function ($value) {
        if (strcasecmp($value, 'signed up') == 0 ||
            strcasecmp($value, 'claimed a profile') == 0 ||
            strcasecmp($value, 'verified email') == 0 ||
            strcasecmp($value, 'buy credits') == 0 ||
            strcasecmp($value, 'created a post') == 0 ||
            strcasecmp($value, 'downloaded resume') == 0 ||
            strcasecmp($value, 'gained achievement') == 0 ||
            strcasecmp($value, 'gained experience') == 0 ||
            strcasecmp($value, 'uploaded profile') == 0 ||
            strcasecmp($value, 'update credentials') == 0 ||
            strcasecmp($value, 'updated a post') == 0 ||
            strcasecmp($value, 'updated email on post') == 0 ||
            strcasecmp($value, 'updated phone on post') == 0 ||
            strcasecmp($value, 'user unsubscribed') == 0 ||
            strcasecmp($value, 'user email bounced') == 0 ||
            strcasecmp($value, 'forgot password') == 0 ||
            strcasecmp($value, 'interested a post') == 0 ||
            strcasecmp($value, 'viewed a post') == 0 ||
            strcasecmp($value, 'notified for a like') == 0 ||
            strcasecmp($value, 'notified for matches') == 0 ||
            strcasecmp($value, 'notified for matched via sms') == 0
        ) {
            return strtolower(str_replace(' ', '-', $value));
        } else {
            return "default";
        }
    });

    $handlebars->registerHelper('compute_percent', function ($value1, $value2 = 100) {
        return ($value1 / $value2) * 100;
    });

    $handlebars->registerHelper('is_even', function ($number, $options) {
        if ($number % 2 == 0) {
            return $options['fn']();
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('add', function (...$args) {
        return array_sum(func_get_args($args));
    });

    $handlebars->registerHelper('count', function ($arg) {
        return count($arg);
    });

    $handlebars->registerHelper('isset', function ($array, ...$args) {
        if ($array == 'session') {
            $array = $_SESSION;
        }

        $options = array_pop($args);
        $last = array_pop($args);
        foreach ($args as $arg) {
            $array = $array[$arg];
        }

        if (isset($array[$last])) {
            return $options['fn']();
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('education', function ($array, $options) {
        if (!$array) {
            return '';
        }

        usort($array, function ($a, $b) {
            return strtotime($a['education_to']) - strtotime($b['education_to']);
        });

        if ($array) {
            return $options['fn']($array[0]);
        }

        return $options['inverse']();
    });

    $handlebars->registerHelper('number', function ($number, $options) {
        return str_replace('.00', '', number_format((float) $number, 2));
    });

    $handlebars->registerHelper('work_experience', function ($array, $options) {
        if (!$array) {
            return '';
        }

        usort($array, function ($a, $b) {
            return strtotime($a['experience_to']) - strtotime($b['experience_to']);
        });

        if ($array) {
            return $options['fn']($array[0]);
        }

        return $options['inverse']();
    });
};
