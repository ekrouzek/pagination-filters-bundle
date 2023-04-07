<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter;

use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\FilterToken;

/**
 * Structure to represent a single node in binary evaluation tree used for parsing the query filter string.
 */
class BinaryEvaluationTreeNode
{
    public FilterToken $data;
    public ?BinaryEvaluationTreeNode $leftNode;
    public ?BinaryEvaluationTreeNode $rightNode;

    public function __construct(FilterToken $data)
    {
        $this->data = $data;
        $this->leftNode = null;
        $this->rightNode = null;
    }
}
