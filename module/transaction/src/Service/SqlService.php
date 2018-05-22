<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Transaction\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Transaction SQL Service
 *
 * @vendor   Acme
 * @package  transaction
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'transaction';

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
            ->setTransactionCreated(date('Y-m-d H:i:s'))
            ->setTransactionUpdated(date('Y-m-d H:i:s'))
            ->save('transaction')
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
        $search = $this->resource->search('transaction');

        $search->innerJoinUsing('transaction_profile', 'transaction_id');
        $search->innerJoinUsing('profile', 'profile_id');

        $search->filterByTransactionId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['transaction_profile']) {
            $results['transaction_profile'] = json_decode($results['transaction_profile'], true);
        } else {
            $results['transaction_profile'] = [];
        }

        return $results;
    }

    /**
     * Get total transaction credits
     *
     * @param *int $profileId
     *
     * @return int
     */
    public function getTotalCredits($profileId)
    {
        $results = $this
            ->resource
            ->search('transaction')
            ->setColumns('SUM(transaction_credits) as total')
            ->innerJoinUsing('transaction_profile', 'transaction_id')
            ->groupBy('profile_id')
            ->filterByProfileId($profileId)
            ->filterByTransactionActive(1)
            ->addFilter('transaction_status IN (%s, %s, %s)', 'complete', 'verified', 'match')
            ->setRange(1)
            ->getRow();

        if(!$results) {
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
            ->setTransactionId($id)
            ->remove('transaction');
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
        $date = [];
        $groupDate = [];
        $filter = [];
        $filterRange = [];
        $range = 50;
        $start = 0;
        $order = [];
        $count = 0;

        $keywords = null;

        if (isset($data['filter']) && is_array($data['filter'])) {
            $filter = $data['filter'];
        }

        if (isset($data['filter_range']) && is_array($data['filter_range'])) {
            $filterRange = $data['filter_range'];
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

        if (isset($data['date']) && is_array($data['date'])) {
            $date = $data['date'];
        }

        if (isset($data['groupDate']) && is_array($data['groupDate'])) {
            $groupDate = $data['groupDate'];
        }

        if (isset($data['export'])) {
            $range = 0;
        }

        if (isset($data['q'])) {
            $keywords = $data['q'];

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }

        //Checks Transaction Status
        if (isset($filter['transaction_status'])
            && empty($filter['transaction_status'])) {
            unset($filter['transaction_status']);
        }


        if (!isset($filter['transaction_active'])) {
            $filter['transaction_active'] = 1;
        }

        // Checks if active is set to -1
        if ($filter['transaction_active'] == -1) {
            unset($filter['transaction_active']);
        }

        $search = $this->resource
            ->search('transaction')
            ->setStart($start)
            ->setRange($range);

        //join profile
        if (isset($filter['profile_id'])) {
            $search->innerJoinUsing('transaction_profile', 'transaction_id');
            $search->innerJoinUsing('profile', 'profile_id');
        }

        if(isset($_GET['filter']['transaction_active'])
            && ($_GET['filter']['transaction_active'] === '0')) {
                $search
                    ->addFilter('transaction_active = 0');
        }

        if(!isset($_GET['filter']['transaction_active'])
            || $_GET['filter']['transaction_active'] === '') {
                $search
                ->addFilter('transaction_active = 1');
                $filter['transaction_active'] = '1';
        }

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        foreach ($date as $column => $values) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column) &&
                isset($values['start_date']) && isset($values['end_date']) &&
                !empty($values['start_date']) && !empty($values['end_date'])) {
                $search
                    ->addFilter($column . ' >= "'. $values['start_date'].'"')
                    ->addFilter($column . ' <= "'. $values['end_date'].'"');
            }
        }

        foreach ($filterRange as $column => $values) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column) &&
            !empty($values['start']) && !empty($values['end'])) {
                $search
                    ->addFilter($column . ' >= "'. $values['start'].'"')
                    ->addFilter($column . ' <= "'. $values['end'].'"');
            }
        }

        // if user sets a start and end date
        if ((isset($date['start_date']) && isset($date['end_date']))
            && (!empty($date['start_date'])) && (!empty($date['end_date']))){
            $search
                ->addFilter('transaction_created >= '."'". date("Y-m-d 0:00:00",strtotime($date['start_date']))."'")
                ->addFilter('transaction_created <= '."'". date("Y-m-d 23:59:59", strtotime($date['end_date']))."'");
        }

        if ((isset($date['start_date']) && (empty($date['end_date'])))
            && (!empty($date['start_date'])) && (isset($date['end_date']))) {

            $search
                ->addFilter('transaction_created >= '."'". date("Y-m-d 0:00:00",  strtotime($date['start_date']))."'");

        }

        if ((empty($date['start_date']) && (isset($date['end_date'])))
            && (isset($date['start_date'])) && (!empty($date['end_date']))) {
            $search
                ->addFilter('transaction_created <= '."'". date("Y-m-d 23:59:59", strtotime($date['end_date']))."'");
        }

        foreach ($groupDate as $column => $value) {
            $search->addFilter($column . ' LIKE "' . $value . '"');
        }

        // Checks for keywords
        if (isset($keywords)) {
            $or = [];
            $where = [];

            // Loops through the keywords
            foreach ($keywords as $keyword) {
                $where[] = 'LOWER(transaction_payment_reference) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(transaction_profile) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
                $where[] = 'LOWER(transaction_payment_method) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';
            }


            array_unshift($or, '(' . implode(' OR ', $where) . ')');
            call_user_func([$search, 'addFilter'], ...$or);
        }
        // Loops through the sorting
        foreach ($order as $sort => $direction) {
            // Adds sorting
            $sort = ''.$sort.'';
            $search->addSort($sort, $direction);
        }

        $rows = $search->getRows();

        // Loops through the rows
        foreach ($rows as $i => $results) {
            // Checks for transaction profile
            if ($results['transaction_profile']) {
                $rows[$i]['transaction_profile'] = json_decode($results['transaction_profile'], true);
            } else {
                $rows[$i]['transaction_profile'] = [];
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
            ->setTransactionUpdated(date('Y-m-d H:i:s'))
            ->save('transaction')
            ->get();
    }

    /**
     * Links profile
     *
     * @param *int $transactionPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($transactionPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setTransactionId($transactionPrimary)
            ->setProfileId($profilePrimary)
            ->insert('transaction_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $transactionPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($transactionPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setTransactionId($transactionPrimary)
            ->setProfileId($profilePrimary)
            ->remove('transaction_profile');
    }

    /**
     * filter variables to be used in gathering chart data
     *
     * @param array $data
     *
     * @return array
     */
    public function chartFilter(array $data) {
        $where = [];
        //add query filters
        if (isset($data['chartFilter'])) {
            foreach ($data['chartFilter'] as $column => $value) {
                //custom query for custom date filter
                if ($column === 'date') {
                    $where[] = sprintf(
                        'transaction_created BETWEEN \'%s 00:00:00\' AND \'%s 23:59:59\'',
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
     * Select total of transaction credits
     *
     * @param array $data
     *
     * @return array
     */
    public function getChartTotalCredits(array $data) {
        $where = $this->chartFilter($data);

        //we have a different query if the filter is custom date
        if (isset($data['chartFilter']['date'])) {
            $sql = $this->resource->query(
                'SELECT SUM(`transaction_total`) as total,
                date_format(transaction_created, \'%d\') as day,
                date_format(transaction_created, \'%M\') as month,
                YEAR(transaction_created) as year FROM transaction ' . $where .
                ' GROUP BY day, month, year ORDER BY MIN(transaction_created)');

            return $sql;
        }

        //default query (if date is not entered)
        $sql = $this->resource->query(
            'SELECT SUM(`transaction_total`) as total,
            date_format(transaction_created, \'%M\') as month,
            YEAR(transaction_created) as year FROM transaction
            GROUP BY month, year ORDER BY MIN(transaction_created)');

        return $sql;
    }
}
