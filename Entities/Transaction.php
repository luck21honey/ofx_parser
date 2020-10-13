<?php

namespace ofxtocsv\Entities;

class Transaction extends AbstractEntity
{
    /**
     * @var string
     */
    public $type;

    /**
     * Date the transaction was posted
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * Date the user initiated the transaction, if known
     * @var \DateTimeInterface|null
     */
    public $userInitiatedDate;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $uniqueId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $memo;

    /**
     * @var string
     */
    public $sic;

    /**
     * @var string
     */
    public $checkNumber;

    /**
     * Get the associated type description
     *
     * @return string
     */
    public function typeDesc()
    {
        // Cast SimpleXMLObject to string
        $type = (string)$this->type;
        return array_key_exists($type, self::$types) ? self::$types[$type] : '';
    }
}
