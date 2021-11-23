<?php
class Item {
    /*
     * ID
     * @var $id ID
     */
    protected $id;

    public $fk_invoice_id;

    /*
     * Номер счета
     * @var $number
     */
    public $sum;

    /*
     * Количество
     * @var $amount
     */
    public $amount;

    /*
     * Название
     * @var $name Название
     */
    public $name;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function __construct($itemData = []) {

    }

}