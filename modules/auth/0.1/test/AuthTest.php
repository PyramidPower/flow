<?php
// $Id$
// (c) 2010 Pyramid Power, Australia

require_once 'PHPUnit/Framework.php';
 
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
 
class AuthTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp() {
        $this->setBrowser('*firefox');
        $this->setBrowserUrl('http://flow.pyramidlocal.com.au/');
    }
 
    public function testTitle() {
        $this->open('http://flow.pyramidlocal.com.au/');
        $this->assertTitle('Pyramid Power Flow Log In');
    }
    
    public function testSuccessfulLoginLogout() {
        $this->open('http://flow.pyramidlocal.com.au/');
		$this->type("user_login","salesrep");
		$this->type("user_pass","salesrep");
		$this->click("wp-submit");
		$this->waitForPageToLoad("30000");
		$this->assertTrue($this->isTextPresent("Hi, Sales!"),"Login failed!");
		$this->click("link=Logout");
		$this->waitForPageToLoad("30000");
		$this->assertTitle('Pyramid Power Flow Log In');
    }
    
    
}
?>