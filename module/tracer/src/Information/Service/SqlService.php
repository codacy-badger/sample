<?php //-->
/**
 * This file is part of a Custom Project
 * (c) 2017-2019 Acme Inc
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\Module\Tracer\Information\Service;

use PDO as Resource;
use Cradle\Sql\SqlFactory;

use Cradle\Module\Utility\Service\SqlServiceInterface;
use Cradle\Module\Utility\Service\AbstractSqlService;

/**
 * Information SQL Service
 *
 * @vendor   Acme
 * @package  information
 * @author   John Doe <john@acme.com>
 * @standard PSR-2
 */
class SqlService extends AbstractSqlService implements SqlServiceInterface
{
    /**
     * @const TABLE_NAME
     */
    const TABLE_NAME = 'information';

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
            ->setInformationCreated(date('Y-m-d H:i:s'))
            ->setInformationUpdated(date('Y-m-d H:i:s'))
            ->save('information')
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
        $search = $this->resource->search('information');

        $search->innerJoinUsing('information_profile', 'information_id');
        $search->innerJoinUsing('profile', 'profile_id');

        $search->filterByInformationId($id);

        $results = $search->getRow();

        if(!$results) {
            return $results;
        }

        if($results['information_skills']) {
            $results['information_skills'] = json_decode($results['information_skills'], true);
        } else {
            $results['information_skills'] = [];
        }

        // get the experience
        $results['information_experience'] =  $this->resource->search('experience')
            ->innerJoinUsing('experience_information', 'experience_id')
            ->filterByInformationId($results['information_id'])
            ->filterByExperienceActive(1)
            ->getRows();

        // get the education
        $results['information_education'] =  $this->resource->search('education')
            ->innerJoinUsing('education_information', 'education_id')
            ->filterByInformationId($results['information_id'])
            ->filterByEducationActive(1)
            ->getRows();

        // get the accomplishment
        $results['information_accomplishment'] =  $this->resource->search('accomplishment')
            ->innerJoinUsing('accomplishment_information', 'accomplishment_id')
            ->filterByInformationId($results['information_id'])
            ->filterByAccomplishmentActive(1)
            ->getRows();

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
            ->setInformationId($id)
            ->remove('information');
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




        if (!isset($filter['information_active'])) {
            $filter['information_active'] = 1;
        }


        $search = $this->resource
            ->search('information')
            ->setStart($start)
            ->setRange($range);


        //join profile
        $search->innerJoinUsing('information_profile', 'information_id');
        $search->innerJoinUsing('profile', 'profile_id');


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

            if($results['information_skills']) {
                $rows[$i]['information_skills'] = json_decode($results['information_skills'], true);
            } else {
                $rows[$i]['information_skills'] = [];
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
            ->setInformationUpdated(date('Y-m-d H:i:s'))
            ->save('information')
            ->get();
    }

    /**
     * Get Profile Information
     *
     * @param *int $id
     *
     * @return array
     */
    public function getProfileInformation($id)
    {
        // initalize the progress to 0
        $progress = 0;

        // get the information
        $information =  $this->resource->search('information')
            ->innerJoinUsing('information_profile', 'information_id')
            ->innerJoinUsing('profile', 'profile_id')
            ->filterByProfileId($id)
            ->setRange(1)
            ->getRow();

        // check for information
        if (!$information) {
            return $information;
        }

        // if there's information heading add progress
        if ($information['information_heading']) {
            $progress += 20;
        }

        // check for information skills
        if ($information['information_skills']) {
            // json decode the information skills
            $information['information_skills'] = json_decode($information['information_skills'], true);
        } else {
            $information['information_skills'] = [];
        }

        // if there's information skills add progress
        if ($information['information_skills'] &&
            $information['information_skills'] != '[]') {
            $progress += 20;
        }

        // get the experience
        // Add sorting
        // experience_to - Present should be first
        // Followed by experience_to in a descending order
        // Followed by experience_from in a descending order
        $experience =  $this->resource->search('experience')
            ->innerJoinUsing('experience_information', 'experience_id')
            ->filterByInformationId($information['information_id'])
            ->filterByExperienceActive(1)
            ->addSort('experience_to IS NULL', 'DESC')
            ->addSort('experience_to', 'DESC')
            ->addSort('experience_from', 'DESC')
            ->getRows();

        // cradle()->inspect($experience);exit;

        // if there's experience add progress
        if ($experience) {
            $progress += 20;
        }

        // merge the experience to information
        $information['information_experience'] = $experience;

        // get the education
        // Add sorting
        // education_to - Present should be first
        // Followed by education_to in a descending order
        // Followed by education_from in a descending order
        $education =  $this->resource->search('education')
            ->innerJoinUsing('education_information', 'education_id')
            ->filterByInformationId($information['information_id'])
            ->filterByEducationActive(1)
            ->addSort('education_to IS NULL', 'DESC')
            ->addSort('education_to', 'DESC')
            ->addSort('education_from', 'DESC')
            ->getRows();

        // if there's education add progress
        if ($education) {
            $progress += 20;
        }

        // merge the education to information
        $information['information_education'] = $education;

        // get the accomplishment
        // Add sorting
        // Latest accomplishment_to in a descending order
        // Followed by accomplishment_from in a descending order
        $accomplishment =  $this->resource->search('accomplishment')
            ->innerJoinUsing('accomplishment_information', 'accomplishment_id')
            ->filterByInformationId($information['information_id'])
            ->filterByAccomplishmentActive(1)
            ->addSort('accomplishment_to', 'DESC')
            ->addSort('accomplishment_from', 'DESC')
            ->getRows();

        // if there's accomplishment add progress
        if ($accomplishment) {
            $progress += 20;
        }

        // merge the accomplishment to information
        $information['information_accomplishment'] = $accomplishment;

        // merge the progress to information
        $information['information_progress'] = $progress;

        // return the information
        return $information;
    }

    /**
     * Add like count
     *
     * @param *int $information
     * @param *int $profile
     *
     * @return bool
     */
    public function addDownload($information, $profile)
    {
        if ($this->alreadyDownloaded($information, $profile)) {
            return false;
        }

        $this->resource
            ->model()
            ->setInformationId($information)
            ->setProfileId($profile)
            ->insert('information_downloaded');

        $this->resource->query('UPDATE information SET '
        . 'information_download_count = information_download_count + 1 '
        . 'WHERE information_id=:bind0bind', [
            ':bind0bind' => $information
        ]);

        return true;
    }

    /**
     * Already downloaded
     *
     * @param *int $information
     * @param *int $profile
     *
     * @return bool
     */
    public function alreadyDownloaded($information, $profile)
    {
        return !!$this->resource
            ->search('information_downloaded')
            ->filterByInformationId($information)
            ->filterByProfileId($profile)
            ->getRow();
    }

    /**
     * Get User Information Downloaded
     *
     * @param *string $profile
     *
     * @return bool
     */
    public function getUserInformationDownloaded($profile)
    {
        return $this->resource
            ->search('information_downloaded')
            ->filterByProfileId($profile)
            ->getTotal();
    }

    /**
     * Links profile
     *
     * @param *int $informationPrimary
     * @param *int $profilePrimary
     */
    public function linkProfile($informationPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setInformationId($informationPrimary)
            ->setProfileId($profilePrimary)
            ->insert('information_profile');
    }

    /**
     * Unlinks profile
     *
     * @param *int $informationPrimary
     * @param *int $profilePrimary
     */
    public function unlinkProfile($informationPrimary, $profilePrimary)
    {
        return $this->resource
            ->model()
            ->setInformationId($informationPrimary)
            ->setProfileId($profilePrimary)
            ->remove('information_profile');
    }

    /**
     * Search tracer in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchTracer(array $data = [])
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

        if (!isset($filter['information_active'])) {
            $filter['information_active'] = 1;
        }

        $search = $this->resource
            ->search('information')
            ->setStart($start)
            ->setRange($range);

        // query for year employed
        $search->innerJoinUsing('education_information', 'information_id');
        $search->innerJoinUsing('education', 'education_id');
        $search->innerJoinUsing('experience_information', 'information_id');
        $search->innerJoinUsing('experience', 'experience_id');
        $search->innerJoinUsing('information_profile', 'information_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->addFilter('education_to LIKE "' . '%' . $filter['education_to'] . '%' . '"');
        $search->addFilter('experience_to LIKE "' . '%' . $filter['experience_to'] . '%' . '"');
        $search->addFilter('education_active = "1"');
        $search->addFilter('experience_active = "1"');

        // set employed data
        $results['employed'] = $search->getRows();
        $total['employed'] = $search->getTotal();

        $search = $this->resource
            ->search('information')
            ->setStart($start)
            ->setRange($range);

        // query for year unemployed (get all graduates on year then exclude the graduate that employed)
        $search->innerJoinUsing('education_information', 'information_id');
        $search->innerJoinUsing('education', 'education_id');
        $search->innerJoinUsing('information_profile', 'information_id');
        $search->innerJoinUsing('profile', 'profile_id');
        $search->innerJoinUsing('experience_information', 'information_id');
        $search->innerJoinUsing('experience', 'experience_id');
        $search->addFilter('education_to LIKE "' . '%' . $filter['education_to'] . '%' . '"');
        // $search->addFilter('experience_to NOT LIKE "' . '%' . $filter['experience_to'] . '%' . '"' . 'OR experience_to = ""');
        $search->addFilter('education_active = "1"');
        $search->addFilter('experience_active = "1"');

        // get all employed profile id
        foreach ($results['employed'] as $key => $value) {
            // add filter
            $search->addFilter('profile_id != "' . $value['profile_id'] . '"');
        }

        // set unemployed data
        $results['unemployed'] = $search->getRows();
        $total['unemployed'] = $search->getTotal();


        // set rows data
        $rows = $results;


        //return response format
        return [
            'rows' => $rows,
            'total' => $total
        ];
    }

    /**
     * Search Tracer Civil Status in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchCivilStatus(array $data = [])
    {
        $results = [];
        $totalSingle = $totalMarried = 0;
        $search = $this->resource
            ->search('information')
            ->innerJoinUsing('education_information', 'information_id')
            ->innerJoinUsing('education', 'education_id')
            ->addFilter('(YEAR(education_to) BETWEEN "'. $data['start_year'] .'" AND NOW())')
            ->filterByEducationActive(1);

        // filter by school
        if (isset($data['education_school'])) {
            $search->filterByEducationSchool($data['education_school']);
        }

        $infomations = $search->getRows();

        // get civil status percentage
        foreach ($infomations as $key => $information) {
            $year = date('Y', strtotime($information['education_to']));
            if (strtolower($information['information_civil_status']) == 'single') {
                $results[$year]['total_single'] = isset($results[$year]['total_single']) ? $results[$year]['total_single'] + 1 : 1;
            } else if (strtolower($information['information_civil_status']) == 'married') {
                $results[$year]['total_married'] = isset($results[$year]['total_married']) ? $results[$year]['total_married'] + 1 : 1;
            }
        }

        // get current year
        $currentYear = date('Y');
        if (isset($data['year'])) {
            $currentYear = $data['year'];
        }

        // set years
        $years = [];
        for ($i = 0; $i < 4; $i++) {
            $years[] = $currentYear - $i;
        }

        // set civil status per year
        foreach ($years as $key => $year) {
            $results['civil_status'][$year]['year'] = $year;
            $results['civil_status'][$year]['total_single'] = isset($results[$year]['total_single']) ?  $results[$year]['total_single'] : 0;
            $results['civil_status'][$year]['total_married'] = isset($results[$year]['total_married']) ?  $results[$year]['total_married'] : 0;
        }

        // compute percentage
        foreach ($results['civil_status'] as $key => $value) {

            $results['civil_status'][$key]['single_percentage'] = 0;
            $results['civil_status'][$key]['married_percentage'] = 0;
            if ($value['total_single'] != 0 || $value['total_married'] != 0) {
                $results['civil_status'][$key]['single_percentage'] = number_format(($value['total_single'] / ($value['total_single'] + $value['total_married'])) * 100, 2);

                $results['civil_status'][$key]['married_percentage'] = number_format(($value['total_married'] / ($value['total_single'] + $value['total_married'])) * 100, 2);
            }

            // remove current year
            if ($value['year'] == $currentYear) {
                $results['civil_status_current'] = $results['civil_status'][$currentYear];
                unset($results['civil_status'][$currentYear]);
            }
        }

        return $results;
    }

    /**
     * Search Tracer Job Related in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchJobRelated(array $data = [])
    {
        $results = [];

        $search = $this->resource
            ->search('information')
            ->innerJoinUsing('education_information', 'information_id')
            ->innerJoinUsing('education', 'education_id')
            ->addFilter('(YEAR(education_to) BETWEEN "'. $data['start_year'] .'" AND NOW())')
            ->filterByEducationActive(1);

        // filter by school
        if (isset($data['education_school'])) {
            $search->filterByEducationSchool($data['education_school']);
        }

        $rows = $search->getRows();
        $informations = [];
        foreach ($rows as $key => $value) {
            $informations[$value['information_id']] = $value;
        }

        $infoIds = [];
        foreach ($informations as $value) {
            $infoIds[] = $value['information_id'];
        }

        if (!$informations) {
            return [];
        }

        $experience = $this->resource
            ->search('information')
            ->innerJoinUsing('experience_information', 'information_id')
            ->innerJoinUsing('experience', 'experience_id')
            ->addFilter('information_id IN ('.implode(',', $infoIds).')')
            ->getRows();

        foreach ($experience as $key => $value) {
            $informations[$value['information_id']]['experience'][] = $value;
        }

        // get job related percentage
        foreach ($informations as $key => $information) {
            $year = date('Y', strtotime($information['education_to']));
            if (isset($information['experience'])) {
                foreach ($information['experience'] as $experience) {
                    if (strtolower($experience['experience_related']) == 'yes') {
                        $results[$year]['total_yes'] = isset($results[$year]['total_yes']) ? $results[$year]['total_yes'] + 1 : 1;
                    } else {
                        $results[$year]['total_no'] = isset($results[$year]['total_no']) ? $results[$year]['total_no'] + 1: 1;
                    }
                }
            }
        }

        $currentYear = date('Y');
        if (isset($data['year'])) {
            $currentYear = $data['year'];
        }
        // set years
        $years = [];
        for ($i = 0; $i < 4; $i++) {
            $years[] = $currentYear - $i;
        }

        // set civil status per year
        foreach ($years as $key => $year) {
            $results['job_related'][$year]['year'] = $year;
            $results['job_related'][$year]['total_yes'] = isset($results[$year]['total_yes']) ?  $results[$year]['total_yes'] : 0;
            $results['job_related'][$year]['total_no'] = isset($results[$year]['total_no']) ?  $results[$year]['total_no'] : 0;
        }

        // compute percentage
        foreach ($results['job_related'] as $key => $value) {
            $results['job_related'][$key]['yes_percentage'] = 0;
            $results['job_related'][$key]['no_percentage'] = 0;

            if ($value['total_yes'] == 0
                && $value['total_no'] == 0) {
                 $results['job_related'][$key]['yes_percentage'] = 0;
                $results['job_related'][$key]['no_percentage'] = 0;
            } else {
                $results['job_related'][$key]['yes_percentage'] = number_format(($value['total_yes'] / ($value['total_yes'] + $value['total_no'])) * 100, 2);
                $results['job_related'][$key]['no_percentage'] = number_format(($value['total_no'] / ($value['total_yes'] + $value['total_no'])) * 100, 2);
            }
            // remove current year
            if ($value['year'] == $currentYear) {
                $results['job_related_current'] = $results['job_related'][$currentYear];
                unset($results['job_related'][$currentYear]);
            }
        }

        return $results;
    }

     /**
     * Search Tracer Employment Rate in database
     *
     * @param array $data
     *
     * @return array
     */
    public function searchEmploymentRate(array $data = [])
    {
        $results = [];

        $search = $this->resource
            ->search('information')
            ->innerJoinUsing('education_information', 'information_id')
            ->innerJoinUsing('education', 'education_id')
            ->addFilter('(YEAR(education_to) BETWEEN "'. $data['start_year'] .'" AND NOW())')
            ->filterByEducationActive(1);

        // filter by school
        if (isset($data['education_school'])) {
            $search->filterByEducationSchool($data['education_school']);
        }

        $rows = $search->getRows();
        $informations = [];
        foreach ($rows as $key => $value) {
            $informations[$value['information_id']] = $value;
        }

        $infoIds = [];
        foreach ($informations as $value) {
            $infoIds[] = $value['information_id'];
        }

        if (!$informations) {
            return [];
        }

        $experience = $this->resource
            ->search('information')
            ->innerJoinUsing('experience_information', 'information_id')
            ->innerJoinUsing('experience', 'experience_id')
            ->addFilter('(YEAR(experience_to) BETWEEN "'. $data['start_year'] .'" AND NOW())')
            ->addFilter('information_id IN ('.implode(',', $infoIds).')')
            ->getRows();

        foreach ($experience as $key => $value) {
            $informations[$value['information_id']]['experience'][] = $value;
        }

        // get job related percentage
        foreach ($informations as $key => $information) {
            $year = date('Y', strtotime($information['education_to']));
            if (isset($information['experience'])
                && !empty($information['experience'])) {
                     $results[$year]['total_employed'] = isset($results[$year]['total_employed']) ? $results[$year]['total_employed'] + 1 : 1;
            } else {

                 $results[$year]['total_not_employed'] = isset($results[$year]['total_not_employed']) ? $results[$year]['total_not_employed'] + 1: 1;
            }
        }

        // get current year
        $currentYear = date('Y');
        if (isset($data['year'])) {
            $currentYear = $data['year'];
        }

        // set years
        $years = [];
        for ($i = 0; $i < 4; $i++) {
            $years[] = $currentYear - $i;
        }

        // set civil status per year
        foreach ($years as $key => $year) {
            $results['employment_rate'][$year]['year'] = $year;
            $results['employment_rate'][$year]['total_employed'] = isset($results[$year]['total_employed']) ?  $results[$year]['total_employed'] : 0;
            $results['employment_rate'][$year]['total_not_employed'] = isset($results[$year]['total_not_employed']) ?  $results[$year]['total_not_employed'] : 0;
        }

        // compute percentage
        foreach ($results['employment_rate'] as $key => $value) {
            $results['employment_rate'][$key]['employed_percentage'] = 0;
            $results['employment_rate'][$key]['not_employed_percentage'] = 0;

            if ($value['total_employed'] == 0
                && $value['total_not_employed'] == 0) {
                $results['employment_rate'][$key]['employed_percentage'] = 0;
                $results['employment_rate'][$key]['not_employed_percentage'] = 0;
            } else {
                $results['employment_rate'][$key]['employed_percentage'] = number_format(@($value['total_employed'] / ($value['total_employed'] + $value['total_not_employed'])) * 100, 2);
                $results['employment_rate'][$key]['not_employed_percentage'] = number_format(@($value['total_not_employed'] / ($value['total_employed'] + $value['total_not_employed'])) * 100, 2);
            }

            // remove current year
            if ($value['year'] == $currentYear) {
                $results['employment_rate_current'] = $results['employment_rate'][$currentYear];
                unset($results['employment_rate'][$currentYear]);
            }
        }

        return $results;
    }
}
