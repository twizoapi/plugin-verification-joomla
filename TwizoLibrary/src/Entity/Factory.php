<?php

namespace Twizo\Api\Entity;

use Twizo\Api\AbstractClient;

/**
 * Entity factory class
 *
 * This file is part of the Twizo php api
 *
 * (c) Twizo <info@twizo.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * File that was distributed with this source code.
 */
class Factory
{
    /**
     * @var AbstractClient
     */
    protected $client;

    /**
     * Constructor
     *
     * @param AbstractClient $client
     */
    public function __construct(AbstractClient $client)
    {
        $this->client = $client;
    }

    /**
     * Create backup code object
     *
     * @param string $identifier
     *
     * @return BackupCode
     */
    public function createBackupCode($identifier)
    {
        $backupCode = $this->createEmptyBackupCode();
        $backupCode->setIdentifier($identifier);

        return $backupCode;
    }

    /**
     * Create empty application verify credentials object
     *
     * @return Application\VerifyCredentials
     */
    public function createEmptyApplicationVerifyCredentials()
    {
        return new Application\VerifyCredentials($this->client, $this);
    }

    /**
     * Create empty application verification types object
     *
     * @return Application\VerificationTypes
     */
    public function createEmptyApplicationVerificationTypes()
    {
        return new Application\VerificationTypes($this->client, $this);
    }

    /**
     * Create empty backup code object
     *
     * @return BackupCode
     */
    public function createEmptyBackupCode()
    {
        return new BackupCode($this->client, $this);
    }

    /**
     * @return Balance
     */
    public function createEmptyBalance()
    {
        return new Balance($this->client, $this);
    }

    /**
     * Create empty number lookup object
     *
     * @return NumberLookup
     */
    public function createEmptyNumberLookup()
    {
        return new NumberLookup($this->client, $this);
    }

    /**
     * Create empty sms object
     *
     * @return Sms
     */
    public function createEmptySms()
    {
        $sms = new Sms($this->client, $this);

        return $sms;
    }

    /**
     * Create empty verification object
     *
     * @return Verification
     */
    public function createEmptyVerification()
    {
        return new Verification($this->client, $this);
    }

    /**
     * Create empty widget session object
     *
     * @return WidgetSession
     */
    public function createEmptyWidgetSession()
    {
        return new WidgetSession($this->client, $this);
    }

    /**
     * Create object by property name
     *
     * @param string $propertyName
     *
     * @return mixed|Null
     */
    public function createFromPropertyName($propertyName)
    {
        switch ($propertyName) {
            case 'verification':
                return $this->createEmptyVerification();
            case 'verifications':
                return [];
        }

        return null;
    }

    /**
     * Create number lookup for numbers
     *
     * @param array $numbers
     *
     * @return NumberLookup
     */
    public function createNumberLookup(array $numbers)
    {
        $numberLookup = $this->createEmptyNumberLookup();
        $numberLookup->setNumbers($numbers);

        return $numberLookup;
    }

    /**
     * Create number lookup poll
     *
     * @return Poll
     */
    public function createNumberLookupPoll()
    {
        return new Poll($this->client, $this, Poll::TYPE_NUMBER_LOOKUP);
    }

    /**
     * Create sms object
     *
     * @param string $body
     * @param array  $recipients
     * @param string $sender
     *
     * @return Sms
     */
    public function createSms($body, array $recipients, $sender)
    {
        $sms = $this->createEmptySms();
        $sms->setBody($body);
        $sms->setRecipients($recipients);
        $sms->setSender($sender);

        return $sms;
    }

    /**
     * Create sms poll object
     *
     * @return Poll
     */
    public function createSmsPoll()
    {
        return new Poll($this->client, $this, Poll::TYPE_SMS);
    }

    /**
     * Create verification object
     *
     * @param string $recipient
     *
     * @return Verification
     */
    public function createVerification($recipient)
    {
        $verification = $this->createEmptyVerification();
        $verification->setRecipient($recipient);

        return $verification;
    }

    /**
     * Create widget session object
     *
     * @param array|null  $allowedTypes
     * @param string|null $recipient
     * @param string|null $backupCodeIdentifier
     *
     * @return WidgetSession
     */
    public function createWidgetSession(array $allowedTypes = null, $recipient = null, $backupCodeIdentifier = null)
    {
        $widgetSession = $this->createEmptyWidgetSession();

        if ($allowedTypes !== null) {
            $widgetSession->setAllowedTypes($allowedTypes);
        }

        if ($recipient !== null) {
            $widgetSession->setRecipient($recipient);
        }
        if ($backupCodeIdentifier !== null) {
            $widgetSession->setBackupCodeIdentifier($backupCodeIdentifier);
        }

        return $widgetSession;
    }
}
