<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracer\Experience\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Experience SQL Service
 *
 * @vendor   Acme
 * @package  experience
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'experience';

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
            ->setExperienceCreated(date('Y-m-d H:i:s'))
            ->setExperienceUpdated(date('Y-m-d H:i:s'))
            ->save('experience')
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
        $search = $this->resource->search('experience');


        $search->filterByExperienceId($id);

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
            ->setExperienceId($id)
            ->remove('experience');
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




        if (!isset($filter['experience_active'])) {
            $filter['experience_active'] = 1;
        }


        $search = $this->resource
            ->search('experience')
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
        if (isset($data['experience_to']) ||
            empty($data['experience_to'])) {
            $this->updateExperienceTo($data['experience_id']);
        }

        return $this->resource
            ->model($data)
            ->setExperienceUpdated(date('Y-m-d H:i:s'))
            ->save('experience')
            ->get();
    }

    /**
     * Update Experience to null
     *
     * @param array $data
     *
     * @return array
     */
    public function updateExperienceTo($experienceId)
    {
        $this->resource->query('UPDATE experience SET '
            . 'experience_to = null '
            . 'WHERE experience_id=:bind0bind', [
                ':bind0bind' => $experienceId
            ]);

        return true;
    }

    /**
     * Links information
     *
     * @param *int $experiencePrimary
     * @param *int $informationPrimary
     */
    public function linkInformation($experiencePrimary, $informationPrimary)
    {
        return $this->resource
            ->model()
            ->setExperienceId($experiencePrimary)
            ->setInformationId($informationPrimary)
            ->insert('experience_information');
    }

    /**
     * Unlinks information
     *
     * @param *int $experiencePrimary
     * @param *int $informationPrimary
     */
    public function unlinkInformation($experiencePrimary, $informationPrimary)
    {
        return $this->resource
            ->model()
            ->setExperienceId($experiencePrimary)
            ->setInformationId($informationPrimary)
            ->remove('experience_information');
    }

    /**
     * Unlinks All information
     *
     * @param *int $experiencePrimary
     * @param *int $informationPrimary
     */
    public function unlinkAllInformation($experiencePrimary)
    {
        return $this->resource
            ->model()
            ->setExperienceId($experiencePrimary)
            ->remove('experience_information');
    }

}
