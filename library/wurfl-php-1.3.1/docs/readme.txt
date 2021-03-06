Getting Started
=======================

1) Download a release archive from wurfl site and extract it to a directory 
   suitable for your application


To start using the api you need to set some configuration options.


For the impatient ones
====================================
Please look sample of the configuration files in examples/resources directory .

define("WURFL_DIR", 	dirname(__FILE__) . '/../../../WURFL/'); // WURFL INSTALLATION DIR
define("RESOURCES_DIR", dirname(__FILE__) . "/../../resources/"); // DIRECTORY WHERE YOU PUT YOUR CONFIGURATION FILES

require_once WURFL_DIR. 'WURFLManagerFactory.php';

$wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
$wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfigFile);

$wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);

$wurflManager = $wurflManagerFactory->create();

Now you can use some of the WURFLManager class methods;

$device = $wurflManager->getDeviceForHttpRequest();

$device->getCapability("capabilityName");


1) Create a configuration from configuration file / programaticaly
 
	
	a) set the paths to the location of the main wurfl  and patch files
		- you can put a compressed(zip) files if you have the 
		  zip module enabled
	
	b) Configure the Persistance provider by specifying the provider and 
		and the extra parameters needed to initialize the provider:
		The Api support the following caching mechanism
			- Memcache (http://uk2.php.net/memcache)	
			- APC(Alternative PHP Cahce http://uk3.php.net/apc)
			- File
		
		remember that if you want to use the first 2 implementaions you need to 
		install and load the relative modules.
	
		  
	c) Configure the Cache provider by specifying the provider and 
		and the extra parameters needed to initialize the provider:
		The Api support the following caching mechanism
			- Memcache (http://uk2.php.net/memcache)	
			- APC(Alternative PHP Cahce http://uk3.php.net/apc)
			- EAccelerator(http://eaccelerator.net/)
			- File
			- Null 
		
		remember that if you want to use the first 3 mechanisms you need to 
		install and load the relative modules.
		Please refer to the links for further information how to install and enable 
		the modules.
		
		
		1.1 From Configuration FILE(Xml File)
		====================================
			$wurflConfigFile = RESOURCES_DIR . 'wurfl-config.xml';
			$wurflConfig = new WURFL_Configuration_XmlConfig($wurflConfig);

		
		1.2 Programtically
		================================
    	 	$config = new WURFL_Configuration_InMemoryConfig();
			$config->wurflFile($resourcesDir ."/wurfl-regression.xml")
				->wurflPatch($resourcesDir ."/web_browsers_patch.xml")
				->persistence("memcache", array("host"=> "127.0.0.1", "port"=>"11211"));

2) 
	- Add  a require_once to  'WURFL_Installation/WURFL/WURFLManagerFactory.php'; 
		// Use the $wurflConfig in step 1
		$wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
	

2.1 Getting the device
===========================

	You have Four methods for retrieving a device 
		a) getDeviceForRequest(WURFL_Request_GenericRequest $request)
			where a WURFL_Request_GenericRequest is an object which encapsulates 
			-userAgent, ua-profile, support for xhtml of the device
		
		b) getDeviceForHttpRequest($_SERVER)
			Most of the time you will use this method, and the api will create the 
			the  WURFL_Request_GenericRequest instance for you
			
		c) getDeviceForUserAgent(string $userAgent)
			
	 	d) getDevice(string $deviceID)
		
		
		e.g
			$device = $wurflManger->getDeviceForUserAgent($userAgent);

2.2 Getting the device properties and its capabiliites
===================================================

- To get the properites of the device like device id, userAgent, fallBack..
	is simple as 
	
	$deviceID = $device->id;
	$fallBack = $device->fallBack;
	
- To get the capability value 
	$capValue = $device->getCapabilityValue("capabilityName");
	$allCapabilities = $device->getAllCapabilities();
		

2.3) Useful Methods		
====================================
- In WURFL_WURFLManager you will find a bunch of utility methods like:
	- getListOfGroups() 
		which returns an array of all groups id found in the wurfl file.
		
		
		
TESTS
==================

There are a bunch of test in tests dir


The tests are for checking that the matching algorithms are working properly

As tests data provider we use the file "unit-test.yml" in tests/resources directory.

PHPUnit
================
			
