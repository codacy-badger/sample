<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Interview\Interview_schedule\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Interview_schedule SQL Service
 *
 * @vendor   Acme
 * @package  interview_schedule
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'interview_schedule';

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
     * Create in database
     *
     * @param array $data
     *
     * @return array
     */
    public function create(array $data)
    {   
        return $this->resource
            ->model($data)
            ->setInterviewScheduleCreated(date('Y-m-d H:i:s'))
            ->setInterviewScheduleUpdated(date('Y-m-d H:i:s'))
            ->save('interview_schedule')
            ->get();
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
        $search = $this->resource->search('interview_schedule');
        
        $search->leftJoinUsing('interview_schedule_interview_setting', 'interview_schedule_id');
        $search->leftJoinUsing('interview_setting', 'interview_setting_id');
        $search->leftJoinUsing('interview_schedule_profile', 'interview_schedule_id');
        $search->leftJoinUsing('profile', 'profile_id');
        $search->leftJoinUsing('interview_schedule_post', 'interview_schedule_id');
        $search->leftJoinUsing('post', 'post_id');
        
        $search->filterByInterviewScheduleId($id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        if (isset($results['interview_schedule_meta'])) {
            $results['interview_schedule_meta'] = json_decode($results['interview_schedule_meta'], true);
        } else {
            $results['interview_schedule_meta'] = [];
        }

        if (isset($results['post_geo_location'])) {
            $results['post_geo_location'] = json_decode($results['post_geo_location'], true);
        } else {
            $results['post_geo_location'] = [];
        }

        if (isset($results['post_notify'])) {
            $results['post_notify'] = json_decode($results['post_notify'], true);
        } else {
            $results['post_notify'] = [];
        }

        if (isset($results['post_tags'])) {
            $results['post_tags'] = json_decode($results['post_tags'], true);
        } else {
            $results['post_tags'] = [];
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
            ->setInterviewScheduleId($id)
            ->remove('interview_schedule');
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
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;
        
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

        if (!isset($filter['interview_schedule_active'])) {
            $filter['interview_schedule_active'] = 1;
        }

        $search = $this->resource
            ->search('interview_schedule')
            ->setStart($start)
            ->setRange($range);

        //join interview_setting
        $search->leftJoinUsing('interview_schedule_interview_setting', 'interview_schedule_id');
        $search->leftJoinUsing('interview_setting', 'interview_setting_id');
        //join profile
        $search->leftJoinUsing('interview_schedule_profile', 'interview_schedule_id');
        $search->leftJoinUsing('profile', 'profile_id');
        //join post
        $search->leftJoinUsing('interview_schedule_post', 'interview_schedule_id');
        $search->leftJoinUsing('post', 'post_id');

        // Checks for succeeding interview flag
        if (isset($filter['succeeding'])) {
            unset($filter['succeeding']);

            $search
                ->addFilter("NOT (interview_schedule_status <=> 'Interviewed')")
                ->addFilter("NOT (interview_schedule_status <=> 'No Show')")
                ->addFilter("NOT (interview_schedule_status <=> 'Follow Up Interview')");
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach ($rows as $i => $row) {
            if (isset($row['interview_schedule_meta'])) {
                $rows[$i]['interview_schedule_meta'] = json_decode($row['interview_schedule_meta'], true);
            } else {
                $rows[$i]['interview_schedule_meta'] = [];
            }
            
            if (isset($row['post_geo_location'])) {
                $rows[$i]['post_geo_location'] = json_decode($row['post_geo_location'], true);
            } else {
                $rows[$i]['post_geo_location'] = [];
            }
            
            if (isset($row['post_notify'])) {
                $rows[$i]['post_notify'] = json_decode($row['post_notify'], true);
            } else {
                $rows[$i]['post_notify'] = [];
            }
            
            if (isset($row['post_tags'])) {
                $rows[$i]['post_tags'] = json_decode($row['post_tags'], true);
            } else {
                $rows[$i]['post_tags'] = [];
            }

            if ($row['interview_schedule_type'] == 'anonymous') {
                foreach ($row['interview_schedule_meta'] as $field => $value) {
                    $rows[$i][$field] = $value;
                }
            }
        }

        //return response format
        return [
            'rows' => $rows,
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
    public function update(array $data)
    {
        return $this->resource
            ->model($data)
            ->setInterviewScheduleUpdated(date('Y-m-d H:i:s'))
            ->save('interview_schedule')
            ->get();
    }

    /**
     * Gets the interview schedule, post, and profiels
     *
     * @param array $data
     *
     * @return array
     */
    public function getPostProfiles($data = [])
    {
        return $this->resource
            ->search('interview_schedule')
            ->innerJoinUsing('interview_schedule_profile', 'interview_schedule_id')
            ->innerJoinUsing('interview_schedule_post', 'interview_schedule_id')
            ->filterByPostId($data['post_id'])
            ->addFilter("NOT (interview_schedule_status <=> 'Interviewed')")
            ->addFilter("NOT (interview_schedule_status <=> 'No Show')")
            ->addFilter("NOT (interview_schedule_status <=> 'Follow Up Interview')")
            ->getRows();
    }

    /**
     * Links interview_setting
     *
     * @param *int $interview_schedulePrimary
     * @param *int $interview_settingPrimary
     */
    public function linkInterviewSetting($interview_schedulePrimary, $interview_settingPrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewScheduleId($interview_schedulePrimary)
            ->setInterviewSettingId($interview_settingPrimary)
            ->insert('interview_schedule_interview_setting');
    }

    /**
     * Unlinks interview_setting
     *
     * @param *int $interview_schedulePrimary
     * @param *int $interview_settingPrimary
     */
    public function unlinkInterviewSetting($interview_schedulePrimary, $interview_settingPrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewScheduleId($interview_schedulePrimary)
            ->setInterviewSettingId($interview_settingPrimary)
            ->remove('interview_schedule_interview_setting');
    }

    /**
     * Links profile
     *
     * @param *int $interview_schedulePrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($interview_schedulePrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewScheduleId($interview_schedulePrimary)
            ->setProfileId($profilePrimary)
            ->insert('interview_schedule_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $interview_schedulePrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($interview_schedulePrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewScheduleId($interview_schedulePrimary)
            ->setProfileId($profilePrimary)
            ->remove('interview_schedule_profile');
    }

    /**
     * Links post
     *
     * @param *int $interview_schedulePrimary
     * @param *int $postPrimary
     */
    public function linkPost($interview_schedulePrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewScheduleId($interview_schedulePrimary)
            ->setPostId($postPrimary)
            ->insert('interview_schedule_post');
    }

    /**
     * Unlinks post
     *
     * @param *int $interview_schedulePrimary
     * @param *int $postPrimary
     */
    public function unlinkPost($interview_schedulePrimary, $postPrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewScheduleId($interview_schedulePrimary)
            ->setPostId($postPrimary)
            ->remove('interview_schedule_post');
    }
}
