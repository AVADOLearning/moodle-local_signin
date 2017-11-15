<?php

/**
 * Local signin - Domain discovery method
 *
 * @author Stefan Liute
 * @author Jonathan Shad
 * @copyright 2017 AVADO Limited
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once "{$CFG->dirroot}/lib/externallib.php";
require_once "{$CFG->dirroot}/local/signin/classes/external.php";
require_once "{$CFG->dirroot}/local/signin/classes/model/user_default_domain.php";

use local_brandmanager\model\brand;
use local_signin\external;
use local_signin\model\user_default_domain;

class external_test extends advanced_testcase {
    protected $brand;
    protected $user_01;
    protected $user_02;
    protected $webservice;

    const COHORT_TABLE         = 'cohort';
    const USER_TABLE           = 'user';
    const COHORT_MEMBERS_TABLE = 'cohort_members';
    const BRAND_COHORTS_TABLE  = 'brandmanager_brand_cohort';
    const BRAND_DOMAINS_TABLE  = 'brandmanager_brand_domain';
    const DOMAIN_NAME_01       = 'test.domain.net';
    const DEFAULT_DOMAIN_VALUE = 1;

    // The setUp() template method is run once for each test method
    protected function setUp() {
        global $DB, $CFG;

        $this->resetAfterTest();
        $CFG->authloginviaemail = true;

        // Create test brand.
        $this->brand = new brand(null, 'test_brand', time(), time(), 0, 0, false);
        $this->brand->save();
        $brandid = $this->brand->id;

        // Create test cohort.
        $datagenerator = $this->getDataGenerator();
        $cohort = $datagenerator->create_cohort();
        $cohortid = $DB->get_field(static::COHORT_TABLE, 'id', ['name' => $cohort->name]);

        // Create two users.
        $this->user_01 = $datagenerator->create_user();
        $this->user_02 = $datagenerator->create_user();

        // Make first user a cohort member
        $cohortmember = new stdClass();
        $cohortmember->cohortid = $cohortid;
        $cohortmember->userid = $DB->get_field(static::USER_TABLE, 'id', ['username' => $this->user_01->username]);
        $DB->insert_record(static::COHORT_MEMBERS_TABLE, $cohortmember);

        // Associate the cohort to the brand.
        $brandcohort = new stdClass();
        $brandcohort->brandid = $brandid;
        $brandcohort->cohortid = $cohortid;
        $DB->insert_record(static::BRAND_COHORTS_TABLE, $brandcohort);

        // Give the brand a default domain.
        $branddomain = new stdClass();
        $branddomain->brandid = $brandid;
        $branddomain->domain = static::DOMAIN_NAME_01;
        $branddomain->defaultdomain = static::DEFAULT_DOMAIN_VALUE;
        $DB->insert_record(static::BRAND_DOMAINS_TABLE, $branddomain);

        // Set necessary configuration parameters.
        set_config('defaultwwwroot', 'campus.avadolearning.com', 'bmdisco_domain');
        set_config('local_signin_userdomain', 'bmdisco_domain\user_domain');
    }

    /**
     * A non-existent username should remain on the same domain.
     *
     * @return void
     */
    public function test_check_domain_with_nonexistent_user() {
        global $CFG;
        $responsefornonexistentusername = external::check_domain('alfred_the_great');
        $expectedresponse = new user_default_domain(null, null, parse_url($CFG->wwwroot, PHP_URL_HOST));
        $this->assertEquals($expectedresponse, $responsefornonexistentusername);
    }

    /**
     * A user with no cohort should go to a default domain.
     *
     * @return void
     */
    public function test_username_check_domain_with_no_cohort_user() {
        $responsefornocohortusername = external::check_domain($this->user_02->username);
        $expectedresponse = new user_default_domain(
            $this->user_02->username,
            $this->user_02->email,
            get_config('bmdisco_domain','defaultwwwroot')
        );
        $this->assertEquals($expectedresponse, $responsefornocohortusername);
    }

    /**
     * An email with no cohort should go to a default domain.
     *
     * @return void
     */
    public function test_email_check_domain_with_no_cohort_user() {
        $responsefornocohortusername = external::check_domain($this->user_02->email);
        $expectedresponse = new user_default_domain(
            $this->user_02->username,
            $this->user_02->email,
            get_config('bmdisco_domain','defaultwwwroot')
        );
        $this->assertEquals($expectedresponse, $responsefornocohortusername);
    }

    /**
     * A user with a cohort associated to a brand should go to that brand's default domain.
     *
     * @return void
     */
    public function test_username_check_domain_with_cohort_user() {
        $responseforcohortusername = external::check_domain($this->user_01->username);
        $expectedresponse = new user_default_domain(
            $this->user_01->username,
            $this->user_01->email,
            static::DOMAIN_NAME_01
        );
        $this->assertEquals($expectedresponse, $responseforcohortusername);
    }

    /**
     * An email with a cohort associated to a brand should go to that brand's default domain.
     *
     * @return void
     */
    public function test_email_check_domain_with_cohort_user() {
        $responseforcohortusername = external::check_domain($this->user_01->email);
        $expectedresponse = new user_default_domain(
            $this->user_01->username,
            $this->user_01->email,
            static::DOMAIN_NAME_01
        );
        $this->assertEquals($expectedresponse, $responseforcohortusername);
    }
}
