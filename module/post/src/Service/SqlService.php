<?php //-->

/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Post\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;
use HTMLPurifier;

/**
 * Post SQL Service
 *
 * @vendor   Acme
 * @package  post
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'post';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct (Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Get User Post activity Count
     *
     * @param *int profile
     * @param *string $activity
     *
     * @return bool
     */
    public function getUserPostActivity ($profile, $activity)
    {
        switch ($activity) {
            case 'interested':
                $activity = 'liked';
                break;

            case 'downloaded':
                $activity = 'downloaded';
                break;

            default:
                return 0;
                break;
        }

        return $this->resource
            ->search('post_' . $activity)
            ->filterByProfileId($profile)
            ->getTotal();
    }

    /**
     * Add like count
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function addDownload ($post, $profile)
    {
        if ($this->alreadyDownloaded($post, $profile)) {
            return false;
        }

        $this->resource
            ->model()
            ->setPostId($post)
            ->setProfileId($profile)
            ->insert('post_downloaded');

        $this->resource->query('UPDATE post SET '
            . 'post_download_count = post_download_count + 1 '
            . 'WHERE post_id=:bind0bind', [
            ':bind0bind' => $post
        ]);

        return true;
    }

    /**
     * Add Email count
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function addEmail ($post, $profile)
    {
        if ($this->alreadyEmailed($post, $profile)) {
            return false;
        }

        $this->resource
            ->model()
            ->setPostId($post)
            ->setProfileId($profile)
            ->insert('post_emailed');

        $this->resource->query('UPDATE post SET '
            . 'post_email_count = post_email_count + 1 '
            . 'WHERE post_id=:bind0bind', [
            ':bind0bind' => $post
        ]);

        return true;
    }

    /**
     * Add like count
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function addLike ($post, $profile)
    {
        if ($this->alreadyLiked($post, $profile)) {
            return false;
        }

        $this->resource
            ->model()
            ->setPostId($post)
            ->setProfileId($profile)
            ->insert('post_liked');

        $this->resource->query('UPDATE post SET '
            . 'post_like_count = post_like_count + 1 '
            . 'WHERE post_id=:bind0bind', [
            ':bind0bind' => $post
        ]);

        return true;
    }

    /**
     * Add view count
     *
     * @param *int $post
     *
     * @return bool
     */
    public function addView ($post)
    {
        $this->resource->query('UPDATE post SET '
            . 'post_view = post_view + 1 '
            . 'WHERE post_id=:bind0bind', [
            ':bind0bind' => $post
        ]);

        return true;
    }

    /**
     * Add phone count
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function addPhone ($post, $profile)
    {
        if ($this->alreadyPhoned($post, $profile)) {
            return false;
        }

        $this->resource
            ->model()
            ->setPostId($post)
            ->setProfileId($profile)
            ->insert('post_phoned');

        $this->resource->query('UPDATE post SET '
            . 'post_phone_count = post_phone_count + 1 '
            . 'WHERE post_id=:bind0bind', [
            ':bind0bind' => $post
        ]);

        return true;
    }

    /**
     * Already downloaded
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function alreadyDownloaded ($post, $profile)
    {
        return !!$this->resource
            ->search('post_downloaded')
            ->filterByPostId($post)
            ->filterByProfileId($profile)
            ->getRow();
    }

    /**
     * Already emailed
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function alreadyEmailed ($post, $profile)
    {
        return !!$this->resource
            ->search('post_emailed')
            ->filterByPostId($post)
            ->filterByProfileId($profile)
            ->getRow();
    }

    /**
     * Already liked
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function alreadyLiked ($post, $profile)
    {
        return !!$this->resource
            ->search('post_liked')
            ->filterByPostId($post)
            ->filterByProfileId($profile)
            ->getRow();
    }

    /**
     * Already phoned
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function alreadyPhoned ($post, $profile)
    {
        return !!$this->resource
            ->search('post_phoned')
            ->filterByPostId($post)
            ->filterByProfileId($profile)
            ->getRow();
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create (array $data)
    {
        // Sets the columns to be purified
        $columns = [
            'post_location',
            'post_detail'
        ];

        // Checks if there are columns to purifiy
        if (!empty($columns)) {
            // Loops through the columns to be purified
            foreach ($columns as $column) {
                // Checks if the data exists
                if (isset($data[$column])) {
                    // Strip malicious tags using HTMLPurifier
                    $purifier      = new HTMLPurifier;
                    $data[$column] = $purifier->purify($data[$column]);
                }
            }
        }

        return $this->resource
            ->model($data)
            ->setPostCreated(date('Y-m-d H:i:s'))
            ->setPostUpdated(date('Y-m-d H:i:s'))
            ->save('post')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get ($id)
    {
        $search = $this->resource->search('post');

        $search->innerJoinUsing('post_profile', 'post_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->filterByPostId($id);

        $results = $search->getRow();

        // Checks if no results were returned
        if (!$results) {
            return $results;
        }

        // Checks for post_notify
        if ($results['post_notify']) {
            $results['post_notify'] = json_decode($results['post_notify'], true);
        } else {
            $results['post_notify'] = [];
        }

        // Checks for post_tags
        if ($results['post_tags']) {
            $results['post_tags'] = json_decode($results['post_tags'], true);
        } else {
            $results['post_tags'] = [];
        }

        // Checks for post_geo_location
        if ($results['post_geo_location']) {
            $results['post_geo_location'] = json_decode($results['post_geo_location'], true);
        } else {
            $results['post_geo_location'] = [];
        }

        // Checks for post_package
        if (isset($results['post_package'])) {
            if ($results['post_package']) {
                $results['post_package'] = json_decode($results['post_package'], true);
            } else {
                $results['post_package'] = [];
            }
        }

        // Gets the likers
        $likers = $this->resource
                       ->search('post_liked')
                       ->filterByPostId($id)
                       ->getRows();

        $like = [];
        // Loops through the likers
        foreach ($likers as $key => $liker) {
            $like[$liker['profile_id']] = $liker;
        }

        $results['likers'] = $like;

        // Gets the post_likes / post_liked
        $results['post_likes'] = $this->resource
                                      ->search('post_liked')
                                      ->innerJoinUsing('profile', 'profile_id')
                                      ->filterByPostId($id)
                                      ->getRows();

        // Gets the popst_downloads / post_downloaded
        $results['post_downloads'] = $this->resource
                                          ->search('post_downloaded')
                                          ->innerJoinUsing('profile', 'profile_id')
                                          ->filterByPostId($id)
                                          ->getRows();

        //use post resume
        if(!empty($results['post_resume'])) {
            $results['resume_link'] = $results['post_resume'];
        }

        foreach ($results['post_likes'] as $key => $value) {
            //get profile resume
            $results['post_likes'][$key] ['profile_resume'] = $this->resource
                    ->search('resume')
                    ->setColumns('resume.*')
                    ->innerJoinUsing('profile_resume', 'resume_id')
                    ->filterByProfileId($value['profile_id'])
                    ->filterByResumeActive(1)
                    ->sortByResumeCreated('DESC')
                    ->setRange(1)
                    ->getRow();

            // get profile information
            $results['post_likes'][$key]['profile_information']  = $this->resource
                ->search('information')
                ->innerJoinUsing('information_profile', 'information_id')
                ->innerJoinUsing('profile', 'profile_id')
                ->filterByProfileId($value['profile_id'])
                ->setRange(1)
                ->getRow();
        }

        $like = [];
        foreach ($results['post_likes'] as $key => $liker) {
            $like[$liker['profile_id']] = $liker;
        }

        $results['likers'] = $like;

        // Checks the post_type
        if ($results['post_type'] == 'poster') {
            $results['post_url'] = '/Company-Hiring/'
                . $this->slugify($results['post_position'], $results['post_id']);
        } else {
            $results['post_url'] = '/Seeking-Job/'
                . $this->slugify($results['post_position'], $results['post_id']);
        }

        //achievements
        if ($results['profile_achievements']) {
            $results['profile_achievements'] = json_decode($results['profile_achievements'], true);
        } else {
            $results['profile_achievements'] = [];
        }

        if ($results['profile_interviewer']) {
            $results['profile_interviewer'] = json_decode($results['profile_interviewer'], true);
        } else {
            $results['profile_interviewer'] = [];
        }

        if ($results['profile_package']) {
            $results['profile_package'] = json_decode($results['profile_package'], true);
        } else {
            $results['profile_package'] = [];
        }

        //tags
        if ($results['profile_tags']) {
            $results['profile_tags'] = json_decode($results['profile_tags'], true);
        } else {
            $results['profile_tags'] = [];
        }

        //story
        if ($results['profile_story']) {
            $results['profile_story'] = json_decode($results['profile_story'], true);
        } else {
            $results['profile_story'] = [];
        }

        //campaign
        if ($results['profile_campaigns']) {
            $results['profile_campaigns'] = json_decode($results['profile_campaigns'], true);
        } else {
            $results['profile_campaigns'] = [];
        }

        return $results;
    }

    /**
     * Get post geo location
     *
     * @param string
     *
     * @return array
     */
    public function getGeoLocation ($location)
    {
        return $this->resource
            ->search('post')
            ->setColumns('post_location', 'post_geo_location')
            ->addFilter('LOWER(post_location) = "' . strtolower($location) . '"')
            ->addFilter('`post_geo_location`->"$.lat" >= 0')
            ->getRow();
    }

    /**
     * Get total openings per Location
     *
     * @param array
     *
     * @return array
     */
    public function getOpeningLocation ($data)
    {
        $search = $this->resource->search('post');

        if (empty($data['locations'])) {
            return 0;
        }

        $or      = [];
        $where   = [];
        $where[] = 'LOWER(post_position) LIKE %s';
        $or[] = '%' . strtolower($data['position']) . '%';

        $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
        $or[] = '%' . strtolower($data['position']) . '%';

        array_unshift($or, '(' . implode(' OR ', $where) . ')');
        call_user_func([$search, 'addFilter'], ...$or);

        $search = $search
            ->filterByPostActive(1)
            ->addFilter('post_location IN %s', $data['locations'])
            ->addFilter('post_expires > %s', date('Y-m-d H:i:s'));

        return [
            'total' => $search->getTotal()
        ];
    }

    /**
     * Get type total
     *
     * @param array
     *
     * @return array
     */
    public function getPostTypeTotal ($data)
    {
        $search = $this->resource->search('post');

        // if location like
        if (isset($data['location_like'])) {
            $or      = [];
            $where   = [];
            $where[] = 'LOWER(post_location) LIKE %s';
            $or[] = '%' . strtolower($data['location_like']) . '%';

            $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
            $or[] = '%' . strtolower($data['location_like']) . '%';

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
            unset($data['location_like']);
        }

        // if position like
        if (isset($data['position_like'])) {
            $or      = [];
            $where   = [];
            $where[] = 'LOWER(post_position) LIKE %s';
            $or[] = '%' . strtolower($data['position_like']) . '%';

            $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
            $or[] = '%' . strtolower($data['position_like']) . '%';

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
            unset($data['position_like']);
        }

        // set seeker as default
        $postType = 'seeker';
        if (isset($data['post_type'])
            && !empty($data['post_type'])
        ) {
            $postType = $data['post_type'];
        }

        $search = $search
            ->filterByPostActive(1)
            ->addFilter('post_type = %s', $postType)
            ->addFilter('post_expires > %s', date('Y-m-d H:i:s'));

        return [
            'total' => $search->getTotal()
        ];
    }

    /**
     * Get Post Totals from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getTotals ()
    {
        $totals = [];

        $totals['companies'] = $this->resource->search('profile')
            ->setColumns('profile_id')
            ->addFilter('profile_company IS NOT NULL')
            ->getTotal();

        $totals['applicants'] = $this->resource->search('profile')
            ->setColumns('profile_id')
            ->addFilter('(profile_company IS NULL OR profile_company = "")')
            ->getTotal();

        $totals['posts'] = $this->resource->search('post')
            ->setColumns('post_id')
            ->getTotal();

        $totals['connections'] = $this->resource->search('post_liked')
            ->setColumns('post_id')
            ->getTotal();

        return $totals;
    }


    /**
     * Get Featured Post from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getFeaturedPostBulk (array $data = [])
    {
        $filter    = [];
        $positions = $data['filter']['positions'];
        unset($data['filter']['positions']);

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        $search = $this->resource
            ->search('post')
            ->setColumns('count(post_id) as post_count, post_position')
            ->addFilter('post_expires > %s', date(
                'Y-m-d H:i:s',
                strtotime('now')
            ))
            ->filterByPostActive(1);

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' LIKE %s', '%' . addslashes($value) . '%');
            }
        }

        //post_tags
        if (isset($data['post_tags']) &&
            !empty($data['post_tags'])) {

            if (!is_array($data['post_tags'])) {
                $data['post_tags'] = [$data['post_tags']];
            }

            $or    = [];
            $where = [];

            foreach ($data['post_tags'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
                $or[]    = '%' . strtolower($tag) . '%';
            }

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        $posFilter = [];
        foreach ($positions as $position) {
            $posFilter[] = 'post_position LIKE "%' . trim(addslashes($position)) . '%"';
        }

        $search->addFilter('(' . implode(' OR ', $posFilter) . ')')
            ->groupBy('post_position');

        $rows  = $search->getRows();
        $total = 0;
        $pos   = [];
        foreach ($rows as $k => $v) {
            foreach ($positions as $position) {
                if (strpos(strtolower(trim($v['post_position'])), trim(strtolower($position))) !== false) {
                    $pos[$position] = isset($pos[$position]) && is_numeric($pos[$position]) ? $pos[$position] + $v['post_count'] : $v['post_count'];
                }

            }

            $total = $total + $v['post_count'];
        }

        return [
            'rows'  => $pos,
            'total' => $total
        ];

    }

    /**
     * Get Featured Post from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getFeaturedPost (array $data = [])
    {
        $filter = [];

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        $search = $this->resource
            ->search('post')
            ->addFilter('post_expires > %s', date(
                'Y-m-d H:i:s',
                strtotime('now')
            ))
            ->filterByPostActive(1)
            ->setStart(0)
            ->setRange(1);

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' LIKE %s', '%' . $value . '%');
            }
        }

        //post_tags
        if (isset($data['post_tags']) &&
            !empty($data['post_tags'])) {

            if (!is_array($data['post_tags'])) {
                $data['post_tags'] = [$data['post_tags']];
            }

            $or    = [];
            $where = [];

            foreach ($data['post_tags'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
                $or[]    = '%' . strtolower($tag) . '%';
            }

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        if (isset($data['post_position']) &&
            is_array($data['post_position'])) {
            $position = array_column($data['post_position'], 'position_name');
            $search->addFilter('post_position IN %s', $position);
        }

        //return response format
        return [
            'total' => $search->getTotal()
        ];
    }


    /**
     * Get Tracking Job Post from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getTrackingJobPost (array $data = [])
    {
        $filter   = [];
        $range    = 50;
        $start    = 0;
        $order    = [];
        $count    = 0;
        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];
            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        // get all post by profile id
        $search = $this->resource
            ->search('post')
            ->setStart($start)
            ->setRange($range)
            ->setColumns(
                'post_id',
                'post_active',
                'post_detail',
                'post_expires',
                'post_location',
                'post_name',
                'post_package',
                'post_position',
                'profile_id'
            )
            ->innerJoinUsing('post_profile', 'post_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->addFilter('post_expires > %s', date(
                'Y-m-d H:i:s',
                strtotime('now')
            ))
            ->filterByPostType('poster')
            ->filterByProfileId($data['profile_id']);

        // filter by post id
        if (isset($data['post_id'])) {
            $search = $search->filterByPostId($data['post_id']);
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or      = [];
                $where   = [];
                $where[] = 'LOWER(post_position) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //add sorting
        if ($order) {
            foreach ($order as $sort => $direction) {
                $search->addSort($sort, $direction);
            }
        } else {
            $search->addSort('post_id', 'DESC');
        }

        $rows      = $search->getRows();
        $rowsTotal = $search->getTotal();

        if (!$rows) {
            return [
                'rows'  => $rows,
                'total' => $rowsTotal
            ];
        }

        $postIds = [];
        $posts   = [];

        // get post ids
        foreach ($rows as $post) {
            $post['post_slug'] = $this->slugify(
                $post['post_position'],
                $post['post_id']
            );

            $post['post_package']    = json_decode($post['post_package']);
            $posts[$post['post_id']] = $post;
            $postIds[]               = $post['post_id'];
        }

        // get form by post id
        $forms = $this->resource
            ->search('form')
            ->innerJoinUsing('post_form', 'form_id')
            ->addFilter('post_id IN (' . implode(',', $postIds) . ')')
            ->filterByFormActive(1)
            ->filterByFormFlag(1)
            ->getRows();

        // merge form to post
        foreach ($forms as $form) {
            $posts[$form['post_id']] = array_merge($posts[$form['post_id']], $form);
        }

        foreach ($posts as $i => $post) {
            $posts[$i]['post_likers'] = $this->resource
                ->search('post_liked')
                ->setColumns('profile_id')
                ->innerJoinUsing('profile', 'profile_id')
                ->addFilter('(profile_company IS NULL or profile_company = "")')
                ->filterByPostId($post['post_id'])
                ->getRows();
        }

        // get applicant by post
        $search = $this->resource
            ->search('applicant')
            ->setColumns(
                'applicant_id',
                'applicant_status',
                'applicant_created',
                'form_id',
                'profile_id',
                'profile_name',
                'profile_email',
                'profile_phone',
                'profile_address_street',
                'profile_address_city',
                'profile_address_state',
                'profile_address_country',
                'profile_address_postal',
                'post_id',
                'post_position'
            )
            ->innerJoinUsing('applicant_post', 'applicant_id')
            ->innerJoinUsing('applicant_form', 'applicant_id')
            ->innerJoinUsing('applicant_profile', 'applicant_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->innerJoinUsing('post', 'post_id')
            ->addFilter('post_id IN (' . implode(',', $postIds) . ')')
            ->filterByApplicantActive(1);

        //add filters
        foreach ($filter as $column => $value) {
            //applicant status custom search
            if ($column === 'applicant_status') {
                $search->addFilter(sprintf('JSON_CONTAINS(applicant_status, \'["%s"]\')', $value));
                continue;
            }
        }

        $applicants = $search->getRows();

        // merge applicant to post
        foreach ($applicants as $applicant) {
            $profileIds[] = $applicant['profile_id'];

            if (isset($applicant['applicant_status'])) {
                $applicant['applicant_status'] = json_decode($applicant['applicant_status'], true);
            } else {
                $applicant['applicant_status'] = [];
            }

            // get resume link
            $resume = $this->resource
                ->search('resume')
                ->setColumns('resume_id', 'resume_link')
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->innerJoinUsing('post_resume', 'resume_id')
                ->filterByPostId($applicant['post_id'])
                ->filterByProfileId($applicant['profile_id'])
                ->getRow();

            // merge resume to applicant
            if ($resume) {
                $applicant = array_merge($applicant, $resume);
            }

            $posts[$applicant['post_id']]['applicant'][] = $applicant;
        }

        return [
            'rows'  => $posts,
            'total' => $rowsTotal
        ];
    }

    /**
     * Get List of profiles who
     * haven't answered a form in a application
     *
     * @param *int $id
     *
     * @return array
     */
    public function getPostSeekerToInform (array $data = [])
    {
        //first get users who interested the post
        // get all profile by post id
        $profileWhoLiked = $this->resource
            ->search('post_liked')
            ->filterByPostId($data['post_id'])
            ->getRows();

        //get profile ids
        $interestedProfiles = [];
        if (!empty($profileWhoLiked)) {
            foreach ($profileWhoLiked as $index => $interestedProfile) {
                $interestedProfiles[] = $interestedProfile['profile_id'];
            }
        }

        //second get the users who does not exists in applicant post
        $getApplicants = $this->resource
            ->search('applicant_post')
            ->innerJoinUsing('applicant_profile', 'applicant_id')
            ->innerJoinUsing('applicant_form', 'applicant_id')
            ->addFilter('profile_id')
            ->filterByPostId($data['post_id'])
            ->getRows();

        //get profile ids
        $profileApplicants = [];
        if (!empty($getApplicants)) {
            foreach ($getApplicants as $index => $applicant) {
                $profileApplicants[] = $applicant['profile_id'];
            }
        }

        //return response format
        return [
            'profileWhoLiked'     => $interestedProfiles,
            'profileHasApplicant' => $profileApplicants,
        ];
    }

    /**
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove ($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setPostId($id)
            ->remove('post');
    }

    /**
     * Get profile posts from database
     *
     * @param array
     *
     * @return array
     */
    public function getPostProfile ($data)
    {
        $search = $this->resource->search('post_profile');

        $search->filterByProfileId($data['profile_id']);

        // set start
        if (isset($data['start'])) {
            $search->setStart($data['start']);
        }

        // set range
        if (isset($data['range'])) {
            $search->setRange($data['range']);
        }

        $results = $search->getRows();

        if (!$results) {
            return $results;
        }

        return $results;
    }

    /**
     * Pulls all who liked user posts
     *
     * @param *int $profileId
     */
    public function getPostLikes (array $data = [])
    {
        $filter      = [];
        $notFilter   = [];
        $exactFilter = [];
        $range       = 50;
        $start       = 0;
        $order       = [];
        $count       = 0;
        $liked       = false;
        $likers      = false;
        $interested  = false;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['not_filter']) && is_array($data['not_filter'])) {
            $notFilter = $data['not_filter'];
        }

        if (isset($data['exact_filter']) && is_array($data['exact_filter'])) {
            $exactFilter = $data['exact_filter'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        if (isset($data['export'])) {
            $range = 0;
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (isset($data['likers']) && $data['likers']) {
            $likers = true;
        }

        if (isset($data['liked']) && $data['liked']) {
            $liked = true;
        }

        if (isset($data['interested']) && $data['interested']) {
            $interested = true;
        }

        $search = $this->resource
            ->search('post')
            ->setStart($start)
            ->setRange($range);

        //filter by post id
        if (isset($data['post_id'])
            && !empty($data['post_id'])) {
            $search = $search->filterByPostId($data['post_id']);
        }

        //join likers
        $search->innerJoinUsing('post_liked', 'post_id');
        $search->innerJoinUsing('post_profile', 'post_id');
        $search->filterByProfileActive(1);

        if ($liked) {
            // filter posts only liked by this user
            $search->addFilter('post_liked.profile_id = %d', $data['profile_id']);
            $search->innerJoinOn('profile', 'profile.profile_id = post_profile.profile_id');
        }

        if ($likers) {
            // filter posts only of the this user
            $search->addFilter('post_profile.profile_id = %d', $data['profile_id']);
            $search->innerJoinOn('profile', 'profile.profile_id = post_liked.profile_id');
        }

        if ($interested) {
            // filter posts only of the this user
            $search->innerJoinOn('profile', 'profile.profile_id = post_liked.profile_id');
        }

        // join profile of the liker
        $search->setColumns('post.*', 'profile.*');

        //post_tags
        if (isset($data['post_tags']) && !empty($data['post_tags'])) {
            if (!is_array($data['post_tags'])) {
                $data['post_tags'] = [$data['post_tags']];
            }

            foreach ($data['post_tags'] as $tag) {
                $or    = [];
                $where = [];

                $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
                $or[]    = '%' . strtolower($tag) . '%';

                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or      = [];
                $where   = [];
                $where[] = 'LOWER(post_name) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_position) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_location) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_detail) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' LIKE %s', '%' . $value . '%');
            }
        }

        // Checks if there are exact filters
        if (!empty($exactFilter)) {
            // Loops through the filters
            foreach ($exactFilter as $column => $value) {
                // Checks if the value is not empty
                if (!is_null($value)) {
                    if ($value === '""') {
                        $search->addFilter('(' . $column . ' = ' . $value
                            . ' OR ' . $column . ' IS NULL)');
                    } else {
                        $search->addFilter($column . ' = ' . $value);
                    }
                }
            }
        }

        // Checks if the notFilter is not empty
        if (!empty($notFilter)) {
            // Loops through the reverse filter
            foreach ($notFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    // Loops through the array
                    foreach ($value as $v) {
                        // Checks if the value being filtered out is null
                        if (empty($v)) {
                            $search->addFilter($column . ' IS NOT NULL');
                        } else {
                            $search->addFilter($column . ' != ' . $v);
                        }
                    }
                } else {
                    // Checks if the value being filtered out is null
                    if (empty($value)) {
                        $search->addFilter($column . ' IS NOT NULL');
                    } else {
                        $search->addFilter($column . ' != ' . $value);
                    }
                }
            }
        }

        //add grouping
        if (isset($data['group'])) {
            $search->groupBy($data['group']);
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            // Default encasement
            $encase = 'TRIM(LOWER(%s))';

            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false) :
                case (strpos($sort, '_experience') !== false) :
                    break;

                default :
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();
        //get resume
        foreach ($rows as $key => $value) {
            //get profile resume
            $rows[$key]['post_resume'] = $this->resource
                ->search('post_resume')
                ->setColumns('resume_link', 'resume_id')
                ->innerJoinUsing('resume', 'resume_id')
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->filterByPostId($value['post_id'])
                ->filterByProfileId($value['profile_id'])
                ->getRow();
        }

        //return response format
        return [
            'rows'  => $rows,
            'total' => $search->getTotal()
        ];
    }


    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search (array $data = [])
    {
        $filter             = [];
        $exactFilter        = [];
        $notFilter          = [];
        $jsonFilter         = [];
        $jsonNullableFilter = [];
        $columns            = [];
        $range              = 50;
        $start              = 0;
        $order              = [];
        $count              = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['exact_filter']) && is_array($data['exact_filter'])) {
            $exactFilter = $data['exact_filter'];
        }

        if (isset($data['not_filter']) && is_array($data['not_filter'])) {
            $notFilter = $data['not_filter'];
        }

        if (isset($data['json_filter']) && is_array($data['json_filter'])) {
            $jsonFilter = $data['json_filter'];
        }

        if (isset($data['json_nullable_filter']) && is_array($data['json_nullable_filter'])) {
            $jsonNullableFilter = $data['json_nullable_filter'];
        }

        if (isset($data['columns']) && is_array($data['columns'])) {
            $columns = $data['columns'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        if (isset($data['export'])) {
            $range = 0;
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (isset($data['matching'])) {
            $matching = $data['matching'];

            if (!is_array($matching)) {
                $matching = [$matching];
            }
        }

        if (!isset($filter['post_active'])) {
            $filter['post_active'] = 1;
        }

        // Checks if post active if equal to export_expired
        if ($filter['post_active'] == "export_expired") {
            $filter['post_active'] = 1;
            $data['post_expires']  = '-1';
        }

        // Checks if active is set to -1
        if ($filter['post_active'] == -1) {
            unset($filter['post_active']);
        }

        $search = $this->resource
            ->search('post');

        // Checks for columns to be set
        if (!empty($columns)) {
            if (is_array($columns)) {
                $columns = implode(',', $columns);
            }

            $search->setColumns($columns);
        }

        // if we're pulling today, we have to pull all
        if (!isset($data['today'])) {
            $search->setStart($start)
                ->setRange($range);
        }

        //join profile
        $search->innerJoinUsing('post_profile', 'post_id');
        $search->innerJoinUsing('profile', 'profile_id');

        // check if post location is array
        if (isset($filter['post_location']) &&
            is_array($filter['post_location'])) {
            $search->addFilter('post_location IN %s', $filter['post_location']);

            unset($filter['post_location']);
        }

        if (isset($data['location_like'])) {
            $or      = [];
            $where   = [];
            $where[] = 'LOWER(post_location) LIKE %s';
            $or[] = '%' . strtolower($data['location_like']) . '%';

            $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
            $or[] = '%' . strtolower($data['location_like']) . '%';

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
            unset($data['location_like']);
        }

        if (isset($data['position_like'])) {
            $or = [];
            $where = [];

            $where[] = 'LOWER(post_position) LIKE %s';
            $or[] = '%' . strtolower($data['position_like']) . '%';

            $where[] = "JSON_SEARCH(LOWER(post_tags), 'one', %s) IS NOT NULL";
            $or[] = '%' . strtolower($data['position_like']) . '%';

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
            unset($data['position_like']);
        }

        // check if post flag is array
        if (isset($filter['post_flag']) &&
            is_array($filter['post_flag'])) {
            $search->addFilter('post_flag IN %s', $filter['post_flag']);

            unset($filter['post_flag']);
        }

        //add filters
        if (!isset($data['post_duplicate'])) {
            foreach ($filter as $column => $value) {
                if (is_numeric($value) && in_array($column, ['profile_id', 'post_active', 'post_flag'])) {
                    $search->addFilter($column . ' = ' . $value);
                } elseif (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                    if ($column == 'post_tags') {
                        $search->addFilter('LOWER(' . $column . ')' . ' LIKE %s', '%' . $value . '%');
                    } elseif ($column == 'post_type') {
                        $search->filterByPostType($value);
                    } else {
                        //skip empty values in query
                        if (empty($value)) {
                            continue;
                        }

                        $search->addFilter($column . ' LIKE %s', '%' . $value . '%');
                    }
                }
            }

            // Checks if there are exact filters
            if (!empty($exactFilter)) {
                // Loops through the filters
                foreach ($exactFilter as $column => $value) {
                    // Checks if the value is not empty
                    if (!is_null($value)) {
                        $search->addFilter($column . ' = ' . $value);
                    }
                }
            }
        }

        // Checks if the notFilter is not empty
        if (!empty($notFilter)) {
            // Loops through the reverse filter
            foreach ($notFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    // Loops through the array
                    foreach ($value as $v) {
                        // Checks if the value being filtered out is null
                        if (is_null($v)) {
                            $search->addFilter($column . ' IS NOT NULL');
                        } else {
                            $search->addFilter($column . ' != ' . $v);
                        }
                    }
                } else {
                    // Checks if the value being filtered out is null
                    if (is_null($value)) {
                        $search->addFilter($column . ' IS NOT NULL');
                    } else {
                        $search->addFilter($column . ' != ' . $value);
                    }
                }
            }
        }

        /// Variable declaration
        $notNullJson = [];

        //post_tags
        if (isset($data['post_tags']) && !empty($data['post_tags'])) {
            if (!is_array($data['post_tags'])) {
                $data['post_tags'] = [$data['post_tags']];
            }

            $notNullJson['post_tags'] = $data['post_tags'];
        }

        //post_notify
        if (isset($data['post_notify']) && !empty($data['post_notify'])) {
            if (!is_array($data['post_notify'])) {
                $data['post_notify'] = [$data['post_notify']];
            }

            $notNullJson['post_notify'] = $data['post_notify'];
        }

        if (!empty($jsonFilter)) {
            // Loops through the json filter
            foreach ($jsonFilter as $key => $jFilter) {
                if (!is_array($jFilter)) {
                    $jFilter = [$jFilter];
                }

                $notNullJson[$key] = $jFilter;
            }
        }

        if (!empty($jsonNullableFilter)) {
            // loop through the json columns
            foreach ($jsonNullableFilter as $column => $nullable) {
                // array values
                $filter = $nullable;

                // if string, make an array
                if (!is_array($nullable)) {
                    $filter = [$nullable];
                }

                $or    = [];
                $where = [];

                // loop through each filter
                foreach ($filter as $value) {
                    $where[] = "(
                        JSON_SEARCH(
                            LOWER(" . $column . "), 'one', %s
                        ) IS NOT NULL OR
                        JSON_SEARCH(
                            LOWER(" . $column . "), 'one', %s
                        ) IS NULL)";

                    $or[] = '%' . strtolower($value) . '%';
                    $or[] = '%' . strtolower($value) . '%';
                }

                array_unshift($or, '(' . implode(' OR ', $where) . ')');
                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        if (!empty($notNullJson)) {
            // Loops through the json columns to be filtered
            foreach ($notNullJson as $column => $jsonFilter) {
                // Variable declaration
                $or    = [];
                $where = [];

                // Loops throguh the notifications
                foreach ($jsonFilter as $value) {
                    $where[] = "JSON_SEARCH(LOWER(" . $column . "), 'one', %s) IS NOT NULL";
                    $or[]    = '%' . strtolower($value) . '%';
                }

                // Implodes the notifications into an OR statement
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //post_expires
        if (!isset($data['post_duplicate']) && isset($filter['post_active'])) {
            if (!isset($data['post_expires'])) {
                $data['post_expires'] = 'now';
            }

            // Checks for expiring soon
            if ($data['post_expires'] === 'soon') {
                $today   = date('Y-m-d', strtotime('now'));
                $expires = strtotime('+11 days', strtotime('now'));
                $expires = date('Y-m-d', $expires);

                $query = '(post_expires >= "' . $today . ' 00:00:00"' .
                    ' AND post_expires < "' . $expires . ' 00:00:00")';
                $search->addFilter($query);
            } else {
                if ($data['post_expires'] === '-1') {
                    $search->addFilter('post_expires < %s', date(
                        'Y-m-d H:i:s',
                        strtotime('now')
                    ));
                } elseif ($data['post_expires'] === 'disabled') {
                    // nothing to do
                } else {
                    $search->addFilter('post_expires > %s', date(
                        'Y-m-d H:i:s',
                        strtotime($data['post_expires'])
                    ));
                }
            }
        }

        // Add profile id if education school is set
        if (isset($data['post_school'])) {
            $search->filterByProfileId($data['profile_id']);
        }

        if (isset($data['today'])) {
            $data['today'] = date('Y-m-d');
            $search->addFilter('post_created LIKE %s', '%' . $data['today'] . '%');
        }

        //checks for created and restored posts
        if (isset($data['restore_today'])) {
            $data['restore_today'] = date('Y-m-d');

            $where   = [];
            $where[] = '(post_created LIKE %s OR ' .
                '(post_updated LIKE %s AND post_active = 1))';

            array_push($where, '%' . $data['restore_today'] . '%');
            array_push($where, '%' . $data['restore_today'] . '%');
            call_user_func([$search, 'addFilter'], ...$where);
        }

        if (isset($data['date'])) {
            $date = $data['date'];
        }

        // if both start and end dates are provided as filters
        if ((isset($date['start_date']) && isset($date['end_date']))
            && (!empty($date['start_date'])) && (!empty($date['end_date']))) {

            $search->addFilter('post_created >= ' . "'" . date("Y-m-d 0:00:00", strtotime($date['start_date'])) . "'")
                ->addFilter('post_created <= ' . "'" . date("Y-m-d 23:59:59", strtotime($date['end_date'])) . "'");
        }
        // no end date
        if ((isset($date['start_date']) && (empty($date['end_date'])))
            && (!empty($date['start_date'])) && (isset($date['end_date']))) {
            $search->addFilter('post_created >= ' . "'" . date("Y-m-d 0:00:00", strtotime($date['start_date'])) . "'");
        }
        // no start date
        if ((empty($date['start_date']) && (isset($date['end_date'])))
            && (isset($date['start_date'])) && (!empty($date['end_date']))) {
            $search->addFilter('post_created <= ' . "'" . date("Y-m-d 23:59:59", strtotime($date['end_date'])) . "'");
        }

        if (isset($data['has_resume']) && $data['has_resume']) {
            if ($data['has_resume']) {
                $search->innerJoinUsing('information_profile', 'profile_id');
            }
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or      = [];
                $where   = [];
                $where[] = 'LOWER(post_name) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_email) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_phone) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_position) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_location) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_detail) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_id) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_name) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(post_email) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        //matching?
        if (isset($matching)) {
            $or    = [];
            $where = [];
            foreach ($matching as $match) {
                if (!isset($match['post_position'], $match['post_location'])) {
                    continue;
                }

                $where[] = '(LOWER(post_position) LIKE %s AND LOWER(post_location) LIKE %s)';
                $or[]    = '%' . strtolower(trim($match['post_position'])) . '%';
                $or[]    = '%' . strtolower(trim($match['post_location'])) . '%';
            }

            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        // check if post position is array
        if (isset($data['post_position']) &&
            is_array($data['post_position'])) {
            $data['post_position'] = array_column($data['post_position'], 'position_name');
            $search->addFilter('post_position IN %s', $data['post_position']);
        }

        //add grouping
        if (isset($data['group'])) {
            $search->groupBy($data['group']);
        }

        //add grouping
        if (isset($data['group'])) {
            $search->groupBy($data['group']);
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            // Default encasement
            $encase = 'TRIM(LOWER(%s))';

            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false) :
                case (strpos($sort, '_experience') !== false) :
                case (strpos($sort, '_salary') !== false) :
                case (strpos($sort, '_like_count') !== false) :
                    break;

                default :
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }

        // Check if duplicated
        if (isset($data['post_duplicate']) && $data['post_duplicate'] === true) {
            $search->filterByPostActive(1);
            $search->filterByProfileId($data['profile_id']);
            $search->filterByPostName($data['post_name']);
            $search->filterByPostPosition($data['post_position']);
            $search->filterByPostLocation($data['post_location']);
            if (!empty($data['post_experience'])) {
                $search->filterByPostExperience($data['post_experience']);
            }
        }

        // Gets the rows
        $rows = $search->getRows();

        // Loops through the rows
        foreach ($rows as $i => $results) {
            // Checks for post_notify
            if (isset($results['post_notify'])) {
                $rows[$i]['post_notify'] = json_decode($results['post_notify'], true);
            } else {
                $rows[$i]['post_notify'] = [];
            }

            // Checks for post_tags
            if (isset($results['post_tags'])) {
                $rows[$i]['post_tags'] = json_decode($results['post_tags'], true);
            } else {
                $rows[$i]['post_tags'] = [];
            }

            // Checks for post_geo_location
            if (isset($results['post_geo_location'])) {
                $rows[$i]['post_geo_location'] = json_decode($results['post_geo_location'], true);
            } else {
                $rows[$i]['post_geo_location'] = [];
            }

            // Checks for post_package
            if (isset($results['post_package'])) {
                $rows[$i]['post_package'] = json_decode($results['post_package'], true);
            } else {
                $rows[$i]['post_package'] = [];
            }

            // Checks for profile_package
            if (isset($results['profile_package'])
                && !empty($results['profile_package'])) {
                $rows[$i]['profile_package'] = json_decode($results['profile_package'], true);
            } else {
                $rows[$i]['profile_package'] = [];
            }

            $rows[$i]['post_slug'] = $this->slugify(
                $results['post_position'],
                $results['post_id']
            );

            $likers = $this->resource
                ->search('post_liked')
                ->filterByPostId($rows[$i]['post_id'])
                ->getRows();

            $like = [];
            foreach ($likers as $key => $liker) {
                $like[$liker['profile_id']] = $liker;
            }

            $rows[$i]['likers'] = $like;
            //get total matches
            if (isset($data['withMatches'])) {
                $rows[$i]['post_total_matches'] = $likers = $this->resource
                    ->search('post')
                    ->setColumns('post_id')
                    ->addFilter('post_active')
                    ->addFilter('(LOWER(post_position) LIKE %s AND LOWER(post_location) LIKE %s)', strtolower($results['post_position']), strtolower($results['post_location']))
                    ->addFilter('(post_id != ' . $results['post_id'] . ')')
                    ->addFilter('post_expires > %s', date(
                        'Y-m-d H:i:s',
                        strtotime('now')
                    ))
                    ->addFilter('post_type != "' . $results['post_type'] . '"')
                    ->getTotal();
            }

            //get post form
            if (isset($data['withForm'])) {
                $rows[$i]['post_form'] = $this->resource
                    ->search('post_form')
                    ->filterByPostId($results['post_id'])
                    ->getRow();
            }

            if (isset($data['strip_tags']) && $data['strip_tags']) {
                $rows[$i]['post_detail'] = strip_tags($results['post_detail']);
            }
        }

        //return response format
        return [
            'rows'  => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update (array $data)
    {
        // Sets the columns to be purified
        $columns = [
            'post_location',
            'post_detail'
        ];

        // Checks if there are columns to purifiy
        if (!empty($columns)) {
            // Loops through the columns to be purified
            foreach ($columns as $column) {
                // Checks if the data exists
                if (isset($data[$column])) {
                    // Strip malicious tags using HTMLPurifier
                    $purifier      = new HTMLPurifier;
                    $data[$column] = $purifier->purify($data[$column]);
                }
            }
        }

        return $this->resource
            ->model($data)
            ->setPostUpdated(date('Y-m-d H:i:s'))
            ->save('post')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $postPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile ($postPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->setProfileId($profilePrimary)
            ->insert('post_profile');
    }

    /**
     * Links form
     *
     * @param *int $postPrimary
     * @param *int $formPrimary
     */
    public function linkForm ($postPrimary, $formPrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->setFormId($formPrimary)
            ->insert('post_form');
    }

    /**
     * Slugify a given string
     *
     * @param  string $string
     *
     * @return string
     */
    public function slugify ($string, $id)
    {
        $slug = preg_replace("/[^a-zA-Z0-9_\-\s]/i", '', $string);
        $slug = str_replace(' ', '-', trim($slug));
        $slug = preg_replace("/-+/i", '-', $slug);
        $slug = strtolower($slug);
        $slug = substr($slug, 0, 90);
        $slug = str_replace('-', ' ', $slug);
        $slug = ucwords($slug);
        $slug = str_replace(' ', '-', $slug);
        $slug = $slug . '-' . 'p' . $id;

        return $slug;
    }

    /**
     * Unlinks form
     *
     * @param *int $postPrimary
     * @param *int $formPrimary
     */
    public function unlinkForm ($postPrimary, $formPrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->setFormId($formPrimary)
            ->remove('post_form');
    }

    /**
     * Unlinks profile
     *
     * @param *int $postPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile ($postPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->setProfileId($profilePrimary)
            ->remove('post_profile');
    }

    /**
     * Links comment
     *
     * @param *int $postPrimary
     * @param *int $commentPrimary
     */
    public function linkComment ($postPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->setCommentId($commentPrimary)
            ->insert('post_comment');
    }

    /**
     * Unlinks comment
     *
     * @param *int $postPrimary
     * @param *int $commentPrimary
     */
    public function unlinkComment ($postPrimary, $commentPrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->setCommentId($commentPrimary)
            ->remove('post_comment');
    }

    /**
     * Unlinks All comment
     *
     * @param *int $postPrimary
     * @param *int $commentPrimary
     */
    public function unlinkAllComment ($postPrimary)
    {
        return $this->resource
            ->model()
            ->setPostId($postPrimary)
            ->remove('post_comment');
    }

    /**
     * filter variables to be used in gathering chart data
     *
     * @param array $data
     *
     * @return array
     */
    public function chartFilter (array $data)
    {
        //add query filters
        if (isset($data['chartFilter'])) {
            foreach ($data['chartFilter'] as $column => $value) {
                //custom query for custom date filter
                if ($column === 'date') {
                    $where[] = sprintf(
                        'post_created BETWEEN \'%s 00:00:00\' AND \'%s 23:59:59\'',
                        date('Y-m-d', strtotime($value['start'])),
                        date('Y-m-d', strtotime($value['end']))
                    );

                    continue;
                }
                $where[] = sprintf('%s = \'%s\'', $column, $value);
            }

            $where = 'WHERE ' . implode(' AND ', $where);
        } else {
            $where = '';
        }

        return $where;
    }

    /**
     * Select total of signups
     *
     * @param array $data
     *
     * @return array
     */
    public function getChart (array $data)
    {
        $where  = $this->chartFilter($data);
        $column = 'count(*)';
        //change column if filtered by interested
        if ($data['chart'] == 'interested') {
            $column = 'SUM(`post_like_count`)';
        }

        //we have a different query if the filter is custom date
        if (isset($data['chartFilter']['date'])) {
            $sql = $this->resource->query(
                'SELECT ' . $column . ' as total,
                date_format(post_created, \'%d\') as day,
                date_format(post_created, \'%M\') as month,
                YEAR(post_created) as year FROM post
                INNER JOIN post_profile USING(post_id)
                INNER JOIN profile USING(profile_id)' . $where .
                ' GROUP BY day, month, year ORDER BY MIN(post_created)');

            return $sql;
        }

        $sql = $this->resource->query(
            'SELECT ' . $column . ' as total,
            date_format(post_created, \'%M\') as month,
            YEAR(post_created) as year FROM post
            INNER JOIN post_profile USING(post_id)
            INNER JOIN profile USING(profile_id)' . $where .
            ' GROUP BY month, year ORDER BY MIN(post_created)');

        return $sql;
    }

    /**
     * Select total post for credits
     *
     * @param array $data
     *
     * @return array
     */
    public function getTotalPostCredit (array $data)
    {
        //get today's created posts (active or inactive)
        $todayTotal = $this->resource
            ->search('post')
            ->innerJoinUsing('post_profile', 'post_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByProfileActive(1)
            ->filterByProfileId($data['profile_id'])
            ->addFilter('post_expires > %s', date('Y-m-d H:i:s'))
            ->addFilter('post_created LIKE %s', '%' . date('Y-m-d') . '%')
            ->getTotal();

        //get today's created and restored active posts
        $restoreTodayTotal = $this->resource
            ->search('post')
            ->innerJoinUsing('post_profile', 'post_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByProfileActive(1)
            ->filterByProfileId($data['profile_id'])
            ->addFilter('post_expires > %s', date('Y-m-d H:i:s'))
            ->filterByPostActive(1)
            ->addFilter('(post_created LIKE %s OR post_restored LIKE %s)',
                '%' . date('Y-m-d') . '%', '%' . date('Y-m-d') . '%')
            ->getTotal();

        //determines what total to be returned
        if ($todayTotal >= 5 || $restoreTodayTotal >= 5) {
            if (($todayTotal >= 5 && $restoreTodayTotal < 5) &&
                ($data['post_action'] === 'restore' ||
                    $data['post_action'] === 'renew')
            ) {
                return ['total' => $restoreTodayTotal];
            } elseif ($todayTotal > $restoreTodayTotal) {
                return ['total' => $todayTotal];
            } else {
                return ['total' => $restoreTodayTotal];
            }
        } elseif ($data['post_action'] === 'create') {
            return ['total' => $todayTotal];
        } elseif ($data['post_action'] === 'restore' ||
            $data['post_action'] === 'renew'
        ) {
            return ['total' => $restoreTodayTotal];
        }
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchExpireDate (array $data = [])
    {

        $filter      = [];
        $exactFilter = [];
        $notFilter   = [];
        $jsonFilter  = [];
        $columns     = [];
        $range       = 50;
        $start       = 0;
        $order       = [];
        $count       = 0;

        $keywords = null;


        if (isset($data['columns']) && is_array($data['columns'])) {
            $columns = $data['columns'];
        }

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (isset($data['start']) && is_numeric($data['start'])) {
            $start = $data['start'];
        }

        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        if (!isset($filter['post_active'])) {
            $filter['post_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['post_active'] == -1) {
            unset($filter['post_active']);
        }

        $search = $this->resource
            ->search('post');

        // Checks for columns to be set
        if (!empty($columns)) {
            if (is_array($columns)) {
                $columns = implode(',', $columns);
            }

            $search->setColumns($columns);
        }

        // if we're pulling today, we have to pull all
        if (!isset($data['today'])) {
            $search->setStart($start)
                ->setRange($range);
        }

        //join profile
        $search->innerJoinUsing('post_profile', 'post_id');
        $search->innerJoinUsing('profile', 'profile_id');

        // Checks if post_active is set
        if (isset($filter['post_active'])) {
            $search->filterByProfileActive(1);
        }

        // Checks if post_active is 0
        if (isset($filter['post_active'])
            && $filter['post_active'] == 0) {
            $search->filterByPostActive(0);
        }

        //post_expires
        if (!isset($data['post_duplicate']) && isset($filter['post_active'])) {
            if (!isset($data['post_expires'])) {
                $data['post_expires'] = 'now';
            }

            // Checks for expiring soon
            if ($data['post_expires'] == 'soon') {
                $today   = date('Y-m-d', strtotime('now'));
                $expires = strtotime('+11 days', strtotime('now'));
                $expires = date('Y-m-d', $expires);

                $query = '(post_expires >= "' . $today . ' 00:00:00"' .
                    ' AND post_expires < "' . $expires . ' 00:00:00")';
                $search->addFilter($query);
            } else {
                if ($data['post_expires'] === '-1') {
                    $search->addFilter('post_expires < %s', date(
                        'Y-m-d H:i:s',
                        strtotime('now')
                    ));
                } else {
                    $search->addFilter('post_expires > %s', date(
                        'Y-m-d H:i:s',
                        strtotime($data['post_expires'])
                    ));
                }
            }
        }

        if (isset($data['today'])) {
            $data['today'] = date('Y-m-d');
            $search->addFilter('post_created LIKE %s', '%' . $data['today'] . '%');
        }

        //checks for created and restored posts
        if (isset($data['restore_today'])) {
            $data['restore_today'] = date('Y-m-d');

            $where   = [];
            $where[] = '(post_created LIKE %s OR ' .
                '(post_updated LIKE %s AND post_active = 1))';

            array_push($where, '%' . $data['restore_today'] . '%');
            array_push($where, '%' . $data['restore_today'] . '%');
            call_user_func([$search, 'addFilter'], ...$where);
        }

        if (isset($data['date'])) {
            $date = $data['date'];
        }

        // if both start and end dates are provided as filters
        if ((isset($date['start_date']) && isset($date['end_date']))
            && (!empty($date['start_date'])) && (!empty($date['end_date']))) {

            $search->addFilter('post_created >= ' . "'" . date("Y-m-d 0:00:00", strtotime($date['start_date'])) . "'")
                ->addFilter('post_created <= ' . "'" . date("Y-m-d 23:59:59", strtotime($date['end_date'])) . "'");
        }
        // no end date
        if ((isset($date['start_date']) && (empty($date['end_date'])))
            && (!empty($date['start_date'])) && (isset($date['end_date']))) {
            $search->addFilter('post_created >= ' . "'" . date("Y-m-d 0:00:00", strtotime($date['start_date'])) . "'");
        }
        // no start date
        if ((empty($date['start_date']) && (isset($date['end_date'])))
            && (isset($date['start_date'])) && (!empty($date['end_date']))) {
            $search->addFilter('post_created <= ' . "'" . date("Y-m-d 23:59:59", strtotime($date['end_date'])) . "'");
        }

        if (isset($data['has_resume']) && $data['has_resume']) {
            if ($data['has_resume']) {
                $search->addFilter('post_resume IS NOT NULL AND post_resume != \'\'');
            } else {
                $search->addFilter('post_resume IS NULL');
            }
        }

        // Check if duplicated
        if (isset($data['post_duplicate']) && $data['post_duplicate'] === true) {
            $search->filterByPostActive(1);
            $search->filterByProfileId($data['profile_id']);
            $search->filterByPostName($data['post_name']);
            $search->filterByPostPosition($data['post_position']);
            $search->filterByPostLocation($data['post_location']);
            if (!empty($data['post_experience'])) {
                $search->filterByPostExperience($data['post_experience']);
            }
        }

        // Gets the rows
        $rows = $search->getRows();

        // Loops through the rows
        foreach ($rows as $i => $results) {
            // Checks for post_notify
            if (isset($results['post_notify'])) {
                $rows[$i]['post_notify'] = json_decode($results['post_notify'], true);
            } else {
                $rows[$i]['post_notify'] = [];
            }

            // Checks for post_tags
            if (isset($results['post_tags'])) {
                $rows[$i]['post_tags'] = json_decode($results['post_tags'], true);
            } else {
                $rows[$i]['post_tags'] = [];
            }

            // Checks for post_geo_location
            if (isset($results['post_geo_location'])) {
                $rows[$i]['post_geo_location'] = json_decode($results['post_geo_location'], true);
            } else {
                $rows[$i]['post_geo_location'] = [];
            }

            // Checks for post_package
            if (isset($results['post_package'])) {
                $rows[$i]['post_package'] = json_decode($results['post_package'], true);
            } else {
                $rows[$i]['post_package'] = [];
            }

            // Checks for profile_package
            if (isset($results['profile_package'])
                && !empty($results['profile_package'])) {
                $rows[$i]['profile_package'] = json_decode($results['profile_package'], true);
            } else {
                $rows[$i]['profile_package'] = [];
            }

            $rows[$i]['post_slug'] = $this->slugify(
                $results['post_position'],
                $results['post_id']
            );

            $likers = $this->resource
                ->search('post_liked')
                ->filterByPostId($rows[$i]['post_id'])
                ->getRows();

            $like = [];
            foreach ($likers as $key => $liker) {
                $like[$liker['profile_id']] = $liker;
            }

            $rows[$i]['likers'] = $like;
            //get total matches
            if (isset($data['withMatches'])) {
                $rows[$i]['post_total_matches'] = $likers = $this->resource
                    ->search('post')
                    ->setColumns('post_id')
                    ->addFilter('(LOWER(post_position) LIKE %s AND LOWER(post_location) LIKE %s)', strtolower($results['post_position']), strtolower($results['post_location']))
                    ->addFilter('(post_id != ' . $results['post_id'] . ')')
                    ->addFilter('post_expires > %s', date(
                        'Y-m-d H:i:s',
                        strtotime('now')
                    ))
                    ->addFilter('post_type != "' . $results['post_type'] . '"')
                    ->getTotal();
            }

            //get post form
            if (isset($data['withForm'])) {
                $rows[$i]['post_form'] = $this->resource
                    ->search('post_form')
                    ->filterByPostId($results['post_id'])
                    ->getRow();
            }
        }

        //return response format
        return [
            'rows'  => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Get Already liked
     *
     * @param *int $post
     * @param *int $profile
     *
     * @return bool
     */
    public function getAlreadyLiked ($post, $profile)
    {
        return !!$this->resource
            ->search('post_liked')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByPostId($post)
            ->filterByProfileEmail($profile)
            ->getRow();
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function getFeaturedCompanyPosts (array $data = [])
    {
        $range   = 50;
        $startId = 0;
        $order   = [];

        // range
        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        // start id
        if (isset($data['start_id']) && is_numeric($data['start_id'])) {
            $startId = $data['start_id'];
        }

        // order
        if (isset($data['order']) && is_array($data['order'])) {
            $order = $data['order'];
        }

        $search = $this->resource
            ->search('post')
            ->setRange($range);

        //join profile
        $search->innerJoinUsing('post_profile', 'post_id');
        $search->innerJoinUsing('profile', 'profile_id');

        // post_id start
        $search->addFilter('post_id > %d', $startId);
        // banner is not null
        $search->addFilter('post_banner IS NOT NULL');
        // post_type is poster
        $search->filterByPostType('poster');
        // post_flag is 1
        $search->filterByPostFlag(1);
        // post_active is 1
        $search->filterByPostActive(1);
        // post is not expired
        $search->addFilter('post_expires > %s', date(
            'Y-m-d H:i:s',
            strtotime('now')
        ));

        // add custom sorting if there is,
        // otherwise sort by post_like_count
        if ($order) {
            foreach ($order as $column => $direction) {
                $search->addSort($column, $direction);
            }
        } else {
            // post_like_count descending
            $search->sortByPostLikeCount('DESC');
        }

        // Gets the rows
        $rows = $search->getRows();

        // Loops through the rows
        foreach ($rows as $i => $results) {
            // Checks for post_notify
            if (isset($results['post_notify'])) {
                $rows[$i]['post_notify'] = json_decode($results['post_notify'], true);
            } else {
                $rows[$i]['post_notify'] = [];
            }

            // Checks for post_tags
            if (isset($results['post_tags'])) {
                $rows[$i]['post_tags'] = json_decode($results['post_tags'], true);
            } else {
                $rows[$i]['post_tags'] = [];
            }

            // Checks for post_geo_location
            if (isset($results['post_geo_location'])) {
                $rows[$i]['post_geo_location'] = json_decode($results['post_geo_location'], true);
            } else {
                $rows[$i]['post_geo_location'] = [];
            }

            // Checks for post_package
            if (isset($results['post_package'])) {
                $rows[$i]['post_package'] = json_decode($results['post_package'], true);
            } else {
                $rows[$i]['post_package'] = [];
            }

            // Checks for profile_package
            if (isset($results['profile_package'])
                && !empty($results['profile_package'])) {
                $rows[$i]['profile_package'] = json_decode($results['profile_package'], true);
            } else {
                $rows[$i]['profile_package'] = [];
            }

            $rows[$i]['post_slug'] = $this->slugify(
                $results['post_position'],
                $results['post_id']
            );

            if (isset($data['strip_tags']) && $data['strip_tags']) {
                $rows[$i]['post_detail'] = strip_tags($results['post_detail']);
            }
        }

        return [
            'rows'  => $rows,
            'total' => $search->getTotal()
        ];
    }
}
