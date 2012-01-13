<?php
/**
 * Pop PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://www.popphp.org/LICENSE.TXT
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@popphp.org so we can send you a copy immediately.
 *
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Payment\Adapter;

use Pop\Curl\Curl,
    Pop\Locale\Locale;

/**
 * @category   Pop
 * @package    Pop_Payment
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2012 Moc 10 Media, LLC. (http://www.moc10media.com)
 * @license    http://www.popphp.org/LICENSE.TXT     New BSD License
 * @version    0.9
 */
class TrustCommerce extends AbstractAdapter
{

    /**
     * Customer ID
     * @var string
     */
    protected $_custId = null;

    /**
     * Password
     * @var string
     */
    protected $_password = null;

    /**
     * URL
     * @var string
     */
    protected $_url = 'https://vault.trustcommerce.com/trans/';

    /**
     * Transaction data
     * @var array
     */
    protected $_transaction = array(
        'custid'           => null,
        'password'         => null,
        'action'           => 'sale',
        'cc'               => null,
        'amount'           => null,
        'exp'              => null,
        'cvv'              => null,
        'checkcvv'         => 'n',
        'avs'              => 'n',
        'transid'          => null,
        'avs'              => null,
        'fname'            => null,
        'lname'            => null,
        'address1'         => null,
        'city'             => null,
        'state'            => null,
        'zip'              => null,
        'country'          => null,
        'phone'            => null,
        'email'            => null,
        'ip'               => null,
        'shipto_fname'     => null,
        'shipto_lname'     => null,
        'shipto_address1'  => null,
        'shipto_city'      => null,
        'shipto_state'     => null,
        'shipto_zip'       => null,
        'shipto_country'   => null,
        'tax'              => null,
        'duty'             => null,
        'shippinghandling' => null,
        'partialauth'      => null
    );

    /**
     * Transaction fields for normalization purposes
     * @var array
     */
    protected $_fields = array(
        'amount'          => 'amount',
        'cardNum'         => 'cc',
        'expDate'         => 'exp',
        'ccv'             => 'cvv',
        'firstName'       => 'fname',
        'lastName'        => 'lname',
        'address'         => 'address1',
        'city'            => 'city',
        'state'           => 'state',
        'zip'             => 'zip',
        'country'         => 'country',
        'phone'           => 'phone',
        'fax'             => 'fax',
        'email'           => 'email',
        'shipToFirstName' => 'shipto_fname',
        'shipToLastName'  => 'shipto_lname',
        'shipToAddress'   => 'shipto_address1',
        'shipToCity'      => 'shipto_city',
        'shipToState'     => 'shipto_state',
        'shipToZip'       => 'shipto_zip',
        'shipToCountry'   => 'shipto_country'
    );

    /**
     * Required fields
     * @var array
     */
    protected $_requiredFields = array(
        'custid',
        'password',
        'action',
        'cc',
        'exp',
        'amount'
    );

    /**
     * Constructor
     *
     * Method to instantiate an TrustCommerce payment adapter object
     *
     * @param  string  $custId
     * @param  string  $password
     * @param  boolean $test
     * @return void
     */
    public function __construct($custId, $password, $test = false)
    {
        $this->_custId = $custId;
        $this->_password = $password;
        $this->_transaction['custid'] = $custId;
        $this->_transaction['password'] = $password;
        $this->_test = $test;
    }
    /**
     * Send transaction
     *
     * @param  boolean $verifyPeer
     * @throws Exception
     * @return Pop\Payment\Adapter\Authorize
     */
    public function send($verifyPeer = true)
    {
        if (!$this->_validate()) {
            throw new Exception(Locale::factory()->__('The required transaction data has not been set.'));
        }

        $this->_transaction['demo'] = ($this->_test) ? 'y' : 'n';

        $options = array(
            CURLOPT_URL            => $this->_url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $this->_buildPostString(),
            CURLOPT_HEADER         => false,
            CURLOPT_RETURNTRANSFER => true
        );

        if (!$verifyPeer) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        }

        $curl = new Curl($options);
        $this->_response = $curl->execute();
        $this->_responseCodes = $this->_parseResponseCodes();
        $this->_responseCode = $this->_responseCodes['transid'];
        $this->_message = $this->_responseCodes['status'];

        switch ($this->_responseCodes['status']) {
            case 'approved':
                $this->_approved = true;
                break;
            case 'decline':
                $this->_declined = true;
                break;
            case 'error':
                $this->_error = true;
                break;
        }
    }

    /**
     * Build the POST string
     *
     * @return string
     */
    protected function _buildPostString()
    {
        $post = $this->_transaction;

        $post['cc'] = $this->_filterCardNum($post['cc']);
        $post['exp'] = $this->_filterExpDate($post['exp']);
        $post['amount'] = str_replace('.', '', $post['amount']);

        if ((null !== $post['fname']) && (null !== $post['lname'])) {
            $post['name'] =  $post['fname'] . ' ' . $post['lname'];
            unset($post['fname']);
            unset($post['lname']);
        }

        if ((null !== $post['shipto_fname']) && (null !== $post['shipto_lname'])) {
            $post['shipto_name'] =  $post['shipto_fname'] . ' ' . $post['shipto_lname'];
            unset($post['shipto_fname']);
            unset($post['shipto_lname']);
        }

        return http_build_query($post);
    }

    /**
     * Parse the response codes
     *
     * @return void
     */
    protected function _parseResponseCodes()
    {
        $responseCodes = explode(PHP_EOL, $this->_response);
        $codes = array();

        foreach ($responseCodes as $key => $value) {
            $value = trim($value);
            $valueAry = explode('=', $value);
            $codes[$valueAry[0]] = (!empty($valueAry[1])) ? $valueAry[1] : null;
        }

        return $codes;
    }

}