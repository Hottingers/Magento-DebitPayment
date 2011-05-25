<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @package    Mage_Debit
 * @copyright  2011 ITABS GmbH / Rouven Alexander Rieker (http://www.itabs.de)
 * @copyright  2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Debit_Model_Debit extends Mage_Payment_Model_Method_Abstract
{
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'debit';

    /**
     * payment form block
     * 
     * @var string MODULE/BLOCKNAME
     */
    protected $_formBlockType = 'debit/form';

    /**
     * payment info block
     * 
     * @var string MODULE/BLOCKNAME
     */
    protected $_infoBlockType = 'debit/info';

    /**
     * assignData
     * 
     * Assigns data to the payment info instance
     * 
     * @param Varien_Object|array $data
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();

        // Fetch routing number
        $ccType = $data->getDebitCcType();
        if (!$ccType) {
            $ccType = $data->getCcType();
        }
        $ccType = Mage::helper('debit')->sanitizeData($ccType);
        $ccType = $info->encrypt($ccType);

        // Fetch account holder
        $ccOwner = $data->getDebitCcOwner();
        if (!$ccOwner) {
            $ccOwner = $data->getCcOwner();
        }

        // Fetch account number
        $ccNumber = $data->getDebitCcNumber();
        if (!$ccNumber) {
            $ccNumber = $data->getCcNumber();
        }
        $ccNumber = Mage::helper('debit')->sanitizeData($ccNumber);
        $ccNumber = $info->encrypt($ccNumber);

        // Set account data in payment info model
        $info->setCcType($ccType)                     // BLZ
             ->setCcOwner($ccOwner)                   // Kontoinhaber
             ->setCcNumberEnc($ccNumber);             // Kontonummer

        return $this;
    }

    /**
     * getCustomText
     * 
     * Returns the custom text for this payment method
     * 
     * @return string Custom text
     */
    public function getCustomText()
    {
        return $this->getConfigData('customtext');
    }

    /**
     * getAccountName
     * 
     * Returns the account name from the payment info instance
     * 
     * @return string Name
     */
    public function getAccountName()
    {
        $info = $this->getInfoInstance();
        return $info->getCcOwner();
    }

    /**
     * getAccountNumber
     * 
     * Returns the account number from the payment info instance
     * 
     * @return string Number
     */
    public function getAccountNumber()
    {
        $info = $this->getInfoInstance();
        $data = $info->getCcNumberEnc();
        
        if(!is_numeric($data)) {
            $data = $info->decrypt($data);
        }
        if(!is_numeric($data)) {
            $data = $info->decrypt($data);
        }
        
        return $data;
    }

    /**
     * getAccountBLZ
     * 
     * Returns the account blz from the payment info instance
     * 
     * @return string BLZ
     */
    public function getAccountBLZ()
    {
        $info = $this->getInfoInstance();
        $data = $info->getCcType();
        
        if(!is_numeric($data)) {
            $data = $info->decrypt($data);
        }
        
        return $data;
    }

    /**
     * getAccountBankname
     * 
     * Returns the account bankname if applicable from the payment info instance
     * 
     * @return string Bankname/Error
     */
    public function getAccountBankname()
    {
        $bankName = Mage::helper('debit')->getBankByBlz($this->getAccountBLZ());
        if ($bankName == null) {
            $bankName = Mage::helper('debit')->__('not available');
        }
        return $bankName;
    }

    /**
     * maskString
     * 
     * Returns the encrypted data for mail
     *
     * @param string $data Data to crypt
     * 
     * @return string Crypted data
     */
    public function maskString($data)
    {
        $crypt = str_repeat('*', strlen($data)-3) . substr($data,-3);
        return $crypt;
    }
}