<?php

namespace Dayploy\JsDtoBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class JsDtoIgnore
{
    private array $groups;

    /**
     * @param array $groups
     */
    public function __construct(array $groups = [])
    {
        $this->groups = $groups;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function hasGroup(string $group): bool
    {
        // Empty groups = ignore in ALL groups (backward compatible)
        return empty($this->groups) || in_array($group, $this->groups);
    }
}
