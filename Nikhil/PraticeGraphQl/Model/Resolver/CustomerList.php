<?php

declare(strict_types=1);

namespace Nikhil\PraticeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\CustomerFactory;

class CustomerList implements ResolverInterface
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     *
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        CustomerFactory $customerFactory
    ) {
        $this->customerFactory = $customerFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        $customerId = $this->getCustomerId($args);

        $customerOrderData = $this->getCustomerData($customerId);

        return $customerOrderData;
    }

    /**
     * Return Customer Id from args
     *
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getCustomerId(array $args): int
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('Customer id should be specified'));
        }

        return (int) $args['id'];
    }

    /**
     * Return Customer Data based on Id
     *
     * @param int $customerId
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getCustomerData(int $customerId): array
    {
        try {
            $customerData = [];

            $customerObj = $this->customerFactory->create()->getCollection()
                                                    ->addFieldToFilter("entity_id", ["eq" => $customerId])
                                                    ->getFirstItem();
            
            $customerData = $customerObj->getData();
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $customerData;
    }
}
