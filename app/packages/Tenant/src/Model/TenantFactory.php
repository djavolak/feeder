<?php

namespace EcomHelper\Tenant\Model;

class TenantFactory
{
    public static function compileEntityForUpdate($data, $entityManager)
    {
        $tenant = $entityManager->getRepository(\EcomHelper\Tenant\Entity\Tenant::class)->find($data['id']);
        $tenantDto = new \EcomHelper\Tenant\Model\Tenant(...$data);
        $tenant->populateFromDto($tenantDto);

        return $tenant->getId();
    }

    public static function compileEntityForCreate($data, $entityManager)
    {
        $tenant = new \EcomHelper\Tenant\Entity\Tenant();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $tenantDto = new \EcomHelper\Tenant\Model\Tenant(...$data);
        $tenant->populateFromDto($tenantDto);
        $entityManager->persist($tenant);

        return $tenant->getId();
    }

    public static function make($itemData, $entityManager): \EcomHelper\Tenant\Model\Tenant
    {
        return new \EcomHelper\Tenant\Model\Tenant(...$itemData);
    }
}