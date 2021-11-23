<?php
class InvoiceRepository {
    /*
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /*
     * @param int ID
     */
    public function load(int $id) {
        $invoice = new Invoice();

        $stmt = $this->pdo->prepare("
            SELECT invoice.id, invoice.number, invoice.fk_id_status, invoice_status.name as status_name, invoice.date, invoice.discount
            FROM invoice
            LEFT JOIN invoice_status ON (invoice_status.id = invoice.fk_id_status)
            WHERE invoice.id=:id
        ");
        $stmt->execute([':id' => $id]);

        $invoiceData = $stmt->fetch();

        if (empty($invoiceData)) {
            return false;
        } else {
            $invoice->setId($invoiceData['id']);
            $invoice->setNumber($invoiceData['number']);
            $invoice->setStatus($invoiceData['fk_id_status']);
            $invoice->setStatusName($invoiceData['status_name']);
            $invoice->date     = $invoiceData['date'];
            $invoice->discount = $invoiceData['discount'];

            $stmt = $this->pdo->prepare("
                SELECT *
                FROM invoice_items
                WHERE fk_id_invoice=:fk_id_invoice
                ORDER BY id ASC
            ");
            $stmt->execute([':fk_id_invoice' => $invoice->getId()]);

            foreach ($stmt->fetchAll() as $item) {
                $invoiceItem = new Item();
                $invoiceItem->setId($item['id']);
                $invoiceItem->sum     = $item['sum'];
                $invoiceItem->amount  = $item['amount'];
                $invoiceItem->name    = $item['name'];

                $invoice->items[] = $invoiceItem;
            }

            return $invoice;
        }
    }

    public function save(Invoice $invoice) {
        try {
            $this->pdo->beginTransaction();

            $query = $this->pdo->prepare("
                INSERT INTO `invoice` (`fk_id_status`, `number`, `date`, `discount`, `created_at`)
                VALUES (:fk_id_status, :number, :date, :discount, :created_at)
            ");
            $query->execute([
                ':fk_id_status' => $invoice->getStatus(),
                ':number'       => $invoice->getNumber(),
                ':date'         => $invoice->date,
                ':discount'     => $invoice->discount,
                ':created_at'    => time()
            ]);

            $invoice->setId($this->pdo->lastInsertId());

            foreach ($invoice->items as &$item) {
                $query = $this->pdo->prepare("
                    INSERT INTO `invoice_items` (`fk_id_invoice`, `sum`, `amount`, `name`)
                    VALUES (:fk_id_invoice, :sum, :amount, :name)
                ");
                $query->execute([
                    ':fk_id_invoice' => $invoice->getId(),
                    ':sum'           => $item->sum,
                    ':amount'        => $item->amount,
                    ':name'          => $item->name
                ]);
                $item->setId($this->pdo->lastInsertId());
            }

            $this->pdo->commit();

            return true;

        } catch (\PDOException $e) {
            $this->pdo->rollBack();

            return false;
        }
    }

    public function update(Invoice $invoice) {
        try {
            $query = $this->pdo->prepare("
                UPDATE `invoice` 
                SET `fk_id_status` = :fk_id_status,
                    `number`       = :number,
                    `date`         =:date,
                    `discount`     =:discount,
                    `updated_at`   =:updated_at
                )
                WHERE id=:id
            ");
            $query->execute([
                ':fk_id_status' => $invoice->getStatus(),
                ':number'       => $invoice->getNumber(),
                ':date'         => $invoice->date,
                ':discount'     => $invoice->discount,
                ':updated_at'   => time(),
                ':id'           => $invoice->getId()
            ]);
            return true;

        } catch (\PDOException $e) {
            return false;
        }
    }

    public function addItem(Item $item) {
        if (empty($item->fk_invoice_id)) {
            return false;
        } else {
            $query = $this->pdo->prepare("
                INSERT INTO invoice_items
                SET fk_id_invoice = :fk_id_invoice,
                    sum           = :sum,
                    amount        = :amount,
                    name          = :name                 
            ");
            $query->execute([
                ':fk_id_invoice' => $item->fk_invoice_id,
                ':sum'           => $item->sum,
                ':amount'        => $item->amount,
                ':name'          => $item->name
            ]);
        }
    }

    public function getInvoicesByParams($status, $date) {
        $query = $this->pdo->prepare("
            SELECT id
            FROM `invoice`
            WHERE `fk_id_status`=:fk_id_status AND `date` > :date
            ORDER BY `date` DESC
        ");
        $query->execute([':fk_id_status' => $status, ':date' => $date]);

        $invoices = [];

        while ($row = $query->fetch()) {
            $invoices[] = $this->load($row[0]);
        }

        return $invoices;
    }
}