<?php

namespace ArrowSphere\PublicApiClient\OrganizationUnit\Entities;

use ArrowSphere\PublicApiClient\Entities\AbstractEntity;
use ArrowSphere\PublicApiClient\Entities\Property;

/**
 * Class OrganizationUnit
 */
class OrganizationUnit extends AbstractEntity
{
    public const COLUMN_REFERENCE = 'organizationUnitRef';
    public const COLUMN_COMPANY_REFERENCE = 'companyRef';
    public const COLUMN_NAME = 'name';
    public const COLUMN_COUNT_USERS = 'countUsers';
    public const COLUMN_COUNT_CUSTOMERS = 'countCustomers';
    public const COLUMN_COUNT_LICENSES = 'countLicenses';
    public const COLUMN_COUNT_ORDERS = 'countOrders';

    /**
     * @var string
     */
    #[Property]
    protected string $organizationUnitRef = '';

    /**
     * @var string
     */
    #[Property]
    protected string $companyRef = '';

    /**
     * @var string
     */
    #[Property(required: true)]
    protected string $name;

    /**
     * @var int|null
     */
    #[Property(type: 'int')]
    protected ?int $countUsers = null;

    /**
     * @var int|null
     */
    #[Property(type: 'int')]
    protected ?int $countCustomers = null;

    /**
     * @var int|null
     */
    #[Property(type: 'int')]
    protected ?int $countLicenses = null;

    /**
     * @var int|null
     */
    #[Property(type: 'int')]
    protected ?int $countOrders = null;

    /**
     * @param array $data
     *
     * @throws \ArrowSphere\PublicApiClient\Entities\Exception\EntitiesException
     */
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getOrganizationUnitRef(): string
    {
        return $this->organizationUnitRef;
    }

    /**
     * @return string
     */
    public function getCompanyRef(): string
    {
        return $this->companyRef;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int|null
     */
    public function getCountUsers(): ?int
    {
        return $this->countUsers;
    }

    /**
     * @return int|null
     */
    public function getCountCustomers(): ?int
    {
        return $this->countCustomers;
    }

    /**
     * @return int|null
     */
    public function getCountLicenses(): ?int
    {
        return $this->countLicenses;
    }

    /**
     * @return int|null
     */
    public function getCountOrders(): ?int
    {
        return $this->countOrders;
    }
}
