<?php

class ZoneTest extends SapphireTest{
	
	static $fixture_file = array(
		'shop/tests/fixtures/Zones.yml',
		'shop/tests/fixtures/Addresses.yml'
	);
	
	function testMatchingZones(){
		$this->assertZoneMatch($this->objFromFixture("Address","wnz6012"), "TransTasman");
		$this->assertZoneMatch($this->objFromFixture("Address","wnz6012"), "Local");
		$this->assertZoneMatch($this->objFromFixture("Address","sau5024"), "TransTasman");
		$this->assertZoneMatch($this->objFromFixture("Address","sau5024"), "Special");
		$this->assertZoneMatch($this->objFromFixture("Address","scn266033"), "Asia");
		$this->assertNoZoneMatch($this->objFromFixture("Address","zch1234"));
		//TODO: test match specificity, ie state matches should come before country matches, but not postcode matches
	}
	
	function assertZoneMatch($address, $zonename){
		$zones = Zone::get_zones_for_address($address);
		$this->assertNotNull($zones);
		$this->assertDOSContains(array(
			array('Name' => $zonename)
		), $zones);
	}
	
	function assertNoZoneMatch($address){
		$zones = Zone::get_zones_for_address($address);
		$this->assertFalse($zones->exists(), "No zones exist");
	}
	
}