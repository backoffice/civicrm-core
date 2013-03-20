<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviSeleniumTestCase.php';
class WebTest_Contact_AddressParsingTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function teststreetAddressParsing() {
    // Logging in.
    $this->webtestLogin();

    //Go to the URL of Address Setting to enable street address parsing option
    $this->openCiviPage('admin/setting/preferences/address', 'reset=1');

    //check the street address parsing is already enabled
    if (!$this->isChecked("address_options[13]")) {
      $this->click("address_options[13]");
      $this->click("_qf_Address_next");
      $this->waitForPageToLoad($this->getTimeoutMsec());
    }

    // Go to the URL to create an Individual contact.
    $this->openCiviPage('contact/add', array('reset' => 1, 'ct' => "Individual"));

    //contact details section
    $firstName = "John" . substr(sha1(rand()), 0, 7);
    $lastName = "Smith" . substr(sha1(rand()), 0, 7);

    //fill in first name
    $this->type("first_name", $firstName);

    //fill in last name
    $this->type("last_name", $lastName);

    //address section
    $this->click("addressBlock");
    $this->waitForElementPresent("address_1_street_address");

    //fill in address 1
    $this->click("//div[@id='addressBlockId']/div[1]");
    $this->type("address_1_street_address", "121A Sherman St. Apt. 12");
    $this->type("address_1_city", "Dumfries");
    $this->type("address_1_postal_code", "1234");
    $this->select("address_1_state_province_id", "value=1019");
    $this->type("address_1_geo_code_1", "1234");
    $this->type("address_1_geo_code_2", "5678");

    //fill in address 2
    $this->click("//div[@id='addMoreAddress1']/a/span");
    $this->waitForElementPresent("address_2_street_address");
    $this->type("address_2_street_address", "121 Sherman Street #15");
    $this->type("address_2_city", "Birmingham");
    $this->type("address_2_postal_code", "3456");
    $this->select("address_2_state_province_id", "value=1002");
    $this->type("address_2_geo_code_1", "2678");
    $this->type("address_2_geo_code_2", "1456");

    //fill in address 3
    $this->click("//div[@id='addMoreAddress2']/a/span");
    $this->waitForElementPresent("address_3_street_address");
    $this->type("address_3_street_address", "121 Sherman Rd Unit 155");
    $this->type("address_3_city", "Birmingham");
    $this->type("address_3_postal_code", "3456");
    $this->select("address_3_state_province_id", "value=1002");

    //fill in address 4
    $this->click("//div[@id='addMoreAddress3']/a/span");
    $this->waitForElementPresent("address_4_street_address");
    $this->type("address_4_street_address", "121 SW Sherman Way Suite 15");
    $this->type("address_4_city", "Birmingham");
    $this->type("address_4_postal_code", "5491");
    $this->assertTrue($this->isTextPresent("- select - United States"));
    $this->select("address_4_state_province_id", "value=1002");

    // Store location type of each address
    for ($i = 1; $i <= 4; ++$i) {
      $location[$this->getSelectedLabel("address_{$i}_location_type_id")] = $i;
    }

    // Clicking save.
    $this->click("_qf_Contact_upload_view");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->assertTrue($this->isTextPresent("{$firstName} {$lastName} has been created."));

    //Get the is of newly created contact
    $matches = array();
    preg_match('/id=([0-9]+)/', $this->getLocation(), $matches);
    $contactId = $matches[1];

    //Go to the url of edit contact
    $this->openCiviPage('contact/add', array('reset' => 1, 'action' => 'update', 'cid' => $contactId), 'addressBlock');
    $this->click("addressBlock");
    $this->click("//div[@id='addressBlockId']/div[1]");
    $this->waitForElementPresent("address_1_street_address");
    $this->waitForElementPresent("address_4_street_address");

    // Match addresses by location type since the order may have changed
    for ($i = 1; $i <= 4; ++$i) {
      $address[$i] = $location[$this->getSelectedLabel("address_{$i}_location_type_id")];
      // Open "Edit Address Elements"
      $this->click("//table[@id='address_{$i}']//a[@href='#']");
    }

    //verify all the address fields whether parsed correctly
    $verifyData = array(
      1 => array(
        'street_number' => '121A',
        'street_name' => 'Sherman St.',
        'street_unit' => 'Apt. 12',
      ),
      2 => array(
        'street_number' => '121',
        'street_name' => 'Sherman Street',
        'street_unit' => '#15',
      ),
      3 => array(
        'street_number' => '121',
        'street_name' => 'Sherman Rd',
        'street_unit' => 'Unit 155',
      ),
      4 => array(
        'street_number' => '121',
        'street_name' => 'SW Sherman Way',
        'street_unit' => 'Suite 15',
      )
    );
    foreach ($verifyData as $loc => $values) {
      $num = $address[$loc];
      foreach ($values as $key => $expectedvalue) {
        $actualvalue = $this->getValue("address_{$num}_$key");
        $this->assertEquals($expectedvalue, $actualvalue);
      }
    }
  }
}

