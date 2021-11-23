<?php
class InvoiceGenerator {
    private $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function generateRandomInvoices($invoicesAmount = 100, $maxItemsAmount = 10, $deleteOldInvoices = true) : bool {
        if ($invoicesAmount < 1 or $maxItemsAmount < 1) {
            return false;
        } else {
            if ($deleteOldInvoices) {
                $this->deleteOldInvoices();
            }

            for ($i = 1; $i <= $invoicesAmount; $i++) {
                $invoice = new Invoice();
                $invoice->setNumber(sprintf("%04d", $i));
                $invoice->setStatus(rand(1, 3));
                $invoice->date = time() - rand(3600, 3600*24*30);
                $invoice->discount = [5, 10, 15, 20, 25][rand(0, 4)];

                for ($j=1; $j <= rand(1, 10); $j++) {
                    $item = new Item();
                    $item->sum = rand(100, 10000) . '.' . rand(0, 99);
                    $item->amount = rand(1, 15);
                    $item->name = 'Товар ' . $j;

                    $invoice->addItem($item);
                }

                $invoiceRepository = new InvoiceRepository($this->pdo);
                $invoiceRepository->save($invoice);
            }

            return true;
        }
    }

    private function deleteOldInvoices() {
        $this->pdo->query("
            DELETE FROM invoice
        ");
    }
}