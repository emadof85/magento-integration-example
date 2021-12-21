<?php
/**
 * Cart product add after Observer
 *
 * @category  Supplitynetsuite
 * @package   Supplitynetsuite_Accountintegration
 * @author    Emad Kerhily
 * @copyright Copyright (c) 2018 Supplity Co (https://supplity.com)
 */

namespace Supplitynetsuite\Accountintegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Framework\Message\ManagerInterface;
use Magento\Checkout\Model\Session;
use Supplitynetsuite\Accountintegration\Util;

class CustomerRegisterSuccess implements ObserverInterface
{
    protected $_customerRepositoryInterface;
	protected $_logger;

    public function __construct(\Psr\Log\LoggerInterface $logger,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface)
	{
		$this->_logger = $logger;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
    }
    /**
     * Customer Register Success event
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {	 
        $customer = $observer->getEvent()->getCustomer();
		$magentoid = $customer->getId();
		$email = $customer->getEmail();
		$fname = $customer->getFirstname();
		$lname = $customer->getLastname();
		$mname = $customer->getMiddlename();
		$addresses = $customer->getAddresses();
		$taxvat = $customer->getTaxvat();
		$groupid = $customer->getGroupId();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$CustomerModel = $objectManager->create('Magento\Customer\Model\Customer');
		$customerGroupCollection = $objectManager->create('Magento\Customer\Model\Group');
		$CustomerModel->setWebsiteId($customer->getWebsiteId()); //Here 1 means Store ID**
		$CustomerModel->loadByEmail($email);
		$password = $CustomerModel->getPasswordHash();
		$phone="";
		$company="";
		if($CustomerModel->getDefaultShippingAddress()){
			$phone = $CustomerModel->getDefaultShippingAddress()->getTelephone(); // For Telephone
			$company = $CustomerModel->getDefaultShippingAddress()->getCompany(); // For Company
		}
		$collection = $customerGroupCollection->load($groupid); 
        $group = $collection->getCustomerGroupCode();//Get current customer group name
		
		$addressArray = [];
		$i=1;
		$defaulShippingAddress = $CustomerModel->getDefaultShippingAddress();
		$defaultBillingAddress = $CustomerModel->getDefaultBillingAddress();
		if($defaulShippingAddress){
			$city = $defaulShippingAddress->getCity(); // For City
			$country = $defaulShippingAddress->getCountry(); // For Country
		}
		$a = [ "magentoid"=>$magentoid,
			  "firstname"=>$fname,
			  "middlename"=>($mname==null)?"":$mname,
			  "lastname"=>$lname,
			  "companyname"=>$company, 
			  "email"=>$email, 
			  "phone"=>$phone, 
			  "address"=>$addressArray,
			  "customergroup"=>$group,
			  "taxnumber"=>($taxvat==null)?"":$taxvat];
			  
		$data_string = json_encode($a);
		// saved in var/log/debug.log 
		$logger = $objectManager->create('\Psr\Log\LoggerInterface');
		
		$util = new Util();
		$util.sendToNetSuite($data_string,"POST","https://*******.restlets.api.netsuite.com/app/site/hosting/restlet.nl");
	
		$this->_logger->info("JSON Customer Data Registered: ".$data_string, []);
		$this->_logger->info("Response: ".$response, []);
    }
}
