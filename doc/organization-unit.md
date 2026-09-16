# Organization Unit Client

## General information

An organization unit allows a partner (reseller) to split its company into smaller units (a service, a
division, ...), each of which can then be assigned users, end customers, licenses and orders. This is
used to organize and restrict access within a single ArrowSphere company account.

This client replaces the organization unit related methods that used to live on the `PartnersClient`
(`/partners/organizationUnits...`), which are now deprecated. See [Partners](partners.md) for the
deprecated methods and their replacements below.

## Entities

### OrganizationUnit

An organization unit is managed by the `OrganizationUnit` entity.

| Field               | Type          | Example                | Description                                                                                    |
| -------------------- | -------------- | ---------------------- | ------------------------------------------------------------------------------------------------ |
| organizationUnitRef  | `string`       | XSPOU123456            | The organization unit's reference. This string is unique.                                       |
| companyRef           | `string`       | XSP12345               | The reference of the company (partner) the unit belongs to.                                      |
| name                 | `string`       | Administration service | The name of the unit, it can be a service name, a division name or other.                        |
| countUsers           | `int` or `null`| 7                       | The count of users assigned to this unit. Not returned when creating/updating a unit.            |
| countCustomers       | `int` or `null`| 11                      | The count of end customers depending on this unit. Not returned when creating/updating a unit.   |
| countLicenses        | `int` or `null`| 8                       | The count of licenses depending on this unit. Not returned when creating/updating a unit.        |
| countOrders          | `int` or `null`| 8                       | The count of orders depending on this unit. Not returned when creating/updating a unit.          |

## Usage

You can get it through the main entry point `PublicApiClient` and its method `getOrganizationUnitClient()`,
or instantiate it directly as follow:

```php
<?php

use ArrowSphere\PublicApiClient\OrganizationUnit\OrganizationUnitClient;

const URL = 'https://your-url-to-arrowsphere.example.com';
const API_KEY = 'your API key in ArrowSphere';

$client = (new OrganizationUnitClient())
    ->setUrl(URL)
    ->setApiKey(API_KEY);

foreach ($client->getOrganizationUnits() as $organizationUnit) {
    echo $organizationUnit->getName() . PHP_EOL;
}
```

### GetOrganizationUnits

The "GetOrganizationUnits" endpoint lists all the organization units that belong to the caller's company.

The `OrganizationUnitClient::getOrganizationUnits()` method returns a `Generator` of `OrganizationUnit`,
handling the pagination transparently. It accepts an optional `array $parameters` to filter and sort the
results:

- `organizationUnitRef` - filter by organization unit reference
- `companyRef` - filter by company reference
- `name` - filter by name
- `sort_by` - one of `organizationUnitRef`, `name`, `countUsers`, `countCustomers`, `countOrders`
- `order_by` - `asc` or `desc`

The `OrganizationUnitClient::getOrganizationUnitsRaw()` method returns a `string` that is the json
containing a single page of results.

### CreateOrganizationUnit

The "CreateOrganizationUnit" endpoint creates a new organization unit for the caller's company, using its
`name` as the only parameter (the `companyRef` is derived from the authenticated caller).

The `OrganizationUnitClient::createOrganizationUnit(string $name)` method returns the newly created
`OrganizationUnit`. The `OrganizationUnitClient::createOrganizationUnitRaw(string $name)` method returns
a `string` that is the json defining the organization unit.

### GetOrganizationUnit

The "GetOrganizationUnit" endpoint retrieves a single organization unit, using its `reference` as
parameter.

The `OrganizationUnitClient::getOrganizationUnit(string $reference, bool $withDetails = false)` method
returns an `OrganizationUnit`. When `$withDetails` is `true`, the API also computes and returns the
`countUsers`, `countCustomers`, `countLicenses` and `countOrders` fields.

The `OrganizationUnitClient::getOrganizationUnitRaw(string $reference)` method returns a `string` that is
the json defining the organization unit.

### UpdateOrganizationUnit

The "UpdateOrganizationUnit" endpoint updates an existing organization unit, using its `reference` and its
new `name` as parameters.

The `OrganizationUnitClient::updateOrganizationUnit(string $reference, string $name)` method returns the
updated `OrganizationUnit`. The `OrganizationUnitClient::updateOrganizationUnitRaw(string $reference, string $name)`
method returns a `string` that is the json defining the organization unit.

### DeleteOrganizationUnit

The "DeleteOrganizationUnit" endpoint deletes an organization unit, using its `reference` as parameter.

The `OrganizationUnitClient::deleteOrganizationUnit(string $reference)` and
`OrganizationUnitClient::deleteOrganizationUnitRaw(string $reference)` methods both return a string
without content.
