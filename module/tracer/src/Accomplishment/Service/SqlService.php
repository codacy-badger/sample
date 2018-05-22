<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracer\Accomplishment\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Accomplishment SQL Service
 *
 * @vendor   Acme
 * @package  accomplishment
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'accomplishment';

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
            ->setAccomplishmentCreated(date('Y-m-d H:i:s'))
            ->setAccomplishmentUpdated(date('Y-m-d H:i:s'))
            ->save('accomplishment')
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
        $search = $this->resource->search('accomplishment');
        
        
        $search->filterByAccomplishmentId($id);

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
            ->setAccomplishmentId($id)
            ->remove('accomplishment');
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

        

        
        if (!isset($filter['accomplishment_active'])) {
            $filter['accomplishment_active'] = 1;
        }
        

        $search = $this->resource
            ->search('accomplishment')
            ->setStart($start)
            ->setRange($range);

        
        

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
            ->setAccomplishmentUpdated(date('Y-m-d H:i:s'))
            ->save('accomplishment')
            ->get();
    }

    /**
     * Links information
     *
     * @param *int $accomplishmentPrimary
     * @param *int $informationPrimary
     */
    public function linkInformation($accomplishmentPrimary, $informationPrimary)
    {
        return $this->resource
            ->model()
            ->setAccomplishmentId($accomplishmentPrimary)
            ->setInformationId($informationPrimary)
            ->insert('accomplishment_information');
    }

    /**
     * Unlinks information
     *
     * @param *int $accomplishmentPrimary
     * @param *int $informationPrimary
     */
    public function unlinkInformation($accomplishmentPrimary, $informationPrimary)
    {
        return $this->resource
            ->model()
            ->setAccomplishmentId($accomplishmentPrimary)
            ->setInformationId($informationPrimary)
            ->remove('accomplishment_information');
    }

    /**
     * Unlinks All information
     *
     * @param *int $accomplishmentPrimary
     * @param *int $informationPrimary
     */
    public function unlinkAllInformation($accomplishmentPrimary)
    {
        return $this->resource
            ->model()
            ->setAccomplishmentId($accomplishmentPrimary)
            ->remove('accomplishment_information');
    }
    
}
