<?php

declare(strict_types=1);

namespace Autodoctor\OlxWatcher\Validator;

use Autodoctor\OlxWatcher\Exceptions\ValidateException;

class ValidateService
{
    /**
     * @throws ValidateException
     */
    public static function validated(array $rules): array
    {
        return (new static)->validate($rules);
    }

    /**
     * @throws ValidateException
     */
    public function validate(array $rules): array
    {
        $data = filter_var_array($_REQUEST, $rules);

        if ($data && !in_array(false, $data, true)) {
            return $data;
        }

        throw new ValidateException(sprintf(
                'Invalid entered data: "%s"', $this->toString($this->errors($data))
            )
        );
    }

    protected function errors(array $data): array
    {
        return array_filter($data, fn($value) => $value === false);
    }

    protected function toString(array $errors): string
    {
        return implode(', ', array_keys($errors));
    }
}