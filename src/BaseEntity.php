<?php

namespace Ctechhindi\CorePhpFramework;

/**
 * Application Base Controller
 */

class BaseEntity
{
    /**
     * Remove Table Column in Output
     * @param {Array}
     */
    public function removeTableColumn($removeColumns)
    {
        // VALIDATION
        if (empty($removeColumns)) return false;

        foreach ($removeColumns as $key => $value) {
            if (!isset($this->$value)) continue;
            else unset($this->$value);
        }
    }
}
