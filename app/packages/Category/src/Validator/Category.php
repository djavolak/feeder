<?php
namespace EcomHelper\Category\Validator;

use Skeletor\Core\Validator\ValidatorInterface;
use Volnix\CSRF\CSRF;

/**
 *
 */
class Category implements ValidatorInterface
{
    /**
     * @var CSRF
     */
    private $csrf;

    private $messages = [];

    /**
     * @param CSRF $csrf
     */
    public function __construct(CSRF $csrf)
    {
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
        if (strlen($data['title']) < 3) {
            $this->messages['title'][] = 'Name must be at least 3 characters long.';
            $valid = false;
        }
//        if ($data['level'] < 1 || $data['level'] > 3) {
//            $this->messages['skuPrefix'][] = 'Level must be between 1 and 3.';
//            $valid = false;
//        }
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
