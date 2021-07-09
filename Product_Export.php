<?php
/**
 * Download All Product Data in CSV
 * Put the file in root folder where Magento is installed (/home/kitto/public_html/)
 * Tested on Magento 2.3.7
 * Author:- Basant Mandal | Kitto
 * https://www.techbasant.in
 * Last Updated - 05-July-2021
 */

/**
 * Step-1 Lets Performs essential initialization routines & create an Instance using bootstrap
 */

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

require __DIR__ . '/app/bootstrap.php';
$params    = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);

/**
 * Step-2 Get Object Manager Instance & Use it for Product Factory, File Factory Etc
 */

$objectManager = $bootstrap->getObjectManager();
$objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

$productLoader  = $objectManager->get('Magento\Catalog\Model\ProductFactory');
$fileFactory    = $objectManager->get('Magento\Framework\App\Response\Http\FileFactory');
$productFactory = $objectManager->get('Magento\Catalog\Model\ProductFactory');
$layoutFactory  = $objectManager->get('Magento\Framework\View\Result\LayoutFactory');
$csvProcessor   = $objectManager->get('Magento\Framework\File\Csv');
$directoryList  = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
$collection     = $productFactory->create()->getCollection();

/**
 * I NEED THESE 4 COLUMNS, YOU CAN ADD MORE AS PER YOUR REQUIREMENTS
 */
$content[] = [
    'entity_id' => __('Product ID'),
    'sku'       => __('Product SKU'),
    'name'      => __('Product Name'),
    'type_id'   => __('Product Type')
];
while ($product = $collection->fetchItem()) {
    $productData = $productLoader->create()->load($product->getEntityId());
    $content[]   = [
        $product->getEntityId(),
        $product->getSku(),
        $productData->getName(),
        $product->getTypeId(),
    ];
}

/**
 * Lets Export our data as CSV
 * Filename:- ProductExported.csv
 * Lets Remove after Download
 */
$fileName = 'productsExported.csv';
$filePath = $directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
$csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $content);
$fileFactory->create($fileName, ['type' => "filename", 'value' => $fileName, 'rm' => true,], DirectoryList::MEDIA, 'text/csv', null);
