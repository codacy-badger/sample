<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracking\Label\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Label SQL Service
 *
 * @vendor   Acme
 * @package  label
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'label';

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
            ->setLabelCreated(date('Y-m-d H:i:s'))
            ->setLabelUpdated(date('Y-m-d H:i:s'))
            ->save('label')
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
        $search = $this->resource->search('label');
        
        $search->filterByLabelId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['label_custom']) {
            $results['label_custom'] = json_decode($results['label_custom'], true);
        } else {
            $results['label_custom'] = [];
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
    public function getByProfile($id)
    {
        $search = $this->resource
            ->search('label')
            ->innerJoinUsing('profile_label', 'label_id')
            ->innerJoinUsing('profile', 'profile_id');
        
        $search->filterByProfileId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['label_custom']) {
            $results['label_custom'] = json_decode($results['label_custom'], true);
        } else {
            $results['label_custom'] = [];
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
            ->setLabelId($id)
            ->remove('label');
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

        

        
        if (!isset($filter['label_active'])) {
            $filter['label_active'] = 1;
        }
        

        $search = $this->resource
            ->search('label')
            ->setStart($start)
            ->setRange($range);

        //join profile
        $search->innerJoinUsing('profile_label', 'label_id');
        $search->innerJoinUsing('profile', 'profile_id');

         // filter applicant status
        if (isset($filter['label_custom']) &&
            !empty($filter['label_custom'])) {
                $search->addFilter('JSON_SEARCH(LOWER(label_custom),
                    "one" ,"%'.strtolower($filter['label_custom']).'%") IS NOT NULL');

                unset($filter['label_custom']);
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

        foreach($rows as $i => $results) {
            
            if($results['label_custom']) {
                $rows[$i]['label_custom'] = json_decode($results['label_custom'], true);
            } else {
                $rows[$i]['label_custom'] = [];
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
            ->setLabelUpdated(date('Y-m-d H:i:s'))
            ->save('label')
            ->get();
    }
}
