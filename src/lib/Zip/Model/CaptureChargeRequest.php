<?php
/**
 * CaptureChargeRequest
 *
 * @package Zip
 * @author  Zip Co - Plugin Team
 */


namespace Zip\Model;

use \ArrayAccess;

class CaptureChargeRequest implements ArrayAccess
{
    const DISCRIMINATOR = 'subclass';

    /**
      * The original name of the model.
      * @var string
      */
    protected static $swaggerModelName = 'CaptureChargeRequest';

    /**
      * Array of property to type mappings. Used for (de)serialization
      * @var string[]
      */
    protected static $zipTypes = array(
        'amount' => 'float',
        'is_partial_capture' => 'boolean'
    );

    public static function zipTypes()
    {
        return self::$zipTypes;
    }

    /**
     * Array of attributes where the key is the local name, and the value is the original name
     * @var string[]
     */
    protected static $attributeMap = array(
        'amount' => 'amount',
        'is_partial_capture' => 'is_partial_capture'
    );


    /**
     * Array of attributes to setter functions (for deserialization of responses)
     * @var string[]
     */
    protected static $setters = array(
        'amount' => 'setAmount',
        'is_partial_capture' => 'setPartialCapture'
    );


    /**
     * Array of attributes to getter functions (for serialization of requests)
     * @var string[]
     */
    protected static $getters = array(
        'amount' => 'getAmount',
        'is_partial_capture' => 'isPartialCapture'
    );

    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    public static function setters()
    {
        return self::$setters;
    }

    public static function getters()
    {
        return self::$getters;
    }


    /**
     * Associative array for storing property values
     * @var mixed[]
     */
    protected $container = array();

    /**
     * Constructor
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['amount'] = isset($data['amount']) ? $data['amount'] : null;
        $this->container['is_partial_capture'] = isset($data['is_partial_capture']) ? $data['is_partial_capture'] : null;
    }

    /**
     * show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalid_properties = array();

        if ($this->container['amount'] === null) {
            $invalid_properties[] = "Amount value can't be null";
        }

        if (($this->container['amount'] < 0)) {
            $invalid_properties[] = 'Invalid amount value while calling CaptureChargeRequest, must be equal or larger than 0';
        }

        return $invalid_properties;
    }

    /**
     * validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        if ($this->container['amount'] === null) {
            return false;
        }

        if ($this->container['amount'] < 0) {
            return false;
        }

        return true;
    }


    /**
     * Gets whether it's partial capture
     * @return bool
     */
    public function isPartialCapture()
    {
        return $this->container['is_partial_capture'];
    }

    /**
     * Set partial capture
     * @param bool $isPartialCapture Amount can be less than or equal to the previously authorised amount
     * @return $this
     */
    public function setPartialCapture($isPartialCapture)
    {
        $this->container['is_partial_capture'] = $isPartialCapture;

        return $this;
    }

    /**
     * Gets amount
     * @return float
     */
    public function getAmount()
    {
        return $this->container['amount'];
    }

    /**
     * Sets amount
     * @param float $amount Amount can be less than or equal to the previously authorised amount
     * @return $this
     */
    public function setAmount($amount)
    {
        if (($amount < 0)) {
            throw new \InvalidArgumentException('Invalid amount value while calling CaptureChargeRequest, must be equal or larger than 0.');
        }

        $this->container['amount'] = $amount;

        return $this;
    }

    /**
     * Returns true if offset exists. False otherwise.
     * @param  integer $offset Offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     * @param  integer $offset Offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     * @param  integer $offset Offset
     * @param  mixed   $value  Value to be set
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     * @param  integer $offset Offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) { // use JSON pretty print
            return json_encode(Zip\ObjectSerializer::sanitizeForSerialization($this), JSON_PRETTY_PRINT);
        }

        return json_encode(Zip\ObjectSerializer::sanitizeForSerialization($this));
    }
}