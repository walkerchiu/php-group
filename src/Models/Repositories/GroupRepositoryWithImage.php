<?php

namespace WalkerChiu\Group\Models\Repositories;

use WalkerChiu\Group\Models\Repositories\GroupRepository;
use WalkerChiu\MorphImage\Models\Repositories\ImageRepositoryTrait;

class GroupRepositoryWithImage extends GroupRepository
{
    use ImageRepositoryTrait;
}
