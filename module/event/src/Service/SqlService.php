<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Event\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Event SQL Service
 *
 * @vendor   Acme
 * @package  event
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'event';

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
            ->setEventCreated(date('Y-m-d H:i:s'))
            ->setEventUpdated(date('Y-m-d H:i:s'))
            ->save('event')
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
        $search = $this->resource->search('event');

        $search->innerJoinUsing('event_profile', 'event_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->leftJoinUsing('event_deal', 'event_id');
        $search->leftJoinUsing('deal', 'deal_id');

        $search->filterByEventId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
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
            ->setEventId($id)
            ->remove('event');
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
        $dateType = [
            'start' => 'created',
            'end' => 'created'
        ];

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

        if (isset($data['date_type'])) {
            if (!is_array($data['date_type'])) {
                $dateType = [
                    'start' => sprintf('%s', $data['date_type']),
                    'end' => sprintf('%s', $data['date_type'])
                ];
            } else {
                $dateType = $data['date_type'];
            }
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }



        if (!isset($filter['event_active'])) {
            $filter['event_active'] = 1;
        }


        $search = $this->resource
            ->search('event')
            ->setStart($start)
            ->setRange($range);


        //join deal
        $search->innerJoinUsing('event_profile', 'event_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->leftJoinUsing('event_deal', 'event_id');
        $search->leftJoinUsing('deal', 'deal_id');

        if (isset($data['upcoming'])) {
            $search->addFilter('event_start >= %s', date('Y-m-d H:i:s'));
            $search->addFilter('event_end <= %s', date('Y-m-d', strtotime('+1 week')));
        }

        //date_start
        if (isset($data['date_start']) && $data['date_start']) {
            $date = date('Y-m-d 00:00:00', strtotime($data['date_start']));
            $search->addFilter('event_'.$dateType['start'].' >= "'.$date.'"');
        }

        //date_end
        if (isset($data['date_end']) && $data['date_end']) {
            $date = date('Y-m-d 23:59:59', strtotime($data['date_end']));
            $search->addFilter('event_'.$dateType['end'].' <= "'.$date.'"');
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (trim($value) == '') {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }


        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(event_title) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(event_start) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(event_end) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(event_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                array_unshift($or, '(' . implode(' OR ', $where) . ')');

                call_user_func([$search, 'addFilter'], ...$or);
            }
        }


        //add sorting
        foreach ($order as $sort => $direction) {
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        foreach($rows as $i => $results) {

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
            ->setEventUpdated(date('Y-m-d H:i:s'))
            ->save('event')
            ->get();
    }
    /**
     * Links deal
     *
     * @param *int $eventPrimary
     * @param *int $dealPrimary
     */
    public function linkDeal($eventPrimary, $dealPrimary)
    {
        return $this->resource
            ->model()
            ->setEventId($eventPrimary)
            ->setDealId($dealPrimary)
            ->insert('event_deal');
    }

    /**
     * Unlinks deal
     *
     * @param *int $eventPrimary
     * @param *int $dealPrimary
     */
    public function unlinkDeal($eventPrimary, $dealPrimary)
    {
        return $this->resource
            ->model()
            ->setEventId($eventPrimary)
            ->setDealId($dealPrimary)
            ->remove('event_deal');
    }

    /**
     * Links profile
     *
     * @param *int $eventPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($eventPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setEventId($eventPrimary)
            ->setProfileId($profilePrimary)
            ->insert('event_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $eventPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($eventPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setEventId($eventPrimary)
            ->setProfileId($profilePrimary)
            ->remove('event_profile');
    }

    /**
    * Unlinks All profile
    *
    * @param *int $eventPrimary
    * @param *int $profilePrimary
    */
    public function unlinkAllProfile($eventPrimary)
    {
        return $this->resource
            ->model()
            ->setEventId($eventPrimary)
            ->remove('event_profile');
    }

}
