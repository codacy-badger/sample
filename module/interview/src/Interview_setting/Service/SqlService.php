<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Interview\Interview_setting\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Interview_setting SQL Service
 *
 * @vendor   Acme
 * @package  interview_setting
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'interview_setting';

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
            ->setInterviewSettingCreated(date('Y-m-d H:i:s'))
            ->setInterviewSettingUpdated(date('Y-m-d H:i:s'))
            ->save('interview_setting')
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
        $search = $this->resource->search('interview_setting');
        
        $search->innerJoinUsing('interview_setting_profile', 'interview_setting_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->filterByInterviewSettingId($id);

        $results = $search->getRow();

        if (!$results) {
            return $results;
        }

        if (isset($results['interview_setting_meta'])) {
            $results['interview_setting_meta'] = json_decode($results['interview_setting_meta'], true);
        } else {
            $results['interview_setting_meta'] = [];
        }

        $results['slots_taken'] = $this->resource
            ->search('interview_schedule_interview_setting')
            ->filterByInterviewSettingId($results['interview_setting_id'])
            ->getTotal();

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
            ->setInterviewSettingId($id)
            ->remove('interview_setting');
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
        $exactFilter = [];
        $inFilter = [];
        $notFilter = [];
        $columns = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['exact_filter']) && is_array($data['exact_filter'])) {
            $exactFilter = $data['exact_filter'];
        }

        if (isset($data['in_filter']) && is_array($data['in_filter'])) {
            $inFilter = $data['in_filter'];
        }

        if (isset($data['not_filter']) && is_array($data['not_filter'])) {
            $notFilter = $data['not_filter'];
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

        if (!isset($filter['interview_setting_active'])) {
            $filter['interview_setting_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['interview_setting_active'] == -1) {
            unset($filter['interview_setting_active']);
        }

        $search = $this->resource
            ->search('interview_setting')
            ->setStart($start)
            ->setRange($range);

        // Checks for columns
        if (!empty($columns)) {
            // Checks if the columns is an array
            if (is_array($columns)) {
                $columns = implode(',', $columns);
            }

            $search->setColumns($columns);
        }

        //join profile
        $search->innerJoinUsing('interview_setting_profile', 'interview_setting_id');
        $search->innerJoinUsing('profile', 'profile_id');

        // Checks for date filter
        if (isset($filter['dates'])) {
            // Checks if there is an end date
            if (!empty($filter['dates']['end_date'])) {
                $query = '`interview_setting_date` >= "' . $filter['dates']['start_date'] . '"'
                    . ' AND `interview_setting_date` <= "' . $filter['dates']['end_date'] . '"';
                $search->addFilter($query);
            } else {
                $search->filterByInterviewSettingDate($filter['dates']['start_date']);
            }

            // Unset the date filters
            unset($filter['dates']);
        }

        // Checks if we need to filter for dates greater than or equal to today
        if (isset($filter['today'])) {
            // Search for posts where the dates have not yet passed
            $search->addFilter('interview_setting_date >= %s',
                date('Y-m-d',strtotime('now')));

            // Unsets the today filter
            unset($filter['today']);
        }

        // Checks for filters
        if (!empty($filter)) {
            // Loops through the filters
            foreach ($filter as $column => $value) {
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                    $search->addFilter($column . ' = %s', $value);
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

        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        // Checks for grouping
        if (isset($data['group'])) {
            $search->groupBy($data['group']);
        }

        $rows = $search->getRows();
        $return = array();

        foreach ($rows as $i => $row) {
            if (isset($rows[$i]['interview_setting_meta'])) {
                $rows[$i]['interview_setting_meta'] = json_decode($row['interview_setting_meta'], true);
            } else {
                $rows[$i]['interview_setting_meta'] = [];
            }

            // Checks if the total need to be fetched
            if (isset($data['schedule_total'])) {
                $rows[$i]['slots_taken'] = $this->resource
                    ->search('interview_schedule_interview_setting')
                    ->filterByInterviewSettingId($row['interview_setting_id'])
                    ->getTotal();

                // Checks if we need to exlucde unavailable dates
                if (isset($data['exclude_maxed_out']) && $data['exclude_maxed_out']) {
                    if ($rows[$i]['slots_taken'] >= $row['interview_setting_slots']) {
                        unset($rows[$i]);
                    }
                }
            }

            // Checks if we need to get the schedules as well
            if (isset($data['schedule_list'])) {
                // Constructs the date today
                $today = strtotime('now');

                // Limit the columns being fetched
                $columns = array();
                $columns[] = 'interview_schedule.*';
                $columns[] = 'post.post_id';
                $columns[] = 'post.post_position';
                $columns[] = 'post.post_flag';
                $columns[] = 'post.post_notify';
                $columns[] = 'profile.profile_id';
                $columns[] = 'profile.profile_name';
                $columns[] = 'profile.profile_phone';
                $columns[] = 'profile.profile_email';
                $columns[] = 'profile.profile_package';
                $columns = implode(',', $columns);

                $schedules = $this->resource
                    ->search('interview_schedule')
                    ->setColumns($columns)
                    ->innerJoinUsing('interview_schedule_interview_setting', 'interview_schedule_id')
                    ->leftJoinUsing('interview_schedule_profile', 'interview_schedule_id')
                    ->leftJoinUsing('profile', 'profile_id')
                    ->leftJoinUsing('interview_schedule_post', 'interview_schedule_id')
                    ->leftJoinUsing('post', 'post_id')
                    ->filterByInterviewSettingId($row['interview_setting_id']);

                // Checks for date filter
                if (isset($data['date']) && !empty($data['date']['export_start_date']) && !empty($data['date']['export_start_date'])) {
                    // Checks if there is an end date
                    if (!empty($data['date']['export_end_date'])) {
                        $query = '`interview_schedule_date` >= "' . $data['date']['export_start_date'] . '"'
                            . ' AND `interview_schedule_date` <= "' . $data['date']['export_end_date'] . '"';
                        $schedules->addFilter($query);
                    } else {
                        $query = "`interview_schedule_date` BETWEEN '" .  $data['date']['export_start_date'] . "' AND '" 
                            . $data['date']['export_end_date'] . "'";
                        $schedules->addFilter($query);
                    }

                    // Unset the date filters
                    unset($filter['filter']);
                }

                
                $schedules = $schedules->getRows();

                // Checks if there are schedules
                if (!empty($schedules)) {
                    // Loops through the schedule
                    foreach ($schedules as $index => $schedule) {
                        $schedule['interview_schedule_meta'] = json_decode($schedule['interview_schedule_meta']);

                        // Checks if there is no status
                        // Checks if the date has passed
                        if ($today > strtotime($schedule['interview_schedule_date'])
                            && $schedule['interview_schedule_status'] == '') {
                            $schedule['interview_schedule_pending'] = true;
                        }

                        if ($schedule['interview_schedule_type'] == 'anonymous') {
                            foreach ($schedule['interview_schedule_meta'] as $field => $value) {
                                $schedule[$field] = $value;
                            }
                        }

                        $schedules[$index] = $schedule;
                    }
                }

                $rows[$i]['interview_schedule'] = $schedules;
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
            ->setInterviewSettingUpdated(date('Y-m-d H:i:s'))
            ->save('interview_setting')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $interview_settingPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($interview_settingPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewSettingId($interview_settingPrimary)
            ->setProfileId($profilePrimary)
            ->insert('interview_setting_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $interview_settingPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($interview_settingPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setInterviewSettingId($interview_settingPrimary)
            ->setProfileId($profilePrimary)
            ->remove('interview_setting_profile');
    }

}
