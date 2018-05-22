<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracer\Education\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Education SQL Service
 *
 * @vendor   Acme
 * @package  education
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'education';

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
            ->setEducationCreated(date('Y-m-d H:i:s'))
            ->setEducationUpdated(date('Y-m-d H:i:s'))
            ->save('education')
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
        $search = $this->resource->search('education');


        $search->filterByEducationId($id);

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
            ->setEducationId($id)
            ->remove('education');
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




        if (!isset($filter['education_active'])) {
            $filter['education_active'] = 1;
        }


        $search = $this->resource
            ->search('education')
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
        if (isset($data['education_to']) ||
            empty($data['education_to'])) {
            $this->updateEducationTo($data['education_id']);
        }

        return $this->resource
            ->model($data)
            ->setEducationUpdated(date('Y-m-d H:i:s'))
            ->save('education')
            ->get();
    }

    /**
     * Update Education to null
     *
     * @param array $data
     *
     * @return array
     */
    public function updateEducationTo($educationId)
    {
        $this->resource->query('UPDATE education SET '
            . 'education_to = null '
            . 'WHERE education_id=:bind0bind', [
                ':bind0bind' => $educationId
            ]);

        return true;
    }

    /**
     * Links information
     *
     * @param *int $educationPrimary
     * @param *int $informationPrimary
     */
    public function linkInformation($educationPrimary, $informationPrimary)
    {
        return $this->resource
            ->model()
            ->setEducationId($educationPrimary)
            ->setInformationId($informationPrimary)
            ->insert('education_information');
    }

    /**
     * Unlinks information
     *
     * @param *int $educationPrimary
     * @param *int $informationPrimary
     */
    public function unlinkInformation($educationPrimary, $informationPrimary)
    {
        return $this->resource
            ->model()
            ->setEducationId($educationPrimary)
            ->setInformationId($informationPrimary)
            ->remove('education_information');
    }

    /**
     * Unlinks All information
     *
     * @param *int $educationPrimary
     * @param *int $informationPrimary
     */
    public function unlinkAllInformation($educationPrimary)
    {
        return $this->resource
            ->model()
            ->setEducationId($educationPrimary)
            ->remove('education_information');
    }

}
