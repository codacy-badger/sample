<?php //-->

/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Service\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Service SQL Service
 *
 * @vendor   Acme
 * @package  service
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'service';

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
            ->setServiceCreated(date('Y-m-d H:i:s'))
            ->setServiceUpdated(date('Y-m-d H:i:s'))
            ->save('service')
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
        $search = $this->resource->search('service');
        $search->innerJoinUsing('service_profile', 'service_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->filterByServiceId($id);
        $results = $search->getRow();
        if (!$results) {
            return $results;
        }
        if ($results['service_meta']) {
            $results['service_meta'] = json_decode($results['service_meta'], true);
        } else {
            $results['service_meta'] = [];
        }

        return $results;
    }

    /**
     * Get total service credits
     *
     * @param *int $profileId
     *
     * @return int
     */
    public function getTotalCredits($profileId)
    {
        $results = $this
            ->resource
            ->search('service')
            ->setColumns('SUM(service_credits) as total')
            ->innerJoinUsing('service_profile', 'service_id')
            ->groupBy('profile_id')
            ->filterByProfileId($profileId)
            ->filterByServiceActive(1)
            ->setRange(1)
            ->getRow();
        if (!$results) {
            return 0;
        }

        return $results['total'];
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
            ->setServiceId($id)
            ->remove('service');
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
        $date      = [];
        $groupDate = [];
        $filter    = [];
        $range     = 50;
        $start     = 0;
        $order     = [];
        $count     = 0;
        $keywords  = NULL;
        $reference = 'null';

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['date']) && is_array($data['date'])) {
            $date = $data['date'];
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
        if (isset($data['groupDate']) && is_array($data['groupDate'])) {
            $groupDate = $data['groupDate'];
        }

        if (!isset($filter['service_active'])) {
            $filter['service_active'] = 1;
        }

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['date']) && is_array($data['date'])) {
            $date = $data['date'];
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

        if (isset($data['groupDate']) && is_array($data['groupDate'])) {
            $groupDate = $data['groupDate'];
        }

        if (!isset($filter['service_active'])) {
            $filter['service_active'] = 1;
        }

        if (isset($filter['service_name'])
            && empty($filter['service_name'])) {
            unset($filter['service_name']);
        }

        // Checks if active is set to -1
        if ($filter['service_active'] == -1) {
            unset($filter['service_active']);
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

        $search = $this->resource
            ->search('service')
            ->setStart($start)
            ->setRange($range);

        //join profile
        $search->innerJoinUsing('service_profile', 'service_id');
        $search->innerJoinUsing('profile', 'profile_id');

        // Checks for date filter
        if (isset($filter['date'])) {
            $date = $filter['date'];
            $date['start_date'] = date('Y-m-d', strtotime($date['start_date'])) . ' 00:00:00';
            $query = '(`service_created` >= "' . $date['start_date'] . '"';
            
            // Checks for end date
            if (isset($date['end_date']) && !empty($date['end_date'])) {
                $date['end_date'] = date('Y-m-d', strtotime($date['end_date'])) . ' 23:59:59';
                $query .= ' AND `service_created` < "' . $date['end_date'] . '"';
            }

            // Close the query
            $query .= ')';

            // Add the custom query
            $search->addFilter($query);

            // Unset the date filter
            unset($filter['date']);
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //add date filters
        foreach ($date as $column => $values) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column) &&
                isset($values['start_date']) && isset($values['end_date']) &&
                !empty($values['start_date']) && !empty($values['end_date'])) {
                $search
                    ->addFilter($column . ' >= "' . $values['start_date'] . '"')
                    ->addFilter($column . ' <= "' . $values['end_date'] . '"');
            }
        }

        // if user sets a start and end date in export
        if ((isset($date['start_date']) && isset($date['end_date']) && isset($data['export']))
            && (!empty($date['start_date'])) && (!empty($date['end_date']))){
            $search
                ->addFilter('service_created >= '."'". date("Y-m-d 0:00:00",strtotime($date['start_date']))."'")
                ->addFilter('service_created <= '."'". date("Y-m-d 23:59:59", strtotime($date['end_date']))."'");
        }

        if ((isset($date['start_date']) && (empty($date['end_date'])))
            && (!empty($date['start_date'])) && (isset($date['end_date']))) {

            $search
                ->addFilter('service_created >= '."'". date("Y-m-d 0:00:00",  strtotime($date['start_date']))."'");

        }

        if ((empty($date['start_date']) && (isset($date['end_date'])))
            && (isset($date['start_date'])) && (!empty($date['end_date']))) {
            $search
                ->addFilter('service_created <= '."'". date("Y-m-d 23:59:59", strtotime($date['end_date']))."'");
        }

        $data['period_covered'] = 'Period Covered';
        $data['service_label']  = 'All Transactions';

        //filter by service_name
        if (isset($data['service_name'])) {
            switch ($data['service_name']) {
                case 'all':
                    $data['service_label'] = 'All Transactions';
                    break;

                case 'download-resume':
                    $data['service_label'] = 'Resume Download';
                    $search
                        ->addFilter("service_name = 'Resume Download'");
                    break;

                case 'promotion':
                    $data['service_label'] = 'Post Promotion';
                    $search
                        ->addFilter("service_name = 'Post Promotion'");
                    break;

                case 'match':
                    $data['service_label'] = 'Match';
                    $search
                        ->addFilter("service_name = 'Match'");
                    break;

                case 'sms-interest':
                    $data['service_label'] = 'Sms Match';
                    $search
                        ->addFilter("service_name = 'Sms Match'");
                    break;

                case 'attach-form':
                    $data['service_label'] = 'Attach Form';
                    $search
                        ->addFilter("service_name = 'Attach Form'");
                    break;

                default:
                    $data['service_label'] = 'All Transactions';
            }
        }

        // new date filter
        if (isset($data['date']['type'])) {

            switch ($data['date']['type']) {

                case 'today':
                    $data['period_covered'] = 'Current Day';
                    $start                  = date('Y-m-d 00:00:00');
                    $end                    = date('Y-m-d 23:59:59');
                    $search
                        ->addFilter('service_created >= ' . "'" . $start . "'")
                        ->addFilter('service_created <= ' . "'" . $end . "'");
                    break;

                case 'last-7':
                    $data['period_covered'] = 'Last 7 Days';
                    $start                  = date('Y-m-d 00:00:00', strtotime('-7 days'));
                    $end                    = date('Y-m-d 23:59:59');
                    $search
                        ->addFilter('service_created >= ' . "'" . $start . "'")
                        ->addFilter('service_created <= ' . "'" . $end . "'");
                    break;

                case 'last-30':
                    $data['period_covered'] = 'Last 30 Days';
                    $start                  = date('Y-m-d 00:00:00', strtotime('-30 days'));
                    $end                    = date('Y-m-d 23:59:59');
                    $search
                        ->addFilter('service_created >= ' . "'" . $start . "'")
                        ->addFilter('service_created <= ' . "'" . $end . "'");
                    break;

                case 'last-q':
                    $data['period_covered'] = 'Last 3 Months';
                    $start                  = date('Y-m-d 00:00:00', strtotime('-3 Months'));
                    $end                    = date('Y-m-d 23:59:59');
                    $search
                        ->addFilter('service_created >= ' . "'" . $start . "'")
                        ->addFilter('service_created <= ' . "'" . $end . "'");
                    break;

                case 'last-6':
                    $data['period_covered'] = 'Last 6 Months';
                    $start                  = date('Y-m-d 00:00:00', strtotime('-6 Months'));
                    $end                    = date('Y-m-d 23:59:59');
                    $search
                        ->addFilter('service_created >= ' . "'" . $start . "'")
                        ->addFilter('service_created <= ' . "'" . $end . "'");
                    break;

                case 'last-year':
                    $data['period_covered'] = 'Last Year';
                    $start                  = date('Y-01-01 00:00:00', strtotime('-1 year'));
                    $end                    = date('Y-12-31 23:59:59', strtotime('-1 year'));
                    $search
                        ->addFilter('service_created >= ' . "'" . $start . "'")
                        ->addFilter('service_created <= ' . "'" . $end . "'");
                    break;

                case 'range':
                    $data['period_covered'] = 'Custom';
                    if (empty($data['date']['end']) &&
                        !empty($data['date']['start'])) {
                        $start = date('Y-m-d 00:00:00', strtotime($date['start']));
                        $end   = date('Y-m-d 23:59:59');

                        $search
                            ->addFilter('service_created >= ' . "'" . $start . "'");
                    }

                    if (empty($data['date']['start']) &&
                        !empty($data['date']['end'])) {
                        $end = date('Y-m-d 23:59:59', strtotime($date['end']));

                        $search
                            ->addFilter('service_created <= ' . "'" . $end . "'");
                    }

                    if (!empty($date['start']) &&
                        !empty($date['end'])) {
                        $start = date('Y-m-d 00:00:00', strtotime($date['start']));
                        $end   = date('Y-m-d 23:59:59', strtotime($date['end']));
                        $search
                            ->addFilter('service_created >= ' . "'" . $start . "'")
                            ->addFilter('service_created <= ' . "'" . $end . "'");
                    }

                    if ((empty($date['start'])) &&
                        (empty($date['end']))) {
                        $end = date('Y-m-d 23:59:59');

                        $search
                            ->addFilter('service_created <= ' . "'" . $end . "'");
                    }
                    break;

                default:
                    $data['period_covered'] = 'Period Covered';
            }
        }

        if (isset($data['q'])) {
            $reference = $data['q'];
            if (!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or      = [];
                $where   = [];
                $where[] = 'LOWER(service_id) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_name) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_email) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(profile_company) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(service_name) LIKE %s';
                $or[]    = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }

        foreach ($groupDate as $column => $value) {
            $search->addFilter($column . ' LIKE "' . $value . '"');
        }

        // Loops through the sorting
        foreach ($order as $sort => $direction) {
            // Default encasement
            $encase = 'TRIM(LOWER(%s))';
            // Checks if we should not encase the sorting
            switch ($sort) {
                case (strpos($sort, '_id') !== false):
                    break;
                case (strpos($sort, '_credits') !== false):
                    break;
                default:
                    $sort = sprintf($encase, $sort);
                    break;
            }
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();
        foreach ($rows as $i => $results) {
            if ($results['service_meta']) {
                $rows[$i]['service_meta'] = json_decode($results['service_meta'], true);
            } else {
                $rows[$i]['service_meta'] = [];
            }
        }

        //return response format
        return [
            'period_covered' => $data['period_covered'],
            'service_label'  => $data['service_label'],
            'rows'           => $rows,
            'total'          => $search->getTotal()
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
            ->setServiceUpdated(date('Y-m-d H:i:s'))
            ->save('service')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $servicePrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($servicePrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setServiceId($servicePrimary)
            ->setProfileId($profilePrimary)
            ->insert('service_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $servicePrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($servicePrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setServiceId($servicePrimary)
            ->setProfileId($profilePrimary)
            ->remove('service_profile');
    }
}