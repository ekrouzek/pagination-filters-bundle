<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter;

use Ekrouzek\FiltersBundle\QueryFilter\DataField\BooleanDataField;
use Ekrouzek\FiltersBundle\QueryFilter\DataField\DataField;
use Ekrouzek\FiltersBundle\QueryFilter\DataField\DatetimeDataField;
use Ekrouzek\FiltersBundle\QueryFilter\DataField\NumberDataField;
use Ekrouzek\FiltersBundle\QueryFilter\DataField\TextDataField;
use Ekrouzek\FiltersBundle\QueryFilter\Exception\PaginationAndFilterException;
use Ekrouzek\FiltersBundle\QueryFilter\Exception\SortParseException;
use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\FilterToken;
use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\TokenAnd;
use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\TokenBracketLeft;
use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\TokenBracketRight;
use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\TokenExpression;
use Ekrouzek\FiltersBundle\QueryFilter\FilterToken\TokenOr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Ekrouzek\FiltersBundle\Sort\SortDirection;
use Ekrouzek\FiltersBundle\Sort\SortField;
use FOS\RestBundle\Request\ParamFetcher;

class QueryFilter
{
    public const FILTER_FORMAT_PARTS_NMB = 3;
    public const SORT_FORMAT_PARTS_NMB = 2;

    /** @var array<DataField> */
    private array $dataFields;

    /** @var array<SortField>  */
    private array|null $defaultSort = [];

    /**
     * Adds the conditions specified in the query parameter 'filter' to the QueryBuilder.
     *
     * First, the string is tokenized.
     * Then a binary expression tree is created.
     * Finally, a Doctrine expression is produced from the tree recursively. This expression is added do the query builder.
     *
     * The format for the filter string is expected as:
     * *"?filter=method:field:value"*
     * where defined method are: *[eq,neq,like,not-like,lt,lte,gt,gte]*.
     *
     * Operands like 'AND' and 'OR' can be used. The symbols are to use in query are *'&'* and *'|'*.
     * Brackets are also supported.
     *
     * More complicated query might look like for example:
     * *?filter=(eq:id:1 & like:name:"test") | gt:created:"2020-01-01 00:00:00"*
     *
     * @param QueryBuilder $queryBuilder The query builder to alter with filter.
     * @param ParamFetcher $paramFetcher The query parameters from the request.
     * @return QueryBuilder The altered query builder.
     * @throws PaginationAndFilterException If the parsing of the filter string is unsuccessful or the data passed are invalid.
     */
    public function filter(QueryBuilder $queryBuilder, ParamFetcher $paramFetcher): QueryBuilder
    {
        /** @var string $filterString */
        $filterString = $paramFetcher->get('filter');

        $filterTokens = $this->tokenizeFilter($filterString);
        $filterTree = $this->createBinaryExpressionTree($filterTokens);
        if ($filterTree === null) {
            return $queryBuilder;
        }

        $queryBuilder->andWhere($this->getQueryExpressionFromTree($queryBuilder, $filterTree));

        return $queryBuilder;
    }

    /**
     * Adds a **number** field to the registered fields.
     * @param string $key The key that is used to translate the filter to db key.
     * @param string $dbKey The key that is associated with the DQL passed in query builder.
     * @return $this The QueryFilter itself for easy chaining.
     */
    public function addNumberField(string $key, string $dbKey): QueryFilter
    {
        $this->dataFields[$key] = new NumberDataField($key, $dbKey);
        return $this;
    }

    /**
     * Adds a **text** field to the registered fields.
     * @param string $key The key that is used to translate the filter to db key.
     * @param string $dbKey The key that is associated with the DQL passed in query builder.
     * @return $this The QueryFilter itself for easy chaining.
     */
    public function addTextField(string $key, string $dbKey): QueryFilter
    {
        $this->dataFields[$key] = new TextDataField($key, $dbKey);
        return $this;
    }

    /**
     * Adds a **datetime** field to the registered fields.
     * @param string $key The key that is used to translate the filter to db key.
     * @param string $dbKey The key that is associated with the DQL passed in query builder.
     * @return $this The QueryFilter itself for easy chaining.
     */
    public function addDatetimeField(string $key, string $dbKey): QueryFilter
    {
        $this->dataFields[$key] = new DatetimeDataField($key, $dbKey);
        return $this;
    }

    /**
     * Adds a **boolean** field to the registered fields.
     * @param string $key The key that is used to translate the filter to db key.
     * @param string $dbKey The key that is associated with the DQL passed in query builder.
     * @return $this The QueryFilter itself for easy chaining.
     */
    public function addBooleanField(string $key, string $dbKey): QueryFilter
    {
        $this->dataFields[$key] = new BooleanDataField($key, $dbKey);
        return $this;
    }

    /**
     * Parses the sort query parameter and alters the query builder so that it will be sorted.
     *
     * The format for the sort string is expected as:
     * *"?sort=field:[asc,desc]"*
     *
     * @param QueryBuilder $queryBuilder The query builder to alter with sort.
     * @param ParamFetcher $paramFetcher The query parameters from the request.
     * @return QueryBuilder The altered query builder.
     * @throws SortParseException If the parsing of the sort string is unsuccessful or the data passed are invalid.
     */
    public function sort(QueryBuilder $queryBuilder, ParamFetcher $paramFetcher): QueryBuilder
    {
        if ($paramFetcher->get('sort') !== "") {
            /** @var string $sortString */
            $sortString = $paramFetcher->get('sort');

            $parts = explode(':', $sortString);
            if (count($parts) !== self::SORT_FORMAT_PARTS_NMB) {
                throw new SortParseException("An expression string doesn't have 2 required parts.");
            }
            if (!array_key_exists($parts[0], $this->dataFields)) {
                throw new SortParseException("An expression key '$parts[0]' does not exist.");
            }
            if (strtolower($parts[1]) !== 'asc' && strtolower($parts[1]) !== 'desc') {
                throw new SortParseException("An expression value '$parts[1]' isn't supported.");
            }

            $sortBys = [new SortField($parts[0], $parts[1])];
        } else if (count($this->defaultSort) !== 0) {
            $sortBys = $this->defaultSort;
        } else {
            return $queryBuilder;
        }

        foreach ($sortBys as $sortField) {
            $dbKey = $this->dataFields[$sortField->getKey()]->getDbKey();
            $queryBuilder->addOrderBy($dbKey, $sortField->getDirection());
        }

        return $queryBuilder;
    }

    /**
     * Sets a **default** field for the query to be sorted by.
     * @param string $key The key that is used to translate the sort to db key.
     * @return $this The QueryFilter itself for easy chaining.
     * @throws SortParseException If the parsing of the sort string is unsuccessful or the data passed are invalid.
     */
    public function addDefaultSort(string $key, string $direction = SortField::ASC): QueryFilter
    {
        if (!array_key_exists($key, $this->dataFields)) {
            throw new SortParseException("An expression key '$key' does not exist.");
        }
        if (strtolower($direction) !== 'asc' && strtolower($direction) !== 'desc') {
            throw new SortParseException("An expression value '$direction' isn't supported.");
        }

        $this->defaultSort[$key] = new SortField($key, $direction);
        return $this;
    }


    /**
     * Creates array of tokens from a filter strings.
     * Tokens are: brackets, ands, ors, expressions
     *
     * @param string $filterString The string to be tokenized.
     * @return array<FilterToken> Array of filter tokens.
     */
    private function tokenizeFilter(string $filterString): array
    {
        $tokens = [];

        $chars = str_split($filterString);
        $currentString = '';
        foreach ($chars as $char) {
            switch ($char) {
                case '(':
                    if (trim($currentString) !== "") {
                        $tokens[] = new TokenExpression(trim($currentString));
                    }
                    $tokens[] = new TokenBracketLeft(trim($currentString));
                    $currentString = '';
                    break;
                case ')':
                    if (trim($currentString) !== "") {
                        $tokens[] = new TokenExpression(trim($currentString));
                    }
                    $tokens[] = new TokenBracketRight(trim($currentString));
                    $currentString = '';
                    break;
                case '&':
                    if (trim($currentString) !== "") {
                        $tokens[] = new TokenExpression(trim($currentString));
                    }
                    $tokens[] = new TokenAnd(trim($char));
                    $currentString = '';
                    break;
                case '|':
                    if (trim($currentString) !== "") {
                        $tokens[] = new TokenExpression(trim($currentString));
                    }
                    $tokens[] = new TokenOr(trim($currentString));
                    $currentString = '';
                    break;
                default:
                    $currentString .= $char;
            }
        }
        if (trim($currentString) !== "") {
            $tokens[] = new TokenExpression(trim($currentString));
        }
        return $tokens;
    }

    /**
     * Converts the array of tokens to binary expression tree.
     * The tree has operators on the inside vertices and operands on the outside.
     *
     * The conversion is done by combining the infix to postfix algorithm and the BET creation algorithm.
     *
     * @param array<FilterToken> $filterTokens The array of filter tokens from which the tree should be created.
     * @return ?BinaryEvaluationTreeNode The root of the created tree.
     */
    private function createBinaryExpressionTree(array $filterTokens): ?BinaryEvaluationTreeNode
    {
        /** @var array<FilterToken> $conversionStack Used for converting infix to postfix */
        $conversionStack = [];
        /** @var array<BinaryEvaluationTreeNode> $binaryExpressionTreeStack Used for creating binary expression tree*/
        $binaryExpressionTreeStack = [];

        foreach ($filterTokens as $token) {
            if ($token instanceof TokenBracketLeft) {
                $conversionStack[] = $token;
            } else if ($token instanceof TokenBracketRight) {
                // pop all elements until we hit left bracket
                while (!(end($conversionStack) instanceof TokenBracketLeft)) {
                    $this->processBETNode($conversionStack, $binaryExpressionTreeStack);
                }
                array_pop($conversionStack); // pop the left bracket
            } else if ($token instanceof TokenExpression) {
                $binaryExpressionTreeStack[] = new BinaryEvaluationTreeNode($token);
            } else /* operator AND, OR */{
                // pop until the conversion stack is empty or the priority of the operator on stack is lower than the current one
                while (count($conversionStack) !== 0 && end($conversionStack)->getPriority() >= $token->getPriority()) {
                    $this->processBETNode($conversionStack, $binaryExpressionTreeStack);
                }
                $conversionStack[] = $token;
            }
        }

        while (count($conversionStack) !== 0) {
            $this->processBETNode($conversionStack, $binaryExpressionTreeStack);
        }
        // empty the binary tree stack
        while (count($binaryExpressionTreeStack) > 1) {
            $this->processBETNode($conversionStack, $binaryExpressionTreeStack);
        }
        if (count($binaryExpressionTreeStack) === 0) {
            return null;
        }
        return $binaryExpressionTreeStack[0];
    }

    /**
     * Consumes 2 items from the binary tree stack and creates a new node which has the two items as the children.
     * Then this new node is pushed back to the stack.
     *
     * @param array<FilterToken> $conversionStack Used for converting infix to postfix
     * @param array<BinaryEvaluationTreeNode> $binaryExpressionTreeStack Used for creating binary expression tree
     */
    private function processBETNode(array &$conversionStack, array &$binaryExpressionTreeStack): void
    {
        $poppedItem = array_pop($conversionStack);
        if ($poppedItem === null) {
            return;
        }

        $node = new BinaryEvaluationTreeNode($poppedItem);
        $node->leftNode = array_pop($binaryExpressionTreeStack);
        $node->rightNode = array_pop($binaryExpressionTreeStack);

        $binaryExpressionTreeStack[] = $node;
    }

    /**
     * Generates the Doctrine Expression from the binary expression tree recursively.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param BinaryEvaluationTreeNode|null $node The current node to process.
     * @return mixed The generated expr object. Can be 'expression', 'and' or 'or'.
     * @throws PaginationAndFilterException If the parsing of the filter string is unsuccessful or the data passed are invalid.
     */
    private function getQueryExpressionFromTree(QueryBuilder $queryBuilder, ?BinaryEvaluationTreeNode $node): mixed
    {
        if ($node === null) {
            return null;
        }

        return $node->data->combineExpr(
            $queryBuilder,
            $this->getQueryExpressionFromTree($queryBuilder, $node->leftNode),
            $this->getQueryExpressionFromTree($queryBuilder, $node->rightNode),
            $this->dataFields
        );
    }
}
