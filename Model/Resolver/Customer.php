<?php

declare(strict_types=1);

namespace Nikhil\PraticeGraphQl\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Customers field resolver, used for GraphQL request processing.
 */

class Customer implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     *
     * @param ValueFactory $valueFactory
     * @param CustomerFactory $customerFactory
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ValueFactory $valueFactory,
        CustomerFactory $customerFactory,
        ServiceOutputProcessor $serviceOutputProcessor,
        ExtensibleDataObjectConverter $dataObjectConverter,
        CustomerRepositoryInterface $customerRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->valueFactory = $valueFactory;
        $this->customerFactory = $customerFactory;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->dataObjectConverter = $dataObjectConverter;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
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
    ) : Value {
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }

        try {
            $data = $this->getCustomerData($context->getUserId());

            $result = function () use ($data) {
                return !empty($data) ? $data : [];
            };

            return $this->valueFactory->create($result);

        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }

    /**
     * Return Customer Data
     *
     * @param int $customerId
     * @return array
     * @throws NoSuchEntityException|LocalizedException
     */
    private function getCustomerData($customerId) : array
    {
        try {
            $customerData = [];
            $customerColl = $this->customerFactory->create()->getCollection()
                                                    ->addFieldToFilter("entity_id", ["eq"=>$customerId]);
            foreach ($customerColl as $customer) {
                array_push($customerData, $customer->getData());
            }
            return isset($customerData[0])?$customerData[0]:[];
        } catch (NoSuchEntityException $e) {
            return [];
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }
}
