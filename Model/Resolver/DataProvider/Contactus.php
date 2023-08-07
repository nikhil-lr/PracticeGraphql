<?php
/**
 * @author nikhil@Nikhil.com
 * @copyright Copyright (c) 2022
 * @package Nikhil_PraticeGraphQl
 */

namespace Nikhil\PraticeGraphQl\Model\Resolver\DataProvider;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

class Contactus
{
    /**
     *
     * @var DataPersistorInterface
     */
    private $dataPersistor;
    /**
     * @var MailInterface
     */
    private $mail;
    /**
     * @var \Magento\Framework\Data\Form
     */
    private $formKey;
    /**
     * Construct
     *
     * @param ConfigInterface $contactsConfig
     * @param MailInterface $mail
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     */
    public function __construct(
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Data\Form\FormKey $formKey
    ) {
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->formKey = $formKey;
    }

    /**
     * Contact Us email send
     *
     * @param string $fullname
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return array
     */
    public function contactUs($fullname, $email, $subject, $message)
    {
        
        $thanks_message = [];
        
        try {
            $this->sendEmail($fullname, $email, $subject, $message);
        } catch (LocalizedException $e) {
            throw new LocalizedException(__("Error while sending email"));
        }

        $thanks_message['success_message']="Thanks For Contacting Us";

        return $thanks_message;
    }

    /**
     * Email send
     *
     * @param string $fullname
     * @param string $email
     * @param string $telephone
     * @param string $message
     * @return void
     */
    private function sendEmail($fullname, $email, $telephone, $message)
    {
        $form_data = [];
        $form_data['name']      =   $fullname;
        $form_data['email']     =   $email;
        $form_data['telephone'] =   $telephone;
        $form_data['comment']   =   $message;
        $form_data['hideit']    =   "";
        $form_data['form_key']  =   $this->getFormKey();

        $this->mail->send(
            $email,
            ['data' => new DataObject($form_data)]
        );
    }
    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
