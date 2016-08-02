<?php

namespace Company\DeleteOrder\Controller\Adminhtml\Delete;

use Magento\Framework\Exception\LocalizedException;

class MassOrder extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction 
{

    protected $orderManagement;
    
    protected $collectionFactory;
    
    public $_resource;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement
    ) {
        $this->_resource = $resource;
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    protected function massAction(\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection) {
        $connection = $this->_resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $invoiceGridTable = $connection->getTableName('sales_invoice_grid');
        $shippmentGridTable = $connection->getTableName('sales_shipment_grid');
        $creditmemoGridTable = $connection->getTableName('sales_creditmemo_grid');

        foreach ($collection->getItems() as $order) {
            $id = $order->getId();
            try {
                $order->delete();
                $connection->rawQuery('DELETE FROM `' . $invoiceGridTable . '` WHERE order_id=' . $id);
                $connection->rawQuery('DELETE FROM `' . $shippmentGridTable . '` WHERE order_id=' . $id);
                $connection->rawQuery('DELETE FROM `' . $creditmemoGridTable . '` WHERE order_id=' . $id);
                $this->messageManager->addSuccess(__('Delete success order #%1.', $order->getIncrementId()));
            } catch (LocalizedException $e) {
                $this->messageManager->addError(__('Error delete order #%1.', $order->getIncrementId()));
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/order/');
        return $resultRedirect;
    }

}
