<?php

namespace Luma\Core\Customize\Controls;

use WP_Customize_Control;
use WP_Customize_Manager;
use Luma\Core\Core\Config;

abstract class BaseControl extends WP_Customize_Control
{
    protected string $prefix = 'luma-core';

    public function __construct(
        WP_Customize_Manager $manager,
        string $id,
        array $args = []
    ) {
        parent::__construct($manager, $id, $args);

        // uses the core prefix, as its core functionality and not
        // used for storing a setting in db.
        $this->prefix = Config::get_prefix_kebab_core() ?? $this->prefix;
    }

    /**
     * Helper to build namespaced control types.
     */
    protected function prefixed_type(string $suffix): string
    {
        return "{$this->prefix}-customize-{$suffix}";
    }
}
