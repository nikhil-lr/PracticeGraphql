<?php
/**
 * @author nikhil@Nikhil.com
 * @copyright Copyright (c) 2021
 * @package Nikhil_PraticeGraphQl
 */

namespace Nikhil\PraticeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class Contactus implements ResolverInterface
{
    /**
     *
     * @var \Nikhil\PraticeGraphQl\Model\Resolver\DataProvider\Contactus
     */
    private $contactusDataProvider;

    /**
     * @param \Nikhil\PraticeGraphQl\Model\Resolver\DataProvider\Contactus $contactusDataProvider
     */
    public function __construct(
        \Nikhil\PraticeGraphQl\Model\Resolver\DataProvider\Contactus $contactusDataProvider
    ) {
        $this->contactusDataProvider = $contactusDataProvider;
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
        $fullname = $args['input']['fullname'];
        $email = $args['input']['email'];
        $telephone = $args['input']['telephone'];
        $message = $args['input']['message'];

        if (!isset($args['input']['fullname'])
            || !isset($args['input']['email'])
            || !isset($args['input']['telephone'])
            || !isset($args['input']['message'])
        ) {
            throw new GraphQlInputException(__('Please check input field'));
        }

        $success_message = $this->contactusDataProvider->contactUs(
            $fullname,
            $email,
            $telephone,
            $message
        );
        return $success_message;
    }
}
