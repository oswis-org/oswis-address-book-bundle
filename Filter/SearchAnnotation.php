<?php

namespace Zakjakub\OswisAccommodationBundle\Filter;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class SearchAnnotation
{
    public $fields = [];

    /**
     * Constructor.
     *
     * @param array $data Key-value for properties to be defined in this class.
     *
     * @throws AnnotationException
     */
    public function __construct(array $data)
    {

        // \error_log('__construct() START');

        if (!isset($data['value']) || !is_array($data['value'])) {
            throw new AnnotationException('Options must be a array of strings.');
        }

        foreach ($data['value'] as $key => $value) {
            if (is_string($value)) {
                $this->fields[] = $value;
                // \error_log('value: ' . $value);
            } else {
                throw new AnnotationException('Options must be a array of strings.');
            }
        }

        // \error_log('__construct() END');
    }
}
