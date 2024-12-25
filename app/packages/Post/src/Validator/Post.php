<?php
namespace EcomHelper\Post\Validator;

use Skeletor\Core\Validator\ValidatorInterface;
use Volnix\CSRF\CSRF;

class Post implements ValidatorInterface
{
    private $messages = [];

    /**
     * @param CSRF $csrf
     */
    public function __construct(private CSRF $csrf) {}

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
            $this->messages['title'][] = 'Title must be at least 3 characters long.';
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
