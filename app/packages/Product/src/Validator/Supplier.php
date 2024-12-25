<?php
namespace EcomHelper\Product\Validator;

use Skeletor\Core\Validator\ValidatorInterface;
use EcomHelper\Product\Repository\SupplierRepository;
use Volnix\CSRF\CSRF;

/**
 * Class Supplier.
 * Supplier validator.
 *
 * @package Fakture\Client\Validator
 */
class Supplier implements ValidatorInterface
{
    /**
     * @var SupplierRepository
     */
    private $repo;

    /**
     * @var CSRF
     */
    private $csrf;

    private $messages = [];

    /**
     * User constructor.
     *
     * @param SupplierRepository $repo
     * @param CSRF $csrf
     */
    public function __construct(SupplierRepository $repo, CSRF $csrf)
    {
        $this->repo = $repo;
        $this->csrf = $csrf;
    }

    /**
     * Validates provided data, and sets errors with Flash in session.
     *
     * @param $data
     *
     * @return bool
     */
    public function isValid(array $data): bool
    {
        $valid = true;
        if (strlen($data['name']) < 3) {
            $this->messages['name'][] = 'Name must be at least 3 characters long.';
            $valid = false;
        }
        if (strlen($data['skuPrefix']) === 0) {
            $this->messages['skuPrefix'][] = 'You must enter sku prefix.';
            $valid = false;
        }
        if (!$this->csrf->validate($data)) {
            $this->messages['general'][] = 'Invalid form key.';
            $valid = false;
        }

        return $valid;
    }

    /**
     * Hack used for testing
     *
     * @return string
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
