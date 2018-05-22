<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Profile\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;
use HTMLPurifier;

/**
 * Profile SQL Service
 *
 * @vendor   Acme
 * @package  profile
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'profile';

    /**
     * Registers the resource for use
     *
     * @param Resource $resource
     */
    public function __construct(Resource $resource)
    {
        $this->resource = SqlFactory::load($resource);
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function addExperience($profileId, $points)
    {
        return $this->resource->query('UPDATE profile SET '
        . 'profile_experience = profile_experience + :bind0bind '
        . 'WHERE profile_id=:bind1bind', [
            ':bind0bind' => $points,
            ':bind1bind' => $profileId
        ]);
    }

    /**
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {
        // Sets the columns to be purified
        $columns = array('profile_detail');

        // Checks if there are columns to purifiy
        if (!empty($columns)) {
            // Loops through the columns to be purified
            foreach ($columns as $column) {
                // Checks if the data exists
                if (isset($data[$column])) {
                    // Strip malicious tags using HTMLPurifier
                    $purifier = new HTMLPurifier;
                    $data[$column] = $purifier->purify($data[$column]);
                }
            }
        }

        return $this->resource
            ->model($data)
            ->setProfileCreated(date('Y-m-d H:i:s'))
            ->setProfileUpdated(date('Y-m-d H:i:s'))
            ->save('profile')
            ->get();
    }

    /**
     * Checks to see if the email already exists
     *
     * @param *string      $email
     * @param string|false $password
     *
     * @return bool
     */
    public function exists($email, $password = false)
    {
        $search = $this->resource
            ->search('profile')
            ->filterByProfileEmail($email);

        return !!$search->getRow();
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('profile');

        $search->filterByProfileId($id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        //achievements
        if ($results['profile_achievements']) {
            $results['profile_achievements'] = json_decode($results['profile_achievements'], true);
        } else {
            $results['profile_achievements'] = [];
        }

        if (isset($results['profile_interviewer'])) {
            if ($results['profile_interviewer']) {
                $results['profile_interviewer'] = json_decode($results['profile_interviewer'], true);
            } else {
                $results['profile_interviewer'] = [];
            }
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

        $deal = $this->resource->search('deal')
            ->leftJoinUsing('deal_company', 'deal_id')
            ->filterByProfileId($id)
            ->filterByDealType('profile')
            ->getRow();

        if (!$deal) {
            $deal = [];
        }

        $results = array_merge($results, $deal);

        if (isset($results['deal_id'])) {
            $results['agent'] = $this->resource->search('profile')
                ->innerJoinUsing('deal_agent', 'profile_id')
                ->filterByDealId($results['deal_id'])
                ->getRow();
        }

        return $results;
    }

    /**
     * Get detail from database
     *
     * @param *int $id
     *
     * @return array
     */
    public function getByEmail($email)
    {
        $search = $this->resource->search('profile');

        $search->filterByProfileEmail($email);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        //achievements
        if ($results['profile_achievements']) {
            $results['profile_achievements'] = json_decode($results['profile_achievements'], true);
        } else {
            $results['profile_achievements'] = [];
        }

        if (isset($results['profile_interviewer'])) {
            if ($results['profile_interviewer']) {
                $results['profile_interviewer'] = json_decode($results['profile_interviewer'], true);
            } else {
                $results['profile_interviewer'] = [];
            }
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
     * Remove from database
     * PLEASE BECAREFUL USING THIS !!!
     * It's here for clean up scripts
     *
     * @param *int $id
     */
    public function remove($id)
    {
        //please rely on SQL CASCADING ON DELETE
        return $this->resource
            ->model()
            ->setProfileId($id)
            ->remove('profile');
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchEmployment(array $data = [])
    {
        $search = $this->resource
            ->search('profile')
            ->setStart(0)
            ->setRange(0)
            // only seeker
            ->addFilter('(profile_company IS NULL OR profile_company = "")');

        if (isset($data['date']['start_date']) || isset($data['date']['end_date'])) {
            if (($data['date']['start_date'] !== '')
                && ($data['date']['end_date'] !== '')
            ) {
                    $search
                        ->addFilter('profile_created >= '."'". date("Y-m-d 0:00:00", strtotime($data['date']['start_date']))."'")
                        ->addFilter('profile_created <= '."'". date("Y-m-d 23:59:59", strtotime($data['date']['end_date']))."'");
            }
        }

        if (isset($data['location'])
            && !empty($data['location'])
        ) {
            $or = [];
            $where = [];
            $where[] = 'LOWER(profile_address_city) LIKE %s';
            $or[] = '%' . strtolower($data['location']) . '%';
            $where[] = 'LOWER(profile_address_state) LIKE %s';
            $or[] = '%' . strtolower($data['location']) . '%';
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        $total = $search->getTotal();

        $search->innerJoinUsing('information_profile', 'profile_id')
            ->innerJoinUsing('experience_information', 'information_id')
            ->innerJoinUsing('experience', 'experience_id')
            ->groupBy('profile_id')
            ->addFilter('(experience_to IS NULL AND YEAR(experience_created) = YEAR("'.$data['date']['start_date'].'"))');

        $employed = $search->getTotal();

        return [
            'total' => $total ? $total : 0,
            'employed' => $employed ? $employed : 0
        ];
    }

    /**
     * Search in database
     *
     * @param array $data
     *
     * @return array
     */
    public function search(array $data = [])
    {
        $filter = [];
        $likeFilter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['like_filter']) && is_array($data['like_filter'])) {
            $likeFilter = $data['like_filter'];
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

        if (!isset($filter['profile_active'])) {
            $filter['profile_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['profile_active'] == -1) {
            unset($filter['profile_active']);
        }

        $search = $this->resource
            ->search('profile')
            ->setStart($start)
            ->setRange($range);

        if (isset($data['sales'])) {
            $search
                ->innerJoinUsing('deal_company', 'profile_id')
                ->innerJoinUsing('deal', 'deal_id')
                ->filterByDealType('profile');
        }

        // if we need auth profile
        if (isset($data['auth_profile'])) {
            $search = $search->leftJoinUsing('auth_profile', 'profile_id');
        }

        // profile_company filter
        if (isset($filter['type'])) {
            if ($filter['type'] == 'seeker') {
                $search->addFilter('(profile_company IS NULL OR profile_company = "")');
            } else if ($filter['type'] == 'poster') {
                $search->addFilter('(profile_company IS NOT NULL AND profile_company != "") ');
            }

            unset($filter['type']);
        }

        // Checks if the likeFilter is not empty
        if (!empty($likeFilter)) {
            // Loops through the reverse filter
            foreach ($likeFilter as $column => $value) {
                // Checks if the value is an array
                if (is_array($value)) {
                    // Loops through the array
                    foreach ($value as $v) {
                        $search->addFilter($column . ' LIKE "%' . $v . '%"');
                    }
                } else {
                    // Checks if the value being filtered out is null
                    $search->addFilter($column . ' LIKE "%' . $value . '%"');
                }
            }
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        // get posters less than 20 credits
        if (isset($data['less_credit']) && !empty($data['less_credit'])) {
            $search->addFilter('profile_credits < 20');
        }

        if (isset($data['crawled'])) {
            $search->addFilter('(auth_id IS NULL)');
            $search->addFilter('(profile_company IS NOT NULL AND profile_company != "") ');
        }

        if (isset($data['date']['start_date']) || isset($data['date']['end_date'])) {
            // if user sets a start and end date
            if (($data['date']['start_date'] !== '')
                && ($data['date']['end_date'] !== '')) {
                    $search
                        ->addFilter('profile_created >= '."'". date("Y-m-d 0:00:00", strtotime($data['date']['start_date']))."'")
                        ->addFilter('profile_created <= '."'". date("Y-m-d 23:59:59", strtotime($data['date']['end_date']))."'");
            }

            // start date is empty
            if (($data['date']['start_date'] ==='')
                && ($data['date']['end_date'] !=='')) {
                    $search
                        ->addFilter('profile_created <= '."'". date("Y-m-d 23:59:59", strtotime($data['date']['end_date']))."'");
            }

            //end date is empty
            if (($data['date']['start_date'] !== '')
                && ($data['date']['end_date'] === '')) {
                    $data['date']['start_date'] = date("Y-m-d", strtotime('-1 day', strtotime($data['date']['start_date'])));

                    $search
                        ->addFilter('profile_created >= '."'". date("Y-m-d 0:00:00", strtotime($data['date']['start_date']))."'");
            }
        }

        //profile_tags
        if (isset($data['profile_tags']) && !empty($data['profile_tags'])) {
            if (!is_array($data['profile_tags'])) {
                $data['profile_tags'] = [$data['profile_tags']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['profile_tags'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(profile_tags), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';
            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //profile_story
        if (isset($data['profile_story']) && !empty($data['profile_story'])) {
            if (!is_array($data['profile_story'])) {
                $data['profile_story'] = [$data['profile_story']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['profile_story'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(profile_story), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';
            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //profile_campaigns
        if (isset($data['profile_campaigns']) && !empty($data['profile_campaigns'])) {
            if (!is_array($data['profile_campaigns'])) {
                $data['profile_campaigns'] = [$data['profile_campaigns']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['profile_campaigns'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(profile_campaigns), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';
            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                 $where[] = 'LOWER(profile_id) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_email) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_company) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_phone) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }



        //add sorting
        foreach ($order as $sort => $direction) {
            // Default encasement
            $encase = 'TRIM(LOWER(%s))';

            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false):
                    break;

                default:
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }



        $rows = $search->getRows();

        foreach ($rows as $i => $results) {
            if ($results['profile_package']) {
                $results['profile_package'] = json_decode($results['profile_package'], true);
            } else {
                $results['profile_package'] = [];
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

            //campaigns
            if ($results['profile_campaigns']) {
                $results['profile_campaigns'] = json_decode($results['profile_campaigns'], true);
            } else {
                $results['profile_campaigns'] = [];
            }

            $rows[$i] = $results;
        }

        //return response format
        return [
            'rows' => $rows,
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
    public function searchApplicant(array $data = [])
    {
        $filter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

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

        if (isset($data['export'])) {
            $range = 0;
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['profile_active'])) {
            $filter['profile_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['profile_active'] == -1) {
            unset($filter['profile_active']);
        }

        $search = $this->resource
            ->search('profile')
            ->addFilter('(profile_company IS NULL OR profile_company = "") ')
            ->setStart($start)
            ->setRange($range);

        $search->innerJoinUsing('post_liked', 'profile_id');

        //add filters
        foreach ($filter as $column => $value) {
            if (strpos($column, 'profile_') !== false
                || strpos($column, 'post_') !== false) {
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                    $search->addFilter($column . ' = %s', $value);
                }
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            if (strpos($sort, 'profile_') === false
                && strpos($sort, 'post_') === false) {
                continue;
            }

            // Default encasement
            $encase = 'TRIM(LOWER(%s))';

            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false):
                    break;

                default:
                    $sort = sprintf($encase, $sort);
                    break;
            }

            $search->addSort($sort, $direction);
        }

        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                 $where[] = 'LOWER(profile_id) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_email) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_company) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_phone) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        $rows = $search->getRows();
        $total = $search->getTotal();

        // Loops through the rows
        foreach ($rows as $i => $row) {
            if ($row['profile_package']) {
                $row['profile_package'] = json_decode($row['profile_package'], true);
            } else {
                $row['profile_package'] = [];
            }

            if ($row['profile_achievements']) {
                $row['profile_achievements'] = json_decode($row['profile_achievements'], true);
            } else {
                $row['profile_achievements'] = [];
            }

            // Search for applicant data
            // Based on post id / post_id
            // Based on profile id / profile_id
            $applicant = $this->resource
                ->search('applicant')
                ->innerJoinUsing('applicant_post', 'applicant_id')
                ->innerJoinUsing('post', 'post_id')
                ->innerJoinUsing('applicant_profile', 'applicant_id')
                ->filterByPostId($filter['post_id'])
                ->filterByProfileId($row['profile_id']);

            foreach ($filter as $column => $value) {
                if (strpos($column, 'applicant_') !== false) {
                    if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                        if ($column == 'applicant_status') {
                            $applicant->addFilter('JSON_SEARCH(LOWER(applicant_status),
                                    "one" ,"%'.strtolower($filter['applicant_status']).'%") IS NOT NULL');
                        } else {
                            $applicant->addFilter($column . ' = %s', $value);
                        }
                    }
                }
            }

            $applicant = $applicant->getRow();

            // Checks if an applicant was returned
            if (!empty($applicant)) {
                if ($applicant['applicant_status']) {
                    $applicant['applicant_status'] = json_decode($applicant['applicant_status'], true);
                } else {
                    $applicant['applicant_status'] = [];
                }

                $row = array_merge($row, $applicant);

                // Default for answers
                $row['answers'] = '2';

                // Search for applicant answers
                // Based on applicant id / applicant_id
                $answers = $this->resource
                    ->search('applicant_answer')
                    ->filterByApplicantId($applicant['applicant_id'])
                    ->getTotal();

                // Checks if there are answers
                if ($answers) {
                    $row['answers'] = '1';
                }
            }

            // Gets the resume for the user
            $resume = $this->resource
                ->search('resume')
                ->setColumns('resume.*')
                ->innerJoinUsing('profile_resume', 'resume_id')
                ->innerJoinUsing('post_resume', 'resume_id')
                ->filterByProfileId($row['profile_id'])
                ->filterByPostId($filter['post_id'])
                ->getRow();

            // Checks if a resume was returned
            if (!empty($resume)) {
                $row = array_merge_recursive($row, $resume);
            }

            $rows[$i] = $row;
        }

        // Checks if the filter for applicant status is set
        if (isset($filter['applicant_status'])) {
            // Loops through the rows
            foreach ($rows as $i => $row) {
                if (!isset($row['applicant_id'])) {
                    unset($rows[$i]);
                    $total--;
                }
            }
        }

        // order by applicant_created
        if (isset($order['applicant_created'])) {
            if ($order['applicant_created'] == 'ASC') {
                array_multisort(array_column($rows, "applicant_created"), SORT_ASC, $rows);
            } else {
                array_multisort(array_column($rows, "applicant_created"), SORT_DESC, $rows);
            }
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $total
        ];
    }

    /**
     * Update to database
     *
     * @param array $data
     *
     * @return array
     */
    public function update(array $data)
    {
        // Sets the columns to be purified
        $columns = array('profile_detail');

        // Checks if there are columns to purifiy
        if (!empty($columns)) {
            // Loops through the columns to be purified
            foreach ($columns as $column) {
                // Checks if the data exists
                if (isset($data[$column])) {
                    // Strip malicious tags using HTMLPurifier
                    $purifier = new HTMLPurifier;
                    $data[$column] = $purifier->purify($data[$column]);
                }
            }
        }

        return $this->resource
            ->model($data)
            ->setProfileUpdated(date('Y-m-d H:i:s'))
            ->save('profile')
            ->get();
    }

    /**
     * Links form
     *
     * @param *int $profilePrimary
     * @param *int $formPrimary
     */
    public function linkForm($profilePrimary, $formPrimary)
    {
        return $this->resource
            ->model()
            ->setProfileId($profilePrimary)
            ->setFormId($formPrimary)
            ->insert('profile_form');
    }
    /**
     * Links label
     *
     * @param *int $profilePrimary
     * @param *int $labelPrimary
     */
    public function linkLabel($profilePrimary, $labelPrimary)
    {
        return $this->resource
            ->model()
            ->setProfileId($profilePrimary)
            ->setLabelId($labelPrimary)
            ->insert('profile_label');
    }

    /**
     * Slugify a given string
     *
     * @param  string $string
     * @return string
     */
    public function slugify($string, $id)
    {
        $slug = preg_replace("/[^a-zA-Z0-9_\-\s]/i", '', $string);
        $slug = str_replace(' ', '-', trim($slug));
        $slug = preg_replace("/-+/i", '-', $slug);
        $slug = strtolower($slug);
        $slug = substr($slug, 0, 90);
        $slug = str_replace('-', ' ', $slug);
        $slug = ucwords($slug);
        $slug = str_replace(' ', '-', $slug);
        $slug = $slug . '-' . 'u' . $id;

        return $slug;
    }

    /**
     * Search companies that have less than 20 credits
     *
     * @param array $data
     * @return array
     */
    public function getInsufficientCreditProfiles(array $data = [])
    {
        $filter = [];
        $likeFilter = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }


        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        if (!isset($filter['profile_active'])) {
            $filter['profile_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['profile_active'] == -1) {
            unset($filter['profile_active']);
        }

        $search = $this->resource
            ->search('profile')
            ->setStart($start)
            ->setRange($range);

        // profile_company filter
        if (isset($filter['type'])) {
            if ($filter['type'] == 'poster') {
                $search->addFilter('(profile_company IS NOT NULL AND profile_company != "") ');
            }

            unset($filter['type']);
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        // get posters less than 20 credits
        if (isset($data['less_credit']) && !empty($data['less_credit'])) {
            $search->addFilter('profile_credits < 20');
        }

        $rows = $search->getRows();

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }

    /**
     * Search database based solely on the filters being passed.
     * Used for searching profiles to update the profile_story.
     *
     * @param $data array
     * @return array
     */
    public function searchByFilters(array $data = [])
    {
        $range = 1000;
        $start = 0;
        $filters = [];

        if (isset($data['range']) && is_numeric($data['range'])) {
            $range = $data['range'];
        }

        $search = $this->resource
            ->search('profile')
            ->setStart($start)
            ->setRange($range);

        // everytime post table is involved
        if (isset($data['post'])) {
            $search
                ->setColumns('DISTINCT(profile.profile_id)')
                ->innerJoinUsing('post_profile', 'profile_id')
                ->innerJoinUsing('post', 'post_id');
        }

        if (isset($data['liked'])) {
            $search
            ->setColumns('DISTINCT(profile.profile_id)')
            ->innerJoinUsing('post_liked', 'profile_id');
        }

        // everytime auth table is involved
        if (isset($data['auth'])) {
            $search
            ->setColumns('DISTINCT(profile_id)')
            ->innerJoinUsing('auth_profile', 'profile_id')
            ->innerJoinUsing('auth', 'auth_id');
        }

        // everytime transaction table is involved
        if (isset($data['transaction'])) {
            $search
            ->setColumns('DISTINCT(profile_id)')
            ->innerJoinUsing('transaction_profile', 'profile_id')
            ->innerJoinUsing('transaction', 'transaction_id');
        }

        // everytime service table is involved
        if (isset($data['service'])) {
            $search
            ->setColumns('DISTINCT(profile_id)')
            ->innerJoinUsing('service_profile', 'profile_id')
            ->innerJoinUsing('service', 'service_id');
        }

        $filters = $data['filter'];

        //add filters
        foreach ($filters as $key => $filter) {
            $search->addFilter($filter);
        }

        $rows = $search->getRows();

        foreach ($rows as $i => $results) {
            //story
            if (isset($results['profile_story']) && !empty($results['profile_story'])) {
                $results['profile_story'] = json_decode($results['profile_story'], true);
            } else {
                $results['profile_story'] = [];
            }

            $rows[$i] = $results;
        }

        //return response format
        return [
            'rows' => $rows,
            'total' => $search->getTotal()
        ];
    }


    /**
     * Get Profile Resume
     *
     * @param *int $id
     *
     * @return array
     */
    public function getProfileResume($id)
    {
        return  $this->resource->search('profile')
            ->setColumns('resume.*')
            ->innerJoinUsing('profile_resume', 'profile_id')
            ->innerJoinUsing('resume', 'resume_id')
            ->filterByProfileId($id)
            ->filterByResumeActive(1)
            ->sortByResumeCreated('DESC')
            ->setRange(1)
            ->getRow();
    }
}
