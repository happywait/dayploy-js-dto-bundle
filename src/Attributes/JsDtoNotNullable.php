<?php

namespace Dayploy\JsDtoBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsDtoNotNullable
{
    private array $groups;

    /**
     * @param array $groups
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function hasGroup(string $group): bool
    {
        return in_array($group, $this->groups);
    }
}
