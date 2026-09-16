# Partners Client (deprecated)

## General information

The Partners client (deprecated) is used by a partner (reseller) to manage information about itself, such as its contacts.

## Usage

You can get it through the main entry point `PublicApiClient` and its method `getPartnersClient()`, or
instantiate it directly as follows:

```php
<?php

use ArrowSphere\PublicApiClient\Partners\PartnersClient;

const URL = 'https://your-url-to-arrowsphere.example.com';
const API_KEY = 'your API key in ArrowSphere';

$client = (new PartnersClient())
    ->setUrl(URL)
    ->setApiKey(API_KEY);

$contacts = $client->getContactsRaw();
```

### GetContacts (deprecated)

**This method is deprecated.** Please use
[`ContactsClient::getContactsRaw()`](contacts.md#getcontacts) instead.

The "GetContacts" endpoint returns the list of contacts of the caller's company.

The `PartnersClient::getContactsRaw()` method returns a `string` that is the json containing the results.
It accepts an optional `array $parameters`, for instance `['vendor' => 'microsoft']` to filter the
contacts by vendor.

### Organization units (deprecated)

**All the methods below are deprecated.** The organization unit management has been moved to its own
dedicated client, [OrganizationUnitClient](organization-unit.md). Please use the replacement methods
listed below instead; the ones on `PartnersClient` are kept for backward compatibility only and will be
removed in a future major version.

| Deprecated method                                    | Replacement                                                                    |
| ------------------------------------------------------ | --------------------------------------------------------------------------------- |
| `PartnersClient::getOrganizationUnitsRaw()`            | [`OrganizationUnitClient::getOrganizationUnitsRaw()`](organization-unit.md#getorganizationunits) |
| `PartnersClient::getOrganizationUnits()`               | [`OrganizationUnitClient::getOrganizationUnits()`](organization-unit.md#getorganizationunits)     |
| `PartnersClient::getOrganizationUnitsPage()`           | [`OrganizationUnitClient::getOrganizationUnits()`](organization-unit.md#getorganizationunits) or [`OrganizationUnitClient::getOrganizationUnitsRaw()`](organization-unit.md#getorganizationunits) |
| `PartnersClient::createOrganizationUnit()`             | [`OrganizationUnitClient::createOrganizationUnit()`](organization-unit.md#createorganizationunit) |
| `PartnersClient::getOrganizationUnit()`                | [`OrganizationUnitClient::getOrganizationUnit()`](organization-unit.md#getorganizationunit)       |
| `PartnersClient::updateOrganizationUnit()`             | [`OrganizationUnitClient::updateOrganizationUnit()`](organization-unit.md#updateorganizationunit) |
| `PartnersClient::deleteOrganizationUnit()`             | [`OrganizationUnitClient::deleteOrganizationUnit()`](organization-unit.md#deleteorganizationunit) |
