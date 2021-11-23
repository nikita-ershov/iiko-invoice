<?php
class Invoice {
    /*
     * ID
     * @var public $id ID
     */
    protected $id;

    /*
     * Номер счета
     * @var private $number
     */
    private $number;

    /*
     * Статус счета
     * @var private $status
     */
    private $status;

    /*
     * Название статуса
     * @var private $status
     */
    private $statusName;

    /*
     * Дата
     * @var public $date
     */
    public $date;

    /*
     * Скидка
     * @var public $date
     */
    public $discount;

    public $items = [];

    public function setId(int $id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function getNumber() : string {
        return $this->number;
    }

    public function setNumber(string $number) : void {
        $this->number = $number;
    }

    public function getStatus() : int {
        return $this->status;
    }

    public function getStatusName() : string {
        return $this->statusName;
    }

    public function setStatus($status) : void {
        $this->status = $status;
    }

    public function setStatusName($statusName) : void {
        $this->statusName = $statusName;
    }

    public function addItem(Item $item) {
        $this->items[] = $item;
    }

    public function getTotalSum() : float {
        $totalSum = 0;
        foreach ($this->items as $item) {
            $totalSum += $item->sum * $item->amount;
        }

        return round(($totalSum - ($totalSum * $this->discount / 100)), 2);
    }
}