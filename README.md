# Filters bundle

Symfony bundle that allows you to set paging, filtering and sorting for selected API endpoints.

1. [Installation](#installation)
2. [Implementation](#implementation)
3. [Usage](#usage)


<a name="installation"></a>

## Installation

### 1. Add the repository to composer

First you need to add this repository as a source to `composer.json`.
```json
{
   "repositories": {
       "gitlab.com/12925620": {
           "type": "composer",
           "url": "https://gitlab.example.com/api/v4/group/12925620/-/packages/composer/"
       }
   }
}
```

### 2. Add the import itself

Furthermore, the bundle itself must be added to the `composer.json` file, specifically to the `require` block.

```json
{
   "require": {
     "ekrouzek/filters-bundle": "1.0.*"
   }
}
```

<a name="implementation"></a>

## Implementation

Usage is shown on an example GET endpoint to get all the courses.

### 1. Header and ParamFetcher

First, it is necessary to specify the query parameters that we expect for the method:
```php
#[QueryParam(name: "page", requirements: "\d+", default: 1)]
#[QueryParam(name: "itemsPerPage", requirements: "\d+", default: 10)]
#[QueryParam(name: "filter", default: "")]
#[QueryParam(name: "sort", default: "")]
```

Then you also need to add ParamFetcher to the function parameters:

```php
public function getCourses(ParamFetcher $paramFetcher, ...): View
```

### 2. Definition of filters

Subsequently, it is enough to add the definition of which data can be filtered and sorted according to which at the same time to the given method:
`addNumberField()`, `addTextField()`, `addDatetimeField()`, `addBooleanField()` methods are available. The first parameter of these functions is the key that will be presented to the outside - that is, which can be entered into the filter from the outside. The second parameter specifies to which attribute it is mapped in the specified DQL query (more below).

So for example:
```php
$paginationHandler = new PaginationHandler($paramFetcher);
$paginationHandler->createQueryFilter()
     ->addNumberField("id", "c.id")
     ->addTextField("semester", "c.semester")
     ->addTextField("subject", "c.subject")
     ->addDatetimeField("created", "c.created");
```

### 3. Getting the QueryBuilder and the result

Instead of the already obtained result, it is necessary to pass the DQL QueryBuilder for evaluation. It is therefore necessary to create your own method in the repository for obtaining the query. The idea is the same as if you were to create your own method in the repository, however you don't call `->getQuery()->getResult()` at the end, but you pass directly the QueryBuilder.

So for example:

```php
public function getAllCoursesQuery(): QueryBuilder
{
     return $this->getEntityManager()
         ->createQueryBuilder()
         ->select('c')
         ->from(Course::class, 'c');
}
```

Finally, you only create a method in the corresponding Service, which transfers the result of this method in the repository to the controller.
To get the final result of the query, just call `->getPaginatedData($query)` in the controller:

```php
$query = $this->courseService->getAllCoursesQuery();

try {
     $courses = $paginationHandler->getPaginatedData($query);
} catch (PaginationAndFilterException $exception) {
     return $this->sendBadRequest($exception->getMessage());
}
```

### 4. Sending the result

So the result just needs to be serialized and sent.
To send the result with the _pagination header, just call the defined `sendPaginatedResponse()` method, which wraps the serialized data with a header with pagination information:

```php
// Create response.
$result = $this->courseService->formatMany($courses);
return $paginationHandler->sendPaginatedResponse($result);
```

<a name="usage"></a>

## Usage

To use paging, filtering and paging, just add the corresponding GET parameters to the HTTP request.

#### Paging

- The `page` parameter can be used to specify how many pages from the server you want to return.
- The `itemsPerPage` parameter can be used to specify how many items you want on one page.

#### Filtering

- Filtering takes place via the `filter` parameter.
- The filter must consist of individual expressions. One expression looks like this: `<method>:<field>:<value>`.
- The method in the expression can be one of the following: `eq, neq, like, not-like, lt, lte, gt, gte`.
- Expressions can be combined into more complex constructions using logical conjunctions `&` (AND) and `|` (OR).
- You can also use parentheses in expressions: `(` and `)`.
- So the filter can look, for example, like this:
   - `?filter=eq:id:1`
   - `?filter=(eq:id:1 & like:name:"test") | gt:created:"2020-01-01 00:00:00"`

#### Shifting

- Sorting takes place via the `sort` parameter.
- Can currently only be sorted by one attribute.
- The sort expression must look like this: `<field>:[asc,desc]`.
- Sorting can therefore look, for example, like this:
   - `?sort=id:asc`
   - `?sort=created:desc`
