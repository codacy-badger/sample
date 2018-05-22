<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Lead\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Lead SQL Service
 *
 * @vendor   Acme
 * @package  lead
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'lead';

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
            ->setLeadCreated(date('Y-m-d H:i:s'))
            ->setLeadUpdated(date('Y-m-d H:i:s'))
            ->save('leads')
            ->get();
    }

    /**
     * Get detail from database
     *
     * @param *int | *string $id
     *
     * @return array
     */
    public function get($id)
    {
        $search = $this->resource->search('leads');

        if (is_numeric($id)) {
            $search->filterByLeadId($id);
        } else {
            $search->filterByLeadEmail($id);
        }

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['lead_tags']) {
            $results['lead_tags'] = json_decode($results['lead_tags'], true);
        } else {
            $results['lead_tags'] = [];
        }

        if($results['lead_campaigns']) {
            $results['lead_campaigns'] = json_decode($results['lead_campaigns'], true);
        } else {
            $results['lead_campaigns'] = [];
        }

        $deal = $this->resource->search('deal')
            ->leftJoinUsing('deal_company', 'deal_id')
            ->filterByProfileId($id)
            ->filterByDealType('lead')
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
            ->setLeadId($id)
            ->remove('leads');
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
        $dateType = 'created';

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
            $dateType = $data['date_type'];
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        if (!isset($filter['lead_active'])) {
            $filter['lead_active'] = 1;
        }

        if (isset($data['export'])) {
            $range = 0;
        }

        $search = $this->resource
            ->search('leads')
            ->setStart($start)
            ->setRange($range);

        if (isset($data['sales'])) {
            $search
                ->innerJoinOn('deal_company', 'profile_id = lead_id')
                ->innerJoinUsing('deal', 'deal_id')
                ->filterByDealType('lead');
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        //date_start
        if (isset($data['date_start'])) {
            $date = date('Y-m-d 00:00:00', strtotime($data['date_start']));
            $search->addFilter('lead_'.$dateType.' >= "'.$date.'"');
        }

        //date_end
        if (isset($data['date_end'])) {
            $date = date('Y-m-d 23:59:59', strtotime($data['date_end']));
            $search->addFilter('lead_'.$dateType.' <= "'.$date.'"');
        }

        //lead_tags
        if (isset($data['lead_tags']) && !empty($data['lead_tags'])) {
            if (!is_array($data['lead_tags'])) {
                $data['lead_tags'] = [$data['lead_tags']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['lead_tags'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(lead_tags), 'one', %s) IS NOT NULL";
                $or[] = '%' . strtolower($tag) . '%';

            }

            // Implode the tags into an OR statement
            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }

        //lead_campaigns
        if (isset($data['lead_campaigns']) && !empty($data['lead_campaigns'])) {
            if (!is_array($data['lead_campaigns'])) {
                $data['lead_campaigns'] = [$data['lead_campaigns']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['lead_campaigns'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(lead_campaigns), 'one', %s) IS NOT NULL";
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
                $where[] = 'LOWER(lead_name) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(lead_email) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(lead_type) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(lead_gender) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(lead_phone) LIKE %s';
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

            if($results['lead_tags']) {
                $rows[$i]['lead_tags'] = json_decode($results['lead_tags'], true);
            } else {
                $rows[$i]['lead_tags'] = [];
            }

            if($results['lead_campaigns']) {
                $rows[$i]['lead_campaigns'] = json_decode($results['lead_campaigns'], true);
            } else {
                $rows[$i]['lead_campaigns'] = [];
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
            ->setLeadUpdated(date('Y-m-d H:i:s'))
            ->save('leads')
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
    public function exists($email, $password = false, $id = false)
    {
        $search = $this->resource
            ->search('leads')
            ->filterByLeadEmail($email);

        if ($id) {
            $search->addFilter('lead_id != %s', $id);
        }

        return !!$search->getRow();
    }

    /**
     * Search count the database
     *
     * @param array $data
     * @return *int
     */
    public function isUserExistByEmail($data) {
        $search = $this->resource->search('leads');

        // $search->filterByLeadId($id);
        $search->filterByLeadEmail($data['profile_email']);

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
    public function bulkAction(array $ids, $value, $field = 'active')
    {
        //please rely on SQL CASCADING ON DELETE
        $fields = ['lead_'.$field => $value];
        $filter = ['lead_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('leads', $fields, $filter);
    }
}
