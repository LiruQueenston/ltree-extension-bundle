<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\TreeBuilder;

use InvalidArgumentException;
use LogicException;
use function array_diff;
use function array_shift;
use function count;
use function is_array;

class TreeBuilderFromArrayResult implements TreeBuilderInterface
{
    public const CHILD_KEY = '__children';

    /**
     * {@inheritdoc}
     */
    public function buildTree($list, string $pathName, array $parentPath = [], ?string $parentName = null, ?string $childrenName = null)
    {
        $nodeList = [];
        $pathFinder = static function (array $path, array &$nodeList, $value) use (&$pathFinder) {
            if (count($path)===1) {
                $nodeList[array_shift($path)]=$value;

                return true;
            }

            $key = array_shift($path);
            if (! isset($nodeList[$key])) {
                return false;
            }

            $element = &$nodeList[$key];
            if (! is_array($element)) {
                throw new InvalidArgumentException('All result values must be instance of array');
            }

            if (! isset($element[self::CHILD_KEY])) {
                $element[self::CHILD_KEY]=[];
            }

            return $pathFinder($path, $element[self::CHILD_KEY], $value);
        };

        while (count($list)>0) {
            $forUnset = [];
            foreach ($list as $key => $item) {
                $path = array_diff($item[$pathName], $parentPath);
                if (! $pathFinder($path, $nodeList, $item)) {
                    continue;
                }

                $forUnset[]=$key;
            }

            foreach ($forUnset as $key) {
                unset($list[$key]);
            }

            if (count($forUnset)===0) {
                throw new LogicException('Impossible to build tree, not all elements have parent node');
            }
        }

        return $nodeList;
    }
}
