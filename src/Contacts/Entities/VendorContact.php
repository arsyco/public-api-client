<?php

namespace ArrowSphere\PublicApiClient\Contacts\Entities;

use ArrowSphere\PublicApiClient\Entities\AbstractEntity;
use ArrowSphere\PublicApiClient\Entities\Exception\EntitiesException;
use ArrowSphere\PublicApiClient\Entities\Property;

/**
 * Class VendorContact
 */
class VendorContact extends AbstractEntity
{
    public const COLUMN_FIRSTNAME = 'firstname';
    public const COLUMN_LASTNAME = 'lastname';
    public const COLUMN_EMAIL = 'email';
    public const COLUMN_PHONE = 'phone';

    /**
     * @var string
     */
    #[Property(required: true)]
    protected string $firstname;

    /**
     * @var string
     */
    #[Property(required: true)]
    protected string $lastname;

    /**
     * @var string
     */
    #[Property(required: true)]
    protected string $email;

    /**
     * @var string|null
     */
    #[Property]
    protected ?string $phone = null;

    /**
     * @param array $data
     *
     * @throws EntitiesException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
