<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '../autoload.php';

$config = require __DIR__. '/../config/config.php';
$pdo    = new PDO(
    $config['db']['dsn'],
    $config['db']['username'],
    $config['db']['password'],
    [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $config['db']['charset']]
);

/*
 * Генерируем счета (чтобы заполнить базу данных для тестирования)
 */
$invoiceGenerator = new InvoiceGenerator($pdo);
$invoiceGenerator->generateRandomInvoices(100, 10, true);

$invoiceRepository = new InvoiceRepository($pdo);

$invoicesData = $invoiceRepository->getInvoicesByParams(InvoiceStatus::INVOICE_STATUS_PAID, strtotime("01.01.2020"));

echo '<pre>';
print_r($invoicesData);
echo '</pre>';