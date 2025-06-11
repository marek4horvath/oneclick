<?php

namespace App\Tests;

use App\Entity\Company\Company;
use App\Entity\Company\CompanySite;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    public function testCompanyEntity(): void
    {
        $company = new Company();
        $company->setName('Test Company');

        $site = new CompanySite();
        $site->setName('Test Site');
        $site->setAddress('Test Address');
        $site->setLatitude(0.0);
        $site->setLongitude(0.0);

        $company->addSite($site);

        $this->assertEquals('Test Company', $company->getName());
        $this->assertCount(1, $company->getSites());
    }

    public function testCompanySiteEntity(): void
    {
        $company = new Company();
        $company->setName('Test Company');

        $site = new CompanySite();
        $site->setName('Test Site');
        $site->setAddress('Test Address');
        $site->setLatitude(0.0);
        $site->setLongitude(0.0);
        $site->setCompany($company);

        $this->assertEquals('Test Site', $site->getName());
        $this->assertEquals('Test Address', $site->getAddress());
        $this->assertEquals(0.0, $site->getLatitude());
        $this->assertEquals(0.0, $site->getLongitude());
        $this->assertInstanceOf(Company::class, $site->getCompany());
    }
}
