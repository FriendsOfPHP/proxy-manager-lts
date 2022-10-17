<?php

declare(strict_types=1);

namespace ProxyManager\Generator;

use Laminas\Code\Generator\Exception\RuntimeException;
use Laminas\Code\Generator\ValueGenerator as LaminasValueGenerator;
use ReflectionParameter;

use function explode;
use function implode;
use function in_array;
use function preg_replace;
use function preg_split;
use function rtrim;
use function substr;
use function var_export;

use const PREG_SPLIT_DELIM_CAPTURE;
use const PREG_SPLIT_NO_EMPTY;

/**
 * @internal do not use this in your code: it is only here for internal use
 */
class ValueGenerator extends LaminasValueGenerator
{
    private $reflection;

    public function __construct($value, ?ReflectionParameter $reflection = null)
    {
        if ($value instanceof LaminasValueGenerator) {
            $this->value         = $value->value;
            $this->type          = $value->type;
            $this->arrayDepth    = $value->arrayDepth;
            $this->outputMode    = $value->outputMode;
            $this->allowedTypes  = $value->allowedTypes;
            $this->constants     = $value->constants;
            $this->isSourceDirty = $value->isSourceDirty;
            $this->indentation   = $value->indentation;
            $this->sourceContent = $value->sourceContent;
        } else {
            parent::__construct($value, parent::TYPE_AUTO, parent::OUTPUT_SINGLE_LINE);
        }

        $this->reflection = $reflection;
    }

    public function generate(): string
    {
        try {
            return parent::generate();
        } catch (RuntimeException $e) {
            if ($this->reflection) {
                $value = self::exportDefault($this->reflection);
            } else {
                $value = var_export($this->value, true);

                if (\PHP_VERSION_ID < 80200) {
                    return self::fixVarExport($value);
                }
            }

            return $value;
        }
    }

    private static function fixVarExport(string $value): string
    {
        $parts = preg_split('{(\'(?:[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\')}', $value, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($parts as $i => &$part) {
            if ($part === '' || $i % 2 !== 0) {
                continue;
            }

            $part = preg_replace('/(?(DEFINE)(?<V>[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+))(?<!\\\\)(?&V)(?:\\\\(?&V))*+::/', '\\\\$0', $part);
        }

        return implode('', $parts);
    }

    private static function exportDefault(\ReflectionParameter $param): string
    {
        $default = rtrim(substr(explode('$'.$param->name.' = ', (string) $param, 2)[1] ?? '', 0, -2));

        if (in_array($default, ['<default>', 'NULL'], true)) {
            return 'null';
        }
        if (str_ends_with($default, "...'") && preg_match("/^'(?:[^'\\\\]*+(?:\\\\.)*+)*+'$/", $default)) {
            return var_export($param->getDefaultValue(), true);
        }

        $regexp = "/(\"(?:[^\"\\\\]*+(?:\\\\.)*+)*+\"|'(?:[^'\\\\]*+(?:\\\\.)*+)*+')/";
        $parts = preg_split($regexp, $default, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $regexp = '/([\[\( ]|^)([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*+(?:\\\\[a-zA-Z0-9_\x7f-\xff]++)*+)(?!: )/';
        $callback = (false !== strpbrk($default, "\\:('") && $class = $param->getDeclaringClass())
            ? function ($m) use ($class) {
                switch ($m[2]) {
                    case 'new': case 'false': case 'true': case 'null': return $m[1].$m[2];
                    case 'NULL': return $m[1].'null';
                    case 'self': return $m[1].'\\'.$class->name;
                    case 'namespace\\parent':
                    case 'parent': $m[1].(($parent = $class->getParentClass()) ? '\\'.$parent->name : 'parent');
                    default: return $m[1].'\\'.$m[2];
                }
            }
            : function ($m) {
                switch ($m[2]) {
                    case 'new': case 'false': case 'true': case 'null': return $m[1].$m[2];
                    case 'NULL': return $m[1].'null';
                    default: return $m[1].'\\'.$m[2];
                }
            };

        return implode('', array_map(function ($part) use ($regexp, $callback) {
            switch ($part[0]) {
                case '"': return $part; // for internal classes only
                case "'": return false !== strpbrk($part, "\\\0\r\n") ? '"'.substr(str_replace(['$', "\0", "\r", "\n"], ['\$', '\0', '\r', '\n'], $part), 1, -1).'"' : $part;
                default: return preg_replace_callback($regexp, $callback, $part);
            }
        }, $parts));
    }
}
