<?php

declare(strict_types=1);

namespace App\Models;

/**
 * DetectLog Model
 */
final class DetectRule extends Model
{
    protected $connection = 'default';
    protected $table = 'detect_list';

    /**
     * 规则类型
     */
    public function type(): string
    {
        return $this->type === 1 ? '数据包明文匹配' : '数据包 hex 匹配';
    }
}
