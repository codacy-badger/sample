<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Campaign\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Campaign SQL Service
 *
 * @vendor   Acme
 * @package  campaign
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'campaign';

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
            ->setCampaignCreated(date('Y-m-d H:i:s'))
            ->setCampaignUpdated(date('Y-m-d H:i:s'))
            ->save('campaign')
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
        $search = $this->resource->search('campaign');

        $search->innerJoinUsing('campaign_template', 'campaign_id');
        $search->innerJoinUsing('template', 'template_id');

        $search->filterByCampaignId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['campaign_tags']) {
            $results['campaign_tags'] = json_decode($results['campaign_tags'], true);
        } else {
            $results['campaign_tags'] = [];
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
            ->setCampaignId($id)
            ->remove('campaign');
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

        if (isset($data['export'])) {
            $range = 0;
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

        if (!isset($filter['campaign_active'])) {
            $filter['campaign_active'] = 1;
        }

        $search = $this->resource
            ->search('campaign')
            ->setStart($start)
            ->setRange($range);

        //join template
        $search->innerJoinUsing('campaign_template', 'campaign_id');
        $search->innerJoinUsing('template', 'template_id');

        //add filters
        foreach ($filter as $column => $value) {
            if ($column != 'campaign_active' && empty($value)) {
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9-_]+$/', $column)) {
                $search->addFilter($column . ' = %s', $value);
            }
        }

        if (isset($data['date_start']) && $data['date_start']) {
            $date = date('Y-m-d 00:00:00', strtotime($data['date_start']));
            $search->addFilter('campaign_'.$dateType.' >= "'.$date.'"');
        }

        if (isset($data['date_end']) && $data['date_end']) {
            $date = date('Y-m-d 23:59:59', strtotime($data['date_end']));
            $search->addFilter('campaign_'.$dateType.' <= "'.$date.'"');
        }

        if (isset($data['in_queue']) && $data['in_queue']) {
            $search->addFilter('campaign_queue != "0"');
        }

        // campaign_tags
        if (isset($data['campaign_tags']) && !empty($data['campaign_tags'])) {
            if (!is_array($data['campaign_tags'])) {
                $data['campaign_tags'] = [$data['campaign_tags']];
            }

            // Variable declaration
            $or = [];
            $where = [];

            // Loops through the tags
            foreach ($data['campaign_tags'] as $tag) {
                $where[] = "JSON_SEARCH(LOWER(campaign_tags), 'one', %s) IS NOT NULL";
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
                $where[] = 'LOWER(campaign_title) LIKE %s';
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

            if ($results['campaign_tags']) {
                $rows[$i]['campaign_tags'] = json_decode($results['campaign_tags'], true);
            } else {
                $rows[$i]['campaign_tags'] = [];
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
            ->setCampaignUpdated(date('Y-m-d H:i:s'))
            ->save('campaign')
            ->get();
    }

    /**
     * Links template
     *
     * @param *int $campaignPrimary
     * @param *int $templatePrimary
     */
    public function linkTemplate($campaignPrimary, $templatePrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->setTemplateId($templatePrimary)
            ->insert('campaign_template');
    }

    /**
     * Unlinks template
     *
     * @param *int $campaignPrimary
     * @param *int $templatePrimary
     */
    public function unlinkTemplate($campaignPrimary, $templatePrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->setTemplateId($templatePrimary)
            ->remove('campaign_template');
    }


    /**
     * Links lead
     *
     * @param *int $campaignPrimary
     * @param *int $leadPrimary
     */
    public function linkLead($campaignPrimary, $leadPrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->setLeadId($leadPrimary)
            ->insert('campaign_lead');
    }

    /**
     * Unlinks lead
     *
     * @param *int $campaignPrimary
     * @param *int $leadPrimary
     */
    public function unlinkLead($campaignPrimary, $leadPrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->setLeadId($leadPrimary)
            ->remove('campaign_lead');
    }

    /**
     * Unlinks All lead
     *
     * @param *int $campaignPrimary
     * @param *int $leadPrimary
     */
    public function unlinkAllLead($campaignPrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->remove('campaign_lead');
    }


    /**
     * Links profile
     *
     * @param *int $campaignPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($campaignPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->setProfileId($profilePrimary)
            ->insert('campaign_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $campaignPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($campaignPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->setProfileId($profilePrimary)
            ->remove('campaign_profile');
    }

    /**
     * Unlinks All profile
     *
     * @param *int $campaignPrimary
     * @param *int $profilePrimary
     */
    public function unlinkAllProfile($campaignPrimary)
    {
        return $this->resource
            ->model()
            ->setCampaignId($campaignPrimary)
            ->remove('campaign_profile');
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
        $fields = ['campaign_'.$field => $value];
        $filter = ['campaign_id IN ('.implode(',', $ids).')'];

        return $this->resource
            ->updateRows('campaign', $fields, $filter);
    }
}
