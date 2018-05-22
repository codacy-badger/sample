<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Ses\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Ses SQL Service
 *
 * @vendor   Acme
 * @package  ses
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'ses';

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
            ->setSesCreated(date('Y-m-d H:i:s'))
            ->setSesUpdated(date('Y-m-d H:i:s'))
            ->save('ses')
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
        $search = $this->resource->search('ses');
        
        $search->filterBySesId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['ses_emails']) {
            $results['ses_emails'] = json_decode($results['ses_emails'], true);
        } else {
            $results['ses_emails'] = [];
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
            ->setSesId($id)
            ->remove('ses');
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

            if(!is_array($keywords)) {
                $keywords = [$keywords];
            }
        }
        

        
        if (!isset($filter['ses_active'])) {
            $filter['ses_active'] = 1;
        }
        

        $search = $this->resource
            ->search('ses')
            ->setStart($start)
            ->setRange($range);

        

        //add filters
        foreach ($filter as $column => $value) {
            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        
        //keyword?
        if (isset($keywords)) {
            foreach ($keywords as $keyword) {
                $or = [];
                $where = [];
                $where[] = 'LOWER(ses_message) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(ses_link) LIKE %s';
                $or[] = '%' . strtolower($keyword) . '%';$where[] = 'LOWER(ses_type) LIKE %s';
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
            
            if($results['ses_emails']) {
                $rows[$i]['ses_emails'] = json_decode($results['ses_emails'], true);
            } else {
                $rows[$i]['ses_emails'] = [];
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
            ->setSesUpdated(date('Y-m-d H:i:s'))
            ->save('ses')
            ->get();
    }

    /**
     * Get SES to database
     * 
     * @param $campaignMessageID
     * @return array
     */
    public function getByMessage($campaignMessageID) 
    {
        //TODO:
        return $this->resource
            ->query('SELECT ses_link, '
            .'count(distinct ses_emails) as ses_count, sum(ses_total) as ses_total'
            .' FROM ses WHERE ses_type = "click" AND '
            .'ses_message = "'.$campaignMessageID.'" group by ses_link;'
        );
    }
}